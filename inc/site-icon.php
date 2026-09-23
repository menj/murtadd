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
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $base . '/site-icon-180.png' ) );
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
