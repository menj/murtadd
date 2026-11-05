<?php
/**
 * Site icon fallback.
 *
 * WordPress core handles the site icon properly: Settings → General → Site Icon
 * generates every size, serves them from the media library, and prints the tags.
 * That is the route the site should use, because an icon set there also drives
 * the admin bar, the login screen, and the WordPress mobile apps.
 *
 * This file only covers the gap before that is configured: it ships the theme's
 * own mark so the site is never left with a blank browser tab. The moment a core
 * Site Icon exists, this file outputs nothing at all.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print icon tags only when core has no Site Icon set.
 */
function murtadd_fallback_site_icon() {
	if ( has_site_icon() ) {
		return; // Core is handling it. Do not compete.
	}

	$base = get_stylesheet_directory_uri() . '/assets/icons';

	printf( '<link rel="icon" href="%s" sizes="32x32">' . "\n", esc_url( $base . '/site-icon-32.png' ) );
	printf( '<link rel="icon" href="%s" sizes="192x192">' . "\n", esc_url( $base . '/site-icon-192.png' ) );
	printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( $base . '/site-icon.svg' ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $base . '/apple-touch-icon.png' ) );
	printf( '<link rel="manifest" href="%s">' . "\n", esc_url( $base . '/site.webmanifest' ) );
}
add_action( 'wp_head', 'murtadd_fallback_site_icon' );
add_action( 'admin_head', 'murtadd_fallback_site_icon' );
add_action( 'login_head', 'murtadd_fallback_site_icon' );

/**
 * Browser theme colour — matches the sidebar, and follows the Colours setting.
 */
function murtadd_theme_colour_meta() {
	$accent = sanitize_hex_color( murtadd_option( 'murtadd_colours', 'accent', '#0f6e56' ) );
	if ( $accent ) {
		printf( '<meta name="theme-color" content="%s">' . "\n", esc_attr( $accent ) );
	}
}
add_action( 'wp_head', 'murtadd_theme_colour_meta' );
add_action( 'login_head', 'murtadd_theme_colour_meta' );

/**
 * Browsers and bots ask for /favicon.ico on their own. Until a core Site Icon
 * is set, answer with assets/icons/favicon.ico: a day's cache and an ETag,
 * with a 304 while it is unchanged. With a Site Icon, core redirects to it.
 */
function murtadd_serve_favicon() {
	$file = get_stylesheet_directory() . '/assets/icons/favicon.ico';
	if ( has_site_icon() || ! is_readable( $file ) ) {
		return;
	}
	$etag = '"' . md5_file( $file ) . '"';
	header( 'Content-Type: image/x-icon' );
	header( 'Cache-Control: public, max-age=86400' );
	header( 'ETag: ' . $etag );
	// If-None-Match may list several validators, weak ones (W/"...") or *.
	$sent = array_map(
		static function ( $v ) {
			return preg_replace( '#^W/#', '', trim( $v ) );
		},
		explode( ',', wp_unslash( (string) ( $_SERVER['HTTP_IF_NONE_MATCH'] ?? '' ) ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	);
	if ( in_array( '*', $sent, true ) || in_array( $etag, $sent, true ) ) {
		status_header( 304 );
		exit;
	}
	status_header( 200 );
	header( 'Content-Length: ' . (string) filesize( $file ) );
	// A HEAD request gets the headers alone.
	if ( 'HEAD' !== ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		readfile( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	exit;
}
add_action( 'do_faviconico', 'murtadd_serve_favicon', 5 );

/**
 * Core ends a HEAD request before do_favicon, with an empty HTML answer.
 * template_redirect runs first, so a HEAD request gets the icon's headers too.
 */
function murtadd_favicon_head_request() {
	if ( is_favicon() ) {
		murtadd_serve_favicon();
	}
}
add_action( 'template_redirect', 'murtadd_favicon_head_request', 0 );
