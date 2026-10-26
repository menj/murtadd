<?php
/**
 * The login screen, in the site's own colours and type
 * (Appearance → Murtadd → Security → Login page).
 *
 * Covers every screen wp-login.php serves: log in, lost password, reset
 * password, registration and the interim (session expired) login. It styles
 * the screen through WordPress's own login hooks, so it works whichever
 * address serves it, the private login address (inc/hide-login.php) included,
 * and builds no login link of its own.
 *
 * The mark above the form is the custom logo if one is set, then the core
 * Site Icon, then the theme's own mark, the same order inc/site-icon.php
 * follows for the browser tab. Colours come from tokens.css and the Colours
 * tab; the fonts are the self-hosted pair tokens.css declares. Light only,
 * per the owner's decision (docs/ssot.md, Exclusions).
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the branded login screen is on.
 *
 * @return bool
 */
function murtadd_login_design_on() {
	return (bool) murtadd_security( 'login_design' );
}

/**
 * The image above the form: custom logo, Site Icon, then the theme's mark.
 *
 * @return string URL.
 */
function murtadd_login_logo_url() {
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$url = wp_get_attachment_image_url( $logo_id, 'medium' );
		if ( $url ) {
			return $url;
		}
	}
	$icon = get_site_icon_url( 192 );
	if ( $icon ) {
		return $icon;
	}
	return get_stylesheet_directory_uri() . '/assets/icons/site-icon.svg';
}

/**
 * The custom properties the login stylesheet reads: the Colours tab's
 * accents and the mark. Injected with wp_add_inline_style, the theme's one
 * route for configured values (docs/ssot.md, rule 1).
 *
 * @return string CSS.
 */
function murtadd_login_inline_css() {
	$css    = '';
	$accent = sanitize_hex_color( (string) murtadd_option( 'murtadd_colours', 'accent', '' ) );
	$tint   = sanitize_hex_color( (string) murtadd_option( 'murtadd_colours', 'accent_tint', '' ) );
	if ( $accent ) {
		$css .= '--murtadd-accent:' . $accent . ';';
	}
	if ( $tint ) {
		$css .= '--murtadd-accent-tint:' . $tint . ';';
	}
	$css .= '--murtadd-login-logo:url("' . esc_url_raw( murtadd_login_logo_url() ) . '");';
	return ':root{' . $css . '}';
}

/**
 * Stylesheets: tokens (colours and fonts), then the login screen.
 */
function murtadd_login_assets() {
	if ( ! murtadd_login_design_on() ) {
		return;
	}
	wp_enqueue_style( 'murtadd-tokens', get_stylesheet_directory_uri() . '/assets/css/tokens' . MURTADD_ASSET_SUFFIX . '.css', array(), MURTADD_VERSION );
	wp_enqueue_style( 'murtadd-login', get_stylesheet_directory_uri() . '/assets/css/login' . MURTADD_ASSET_SUFFIX . '.css', array( 'login', 'murtadd-tokens' ), MURTADD_VERSION );
	wp_add_inline_style( 'murtadd-login', murtadd_login_inline_css() );
}
add_action( 'login_enqueue_scripts', 'murtadd_login_assets' );

/**
 * The mark links to the site, not to WordPress.org.
 *
 * @param string $url Core's address.
 * @return string
 */
function murtadd_login_header_url( $url ) {
	return murtadd_login_design_on() ? home_url( '/' ) : $url;
}
add_filter( 'login_headerurl', 'murtadd_login_header_url' );

/**
 * The mark's text is the site name, shown under the mark.
 *
 * @param string $text Core's text.
 * @return string
 */
function murtadd_login_header_text( $text ) {
	return murtadd_login_design_on() ? get_bloginfo( 'name' ) : $text;
}
add_filter( 'login_headertext', 'murtadd_login_header_text' );

/**
 * A class to scope the stylesheet.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function murtadd_login_body_class( $classes ) {
	if ( murtadd_login_design_on() ) {
		$classes[] = 'murtadd-login';
	}
	return $classes;
}
add_filter( 'login_body_class', 'murtadd_login_body_class' );

/**
 * Browser tab title without "WordPress": "Log In ‹ Murtadd".
 *
 * @param string $login_title Full title.
 * @param string $title       Screen title.
 * @return string
 */
function murtadd_login_title( $login_title, $title = '' ) {
	if ( ! murtadd_login_design_on() || '' === (string) $title ) {
		return $login_title;
	}
	return $title . ' ‹ ' . get_bloginfo( 'name' );
}
add_filter( 'login_title', 'murtadd_login_title', 10, 2 );

/**
 * The owner's note above the form, plain text, on the log-in screen only.
 *
 * @param string $message Messages core and plugins have added.
 * @return string
 */
function murtadd_login_message( $message ) {
	$note   = trim( (string) murtadd_security( 'login_message' ) );
	$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login'; // phpcs:ignore WordPress.Security.NonceVerification
	if ( ! murtadd_login_design_on() || '' === $note || 'login' !== $action ) {
		return $message;
	}
	return $message . '<p class="message murtadd-login-message">' . nl2br( esc_html( $note ) ) . '</p>';
}
add_filter( 'login_message', 'murtadd_login_message' );
