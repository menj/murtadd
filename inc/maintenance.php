<?php
/**
 * Maintenance mode and the branded error pages (Appearance → Theme Options → Upkeep).
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
 * The static pages in errors/ assume a site at the domain root with the
 * theme in /wp-content/themes/murtadd/. The theme writes a copy of each into
 * wp-content/murtadd-errors/ with this site's home address and theme folder,
 * so a site installed in a subfolder gets links that work; the .htaccess
 * lines on the Upkeep tab point there.
 *
 * The pages link the theme's tokens.min.css for the brand colours and fonts;
 * error-page.css falls back to the same values when it cannot load.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/** The marker every file the theme writes carries in its first lines. */
const MURTADD_DROPIN_MARKER = 'Murtadd error page drop-in';

/** The folder in wp-content that holds this site's copies of the static pages. */
const MURTADD_STATIC_ERRORS = 'murtadd-errors';

/**
 * The files the theme writes into wp-content, and their sources in the theme:
 * the drop-ins, their stylesheet and a copy of each static page in errors/.
 *
 * @return array<string,string> Target name, relative to wp-content => source path relative to the theme.
 */
function murtadd_dropin_files() {
	$files = array(
		'maintenance.php'           => 'dropins/maintenance.php',
		'db-error.php'              => 'dropins/db-error.php',
		'php-error.php'             => 'dropins/php-error.php',
		'murtadd-error-page.css'    => 'assets/css/error-page.min.css',
	);
	foreach ( array( '403', '500', '502', '503', '504' ) as $code ) {
		$files[ MURTADD_STATIC_ERRORS . '/' . $code . '.html' ] = 'errors/' . $code . '.html';
	}
	return $files;
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
 * Whether a file is a leftover of one of the owner's sister themes, which
 * mark every drop-in "Written by the ... theme; safe to delete". Such a file
 * may be replaced at install time. A file with no such mark is never touched,
 * and only the theme's own files are removed when it is switched off.
 *
 * @param string $path File path.
 * @return bool
 */
function murtadd_dropin_is_sibling( $path ) {
	if ( ! is_readable( $path ) ) {
		return false;
	}
	$head = (string) file_get_contents( $path, false, null, 0, 400 ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	return (bool) preg_match( '/Written by the [A-Za-z0-9 .\'-]{1,40} theme; safe to delete/', $head );
}

/**
 * Whether the installer may write over a file: the theme's own, or a sister
 * theme's marked leftover.
 *
 * @param string $path File path.
 * @return bool
 */
function murtadd_dropin_is_replaceable( $path ) {
	return murtadd_dropin_is_ours( $path ) || murtadd_dropin_is_sibling( $path );
}

/**
 * The text the theme writes for one file, with its placeholders filled.
 *
 * @param string $name Target name, a key of murtadd_dropin_files().
 * @return string|null Null when the source is missing.
 */
function murtadd_dropin_text( $name ) {
	$files  = murtadd_dropin_files();
	$source = get_stylesheet_directory() . '/' . ( $files[ $name ] ?? '' );
	if ( ! isset( $files[ $name ] ) || ! is_readable( $source ) ) {
		return null;
	}
	$text = (string) file_get_contents( $source ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( '.html' === substr( $name, -5 ) ) {
		// errors/ is built for a site at the root with the theme in
		// /wp-content/themes/murtadd/; the copy carries this site's addresses.
		$tokens = murtadd_dropin_tokens( '' );
		$theme  = untrailingslashit( (string) wp_parse_url( get_stylesheet_directory_uri(), PHP_URL_PATH ) );
		return '<!-- ' . MURTADD_DROPIN_MARKER . ' ' . MURTADD_VERSION . ". Written by the Murtadd theme; safe to delete. -->\n" . strtr(
			$text,
			array(
				'href="/wp-content/themes/murtadd/assets/css/' => 'href="' . esc_attr( $theme ) . '/assets/css/',
				'href="/"'                                     => 'href="' . $tokens['{{HOME_URL}}'] . '"',
			)
		);
	}
	if ( '.css' === substr( $name, -4 ) ) {
		return '/* ' . MURTADD_DROPIN_MARKER . ' ' . MURTADD_VERSION . '. Written by the Murtadd theme; safe to delete. */' . "\n" . $text;
	}
	$css_url = untrailingslashit( (string) wp_parse_url( content_url( '/' ), PHP_URL_PATH ) ) . '/murtadd-error-page.css?ver=' . MURTADD_VERSION;
	return strtr( $text, murtadd_dropin_tokens( $css_url ) );
}

/**
 * The state of one file in a directory: missing, written (the theme's own and
 * current), outdated (the theme's own, from another version or with other
 * addresses) or kept (another file, left alone).
 *
 * @param string $name Target name.
 * @param string $dir  Directory, wp-content by default.
 * @return string
 */
function murtadd_dropin_state( $name, $dir = '' ) {
	$path = rtrim( '' !== $dir ? $dir : WP_CONTENT_DIR, '/' ) . '/' . $name;
	if ( ! file_exists( $path ) ) {
		return 'missing';
	}
	if ( ! murtadd_dropin_is_ours( $path ) ) {
		return 'kept';
	}
	return (string) file_get_contents( $path ) === murtadd_dropin_text( $name ) ? 'written' : 'outdated'; // phpcs:ignore WordPress.WP.AlternativeFunctions
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
	$result = array();
	foreach ( array_keys( murtadd_dropin_files() ) as $name ) {
		$target = rtrim( $dir, '/' ) . '/' . $name;
		if ( ! is_dir( dirname( $target ) ) ) {
			@mkdir( dirname( $target ), 0755, true ); // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
		}
		if ( file_exists( $target ) && ! murtadd_dropin_is_replaceable( $target ) ) {
			$result[ $name ] = 'kept';
			continue;
		}
		$text = murtadd_dropin_text( $name );
		if ( null === $text ) {
			$result[ $name ] = 'failed';
			continue;
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
	// The folder of static copies goes too, but only when it is empty.
	@rmdir( rtrim( $dir, '/' ) . '/' . MURTADD_STATIC_ERRORS ); // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
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
 * Whether this site writes the shared drop-ins: the main site, or on a
 * network whose main site runs another theme, the one site with the lowest
 * ID that runs this one (murtadd_dropins_first_theme_site()). That subsite
 * then puts back files removed while it was switching to the theme, when
 * another site switched away at the same moment.
 *
 * @return bool
 */
function murtadd_dropins_writer() {
	if ( ! is_multisite() || is_main_site() ) {
		return true;
	}
	$slug = basename( dirname( __DIR__ ) );
	$main = get_main_site_id();
	if ( in_array( $slug, array( get_blog_option( $main, 'stylesheet' ), get_blog_option( $main, 'template' ) ), true ) ) {
		return false;
	}
	// One writer only: the site with the lowest ID that runs the theme, so
	// the shared files never flip between subsites' addresses.
	return get_current_blog_id() === murtadd_dropins_first_theme_site();
}

/**
 * The site with the lowest ID that runs this theme, as its stylesheet or
 * its template; 0 when none does. Sites are read 500 at a time.
 *
 * @return int
 */
function murtadd_dropins_first_theme_site() {
	$slug   = basename( dirname( __DIR__ ) );
	$offset = 0;
	do {
		$ids = get_sites(
			array(
				'fields'  => 'ids',
				'number'  => 500,
				'offset'  => $offset,
				'orderby' => 'id',
				'order'   => 'ASC',
			)
		);
		foreach ( $ids as $id ) {
			if ( in_array( $slug, array( get_blog_option( $id, 'stylesheet' ), get_blog_option( $id, 'template' ) ), true ) ) {
				return (int) $id;
			}
		}
		$offset += 500;
	} while ( 500 === count( $ids ) );
	return 0;
}

/**
 * Install, refresh or remove the drop-ins as the Upkeep switch says. Runs for
 * an administrator in wp-admin only: a write into wp-content belongs to the
 * owner, and two visitors' requests must never write the same file at once.
 * On a network the main site writes, since wp-content is shared, or, when
 * the main site runs another theme, the lowest-ID subsite that runs this one.
 *
 * @param bool $force Rewrite even when nothing has changed.
 */
function murtadd_maybe_install_dropins( $force = false ) {
	if ( ! is_admin() || wp_doing_ajax() || ! current_user_can( 'manage_options' ) || ! murtadd_dropins_writer() ) {
		return;
	}
	if ( ! murtadd_upkeep( 'error_pages' ) ) {
		// The status is saved on every attempt, the stamp only on full success,
		// so a partial install is cleaned up too.
		if ( get_option( 'murtadd_dropins_stamp' ) || get_option( 'murtadd_dropins_status' ) ) {
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
	if ( ! murtadd_dropins_writer() ) {
		wp_safe_redirect( add_query_arg( array( 'page' => 'murtadd', 'tab' => 'upkeep' ), admin_url( 'themes.php' ) ) );
		exit;
	}
	murtadd_maybe_install_dropins( true );
	wp_safe_redirect( add_query_arg( array( 'page' => 'murtadd', 'tab' => 'upkeep', 'murtadd_dropins' => 1 ), admin_url( 'themes.php' ) ) );
	exit;
}
add_action( 'admin_post_murtadd_write_dropins', 'murtadd_handle_write_dropins' );

/**
 * Whether another site of the network still runs this theme, as its
 * stylesheet or its template. The drop-ins in the shared wp-content then
 * stay. Sites are read 500 at a time, so a large network is checked in
 * full without loading every site at once.
 *
 * @return bool
 */
function murtadd_dropins_in_use_elsewhere() {
	if ( ! is_multisite() ) {
		return false;
	}
	$slug   = basename( dirname( __DIR__ ) );
	$offset = 0;
	do {
		$ids = get_sites(
			array(
				'fields'       => 'ids',
				'number'       => 500,
				'offset'       => $offset,
				'site__not_in' => array( get_current_blog_id() ),
			)
		);
		foreach ( $ids as $id ) {
			if ( in_array( $slug, array( get_blog_option( $id, 'stylesheet' ), get_blog_option( $id, 'template' ) ), true ) ) {
				return true;
			}
		}
		$offset += 500;
	} while ( 500 === count( $ids ) );
	return false;
}

/**
 * The theme's copies go when the theme is switched off: their stylesheet
 * link points into a theme that may no longer be there.
 */
function murtadd_dropins_on_switch() {
	// On a network the files are shared: they stay while any other site,
	// the main site included, still runs the theme. The last site to switch
	// away removes them, even a subsite.
	if ( murtadd_dropins_in_use_elsewhere() ) {
		delete_option( 'murtadd_dropins_stamp' );
		return;
	}
	murtadd_remove_dropins( WP_CONTENT_DIR );
	delete_option( 'murtadd_dropins_stamp' );
	delete_option( 'murtadd_dropins_status' );
}
add_action( 'switch_theme', 'murtadd_dropins_on_switch' );

/**
 * The lines for .htaccess that point the web server at this site's copies
 * of the static pages, in wp-content/murtadd-errors/.
 *
 * @return string
 */
function murtadd_error_htaccess_snippet() {
	$base  = untrailingslashit( (string) wp_parse_url( content_url( '/' ), PHP_URL_PATH ) ) . '/' . MURTADD_STATIC_ERRORS . '/';
	$lines = array( '# Murtadd: pages for the errors the web server answers before WordPress runs' );
	foreach ( array( 403, 500, 502, 503, 504 ) as $code ) {
		$lines[] = 'ErrorDocument ' . $code . ' ' . $base . $code . '.html';
	}
	return implode( "\n", $lines );
}

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
