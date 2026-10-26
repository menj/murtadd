<?php
/**
 * Maintenance mode and the branded error pages (Appearance → Murtadd → Upkeep).
 *
 * After a compromise, a bare "There has been a critical error" screen reads
 * as the site being hacked again. Every situation in which the site cannot
 * render normally now shows one calm, branded page instead, with a 503 and
 * Retry-After so search engines treat the outage as temporary:
 *
 *  - WordPress updating itself, a plugin or a theme: wp-content/maintenance.php
 *  - A fatal PHP error anywhere: wp-content/php-error.php (503 when it looks
 *    like an upload in progress, 500 otherwise)
 *  - The database unreachable: wp-content/db-error.php
 *  - The owner's own maintenance mode: the same page, served by the theme.
 *
 * The three wp-content files are WordPress drop-ins. They live outside the
 * theme so they keep working while the theme is being replaced, and they hold
 * no PHP beyond their status headers. Their sources are dropins/*.php; the
 * theme fills in the site name and addresses and copies them, with their
 * stylesheet (assets/css/error-page.min.css, saved as
 * wp-content/murtadd-error-page.css), the first time an administrator opens an
 * admin page and again whenever anything they carry changes. A file the theme
 * did not write is never overwritten. Switching the theme off, or the Upkeep
 * switch off, removes the theme's copies.
 *
 * The pages link the theme's tokens.min.css for the brand colours and fonts;
 * error-page.css falls back to the same values when it cannot load.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/** The marker every file the theme writes carries in its first lines. */
const MURTADD_DROPIN_MARKER = 'Murtadd error page drop-in';

/**
 * The files the theme writes into wp-content, and their sources in the theme.
 *
 * @return array<string,string> Target name => source path relative to the theme.
 */
function murtadd_dropin_files() {
	return array(
		'maintenance.php'           => 'dropins/maintenance.php',
		'db-error.php'              => 'dropins/db-error.php',
		'php-error.php'             => 'dropins/php-error.php',
		'murtadd-error-page.css'    => 'assets/css/error-page.min.css',
	);
}

/**
 * The values filled into the templates.
 *
 * @param string $css_url Address of the error-page stylesheet.
 * @return array<string,string> Token => value.
 */
function murtadd_dropin_tokens( $css_url ) {
	$theme = untrailingslashit( (string) wp_parse_url( get_stylesheet_directory_uri(), PHP_URL_PATH ) );
	$home  = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	return array(
		'{{VERSION}}'    => MURTADD_VERSION,
		'{{SITE_NAME}}'  => esc_html( wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES ) ),
		'{{HOME_URL}}'   => esc_attr( '' !== $home ? $home : '/' ),
		'{{TOKENS_URL}}' => esc_attr( $theme . '/assets/css/tokens.min.css?ver=' . MURTADD_VERSION ),
		'{{CSS_URL}}'    => esc_attr( $css_url ),
	);
}

/**
 * Whether a file in wp-content was written by this theme.
 *
 * @param string $path File path.
 * @return bool
 */
function murtadd_dropin_is_ours( $path ) {
	if ( ! is_readable( $path ) ) {
		return false;
	}
	$head = (string) file_get_contents( $path, false, null, 0, 300 ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	return false !== strpos( $head, MURTADD_DROPIN_MARKER );
}

/**
 * Write or refresh the drop-ins in a directory.
 *
 * Free of request checks so it can be tested against a temporary directory;
 * murtadd_maybe_install_dropins() decides when it runs.
 *
 * @param string $dir Target directory (wp-content).
 * @return array<string,string> Target name => written, kept (not ours), failed.
 */
function murtadd_install_dropins( $dir ) {
	$css_url = untrailingslashit( (string) wp_parse_url( content_url( '/' ), PHP_URL_PATH ) ) . '/murtadd-error-page.css?ver=' . MURTADD_VERSION;
	$tokens  = murtadd_dropin_tokens( $css_url );
	$result  = array();
	foreach ( murtadd_dropin_files() as $name => $from ) {
		$source = get_stylesheet_directory() . '/' . $from;
		$target = rtrim( $dir, '/' ) . '/' . $name;
		if ( file_exists( $target ) && ! murtadd_dropin_is_ours( $target ) ) {
			$result[ $name ] = 'kept';
			continue;
		}
		if ( ! is_readable( $source ) ) {
			$result[ $name ] = 'failed';
			continue;
		}
		$text = (string) file_get_contents( $source ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( '.css' === substr( $name, -4 ) ) {
			$text = '/* ' . MURTADD_DROPIN_MARKER . ' ' . MURTADD_VERSION . '. Written by the Murtadd theme; safe to delete. */' . "\n" . $text;
		} else {
			$text = strtr( $text, $tokens );
		}
		// A temporary name, then a rename, so WordPress never reads half a file.
		$tmp = $target . '.' . uniqid( 'murtadd-', true ) . '.tmp';
		if ( false !== @file_put_contents( $tmp, $text ) && @rename( $tmp, $target ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
			$result[ $name ] = 'written';
		} else {
			$result[ $name ] = 'failed';
			if ( file_exists( $tmp ) ) {
				@unlink( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
			}
		}
	}
	return $result;
}

/**
 * Remove the theme's own drop-ins from a directory; anyone else's stay.
 *
 * @param string $dir Target directory (wp-content).
 */
function murtadd_remove_dropins( $dir ) {
	foreach ( array_keys( murtadd_dropin_files() ) as $name ) {
		$target = rtrim( $dir, '/' ) . '/' . $name;
		if ( file_exists( $target ) && murtadd_dropin_is_ours( $target ) ) {
			@unlink( $target ); // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
		}
	}
}

/**
 * A fingerprint of everything the drop-ins carry, so they are rewritten when
 * the theme version, site name or addresses change.
 *
 * @return string
 */
function murtadd_dropins_stamp() {
	return md5( wp_json_encode( array( MURTADD_VERSION, get_bloginfo( 'name' ), home_url( '/' ), content_url( '/' ), get_stylesheet_directory_uri() ) ) );
}

/**
 * Install, refresh or remove the drop-ins as the Upkeep switch says. Runs for
 * an administrator in wp-admin only: a write into wp-content belongs to the
 * owner, and two visitors' requests must never write the same file at once.
 * On a network only the main site writes, since wp-content is shared.
 *
 * @param bool $force Rewrite even when nothing has changed.
 */
function murtadd_maybe_install_dropins( $force = false ) {
	if ( ! is_admin() || wp_doing_ajax() || ! current_user_can( 'manage_options' ) || ( is_multisite() && ! is_main_site() ) ) {
		return;
	}
	if ( ! murtadd_upkeep( 'error_pages' ) ) {
		if ( get_option( 'murtadd_dropins_stamp' ) ) {
			murtadd_remove_dropins( WP_CONTENT_DIR );
			delete_option( 'murtadd_dropins_stamp' );
			delete_option( 'murtadd_dropins_status' );
		}
		return;
	}
	$stamp     = murtadd_dropins_stamp();
	$all_there = true;
	foreach ( array_keys( murtadd_dropin_files() ) as $name ) {
		$all_there = $all_there && file_exists( WP_CONTENT_DIR . '/' . $name );
	}
	if ( ! $force && $all_there && get_option( 'murtadd_dropins_stamp' ) === $stamp ) {
		return;
	}
	$result = murtadd_install_dropins( WP_CONTENT_DIR );
	update_option( 'murtadd_dropins_status', $result, false );
	// A failed write is tried again on the next admin page, not recorded as done.
	if ( ! in_array( 'failed', $result, true ) ) {
		update_option( 'murtadd_dropins_stamp', $stamp, false );
	}
}
add_action( 'admin_init', 'murtadd_maybe_install_dropins' );

/**
 * "Write the error pages now" on the Upkeep tab.
 */
function murtadd_handle_write_dropins() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do that.', 'murtadd' ), 403 );
	}
	check_admin_referer( 'murtadd_write_dropins' );
	murtadd_maybe_install_dropins( true );
	wp_safe_redirect( add_query_arg( array( 'page' => 'murtadd', 'tab' => 'upkeep', 'murtadd_dropins' => 1 ), admin_url( 'themes.php' ) ) );
	exit;
}
add_action( 'admin_post_murtadd_write_dropins', 'murtadd_handle_write_dropins' );

/**
 * The theme's copies go when the theme is switched off: their stylesheet
 * link points into a theme that may no longer be there.
 */
function murtadd_dropins_on_switch() {
	if ( is_multisite() && ! is_main_site() ) {
		delete_option( 'murtadd_dropins_stamp' );
		return;
	}
	murtadd_remove_dropins( WP_CONTENT_DIR );
	delete_option( 'murtadd_dropins_stamp' );
	delete_option( 'murtadd_dropins_status' );
}
add_action( 'switch_theme', 'murtadd_dropins_on_switch' );

/* ---------- Maintenance mode ---------- */

/**
 * The maintenance page as the theme serves it: the drop-in template without
 * its PHP header, linked to the theme's own copy of the stylesheet.
 *
 * @return string HTML document.
 */
function murtadd_maintenance_document() {
	$source = (string) file_get_contents( get_stylesheet_directory() . '/dropins/maintenance.php' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$html   = ltrim( (string) preg_replace( '/^<\?php.*?\?>/s', '', $source ) );
	$css    = untrailingslashit( (string) wp_parse_url( get_stylesheet_directory_uri(), PHP_URL_PATH ) ) . '/assets/css/error-page' . MURTADD_ASSET_SUFFIX . '.css?ver=' . MURTADD_VERSION;
	return strtr( $html, murtadd_dropin_tokens( $css ) );
}

/**
 * Whether the current visitor is held at the maintenance page. Anyone who may
 * change the theme's options sees the site; the login screen, wp-admin, REST
 * and cron never pass through template_redirect, so they stay open.
 *
 * @return bool
 */
function murtadd_maintenance_applies() {
	return (bool) murtadd_upkeep( 'maintenance' ) && ! current_user_can( 'edit_theme_options' );
}

/**
 * Serve the maintenance page.
 */
function murtadd_maintenance_mode() {
	if ( ! murtadd_maintenance_applies() ) {
		return;
	}
	if ( ! headers_sent() ) {
		status_header( 503 );
		header( 'Content-Type: text/html; charset=utf-8' );
		header( 'Retry-After: 600' );
		header( 'X-Robots-Tag: noindex' );
		nocache_headers();
	}
	echo murtadd_maintenance_document(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static template, tokens escaped in murtadd_dropin_tokens().
	exit;
}
add_action( 'template_redirect', 'murtadd_maintenance_mode', 0 );

/**
 * While maintenance mode is on, the admin bar says so, on every screen.
 *
 * @param WP_Admin_Bar $bar Admin bar.
 */
function murtadd_maintenance_admin_bar( $bar ) {
	if ( ! murtadd_upkeep( 'maintenance' ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$bar->add_node(
		array(
			'id'    => 'murtadd-maintenance',
			'title' => __( 'Maintenance mode is on', 'murtadd' ),
			'href'  => add_query_arg( array( 'page' => 'murtadd', 'tab' => 'upkeep' ), admin_url( 'themes.php' ) ),
		)
	);
}
add_action( 'admin_bar_menu', 'murtadd_maintenance_admin_bar', 100 );
