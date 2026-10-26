<?php
/**
 * Security hardening: Appearance → Murtadd → Security.
 *
 * The site that stood at this domain until August 2024 was lost when its
 * database was replaced during a compromise. This module closes the doors
 * WordPress leaves open by default, each behind its own switch:
 *
 *  - Author enumeration. The REST user list (with a Gravatar hash of each
 *    account's email), ?author=N and the author archives it reveals, the
 *    users sitemap, and the account name in feeds and oEmbed all tell an
 *    attacker which login names exist. Each is closed; the name shown is the
 *    author name from the Profiles tab, or the site's.
 *  - XML-RPC, the endpoint password-guessing scripts use to try hundreds of
 *    passwords in one request, together with its X-Pingback header and RSD
 *    link.
 *  - Security headers on every front-end response, each sent only when
 *    nothing else (the server, a plugin) has sent it already.
 *
 * The private login address is a separate module, inc/hide-login.php.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defaults for the Security tab (option murtadd_security).
 *
 * @return array<string,int|string>
 */
function murtadd_security_defaults() {
	return array(
		'hide_authors'  => 1,
		'xmlrpc_off'    => 1,
		'headers'       => 1,
		'login_slug'    => '',
		'login_design'  => 1,
		'login_message' => '',
	);
}

/**
 * One Security setting, its default when unsaved.
 *
 * @param string $key Setting key.
 * @return int|string
 */
function murtadd_security( $key ) {
	$defaults = murtadd_security_defaults();
	$saved    = get_option( 'murtadd_security', array() );
	$saved    = is_array( $saved ) ? $saved : array();
	return array_key_exists( $key, $saved ) ? $saved[ $key ] : ( $defaults[ $key ] ?? '' );
}

/* ---------- Author enumeration ---------- */

/**
 * Remove the user routes from the REST API for anyone who cannot list users.
 *
 * @param array $endpoints Registered routes.
 * @return array
 */
function murtadd_hardening_rest_endpoints( $endpoints ) {
	if ( ! murtadd_security( 'hide_authors' ) || current_user_can( 'list_users' ) ) {
		return $endpoints;
	}
	foreach ( array_keys( (array) $endpoints ) as $route ) {
		if ( 0 === strpos( (string) $route, '/wp/v2/users' ) ) {
			unset( $endpoints[ $route ] );
		}
	}
	return $endpoints;
}
add_filter( 'rest_endpoints', 'murtadd_hardening_rest_endpoints' );

/**
 * Author archives, and the ?author= address that finds them, go to the home
 * page with a 301. The theme ships no author template; the archive only ever
 * served to reveal the login name in its address.
 */
function murtadd_hardening_author_archives() {
	if ( ! murtadd_security( 'hide_authors' ) || is_admin() ) {
		return;
	}
	if ( is_author() || isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'murtadd_hardening_author_archives', 1 );

/**
 * Author links point to the home page.
 *
 * @param string $link Author archive address.
 * @return string
 */
function murtadd_hardening_author_link( $link ) {
	return murtadd_security( 'hide_authors' ) ? home_url( '/' ) : $link;
}
add_filter( 'author_link', 'murtadd_hardening_author_link' );

/**
 * The public name for an author: the Profiles tab's author name, or the site.
 *
 * @return string
 */
function murtadd_hardening_public_author() {
	$name = (string) get_option( 'murtadd_author_name', '' );
	return '' !== $name ? $name : (string) get_bloginfo( 'name' );
}

/**
 * The author shown in feeds and on the page is never the account's name.
 * Screens in wp-admin keep the real name, which editors need.
 *
 * @param string $name Author display name.
 * @return string
 */
function murtadd_hardening_author_name( $name ) {
	if ( ! murtadd_security( 'hide_authors' ) || ( is_admin() && ! wp_doing_ajax() ) ) {
		return $name;
	}
	return murtadd_hardening_public_author();
}
add_filter( 'the_author', 'murtadd_hardening_author_name' );
add_filter( 'get_the_author_display_name', 'murtadd_hardening_author_name' );

/**
 * oEmbed responses name the public author and link home.
 *
 * @param array $data Response data.
 * @return array
 */
function murtadd_hardening_oembed( $data ) {
	if ( ! murtadd_security( 'hide_authors' ) || ! is_array( $data ) ) {
		return $data;
	}
	$data['author_name'] = murtadd_hardening_public_author();
	$data['author_url']  = home_url( '/' );
	return $data;
}
add_filter( 'oembed_response_data', 'murtadd_hardening_oembed' );

/**
 * No users sitemap.
 *
 * @param WP_Sitemaps_Provider|false $provider Provider.
 * @param string                     $name     Provider name.
 * @return WP_Sitemaps_Provider|false
 */
function murtadd_hardening_sitemap( $provider, $name ) {
	return ( murtadd_security( 'hide_authors' ) && 'users' === $name ) ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'murtadd_hardening_sitemap', 10, 2 );

/* ---------- XML-RPC ---------- */

/**
 * XML-RPC on or off.
 *
 * @param bool $enabled Core's value.
 * @return bool
 */
function murtadd_hardening_xmlrpc( $enabled ) {
	return murtadd_security( 'xmlrpc_off' ) ? false : $enabled;
}
add_filter( 'xmlrpc_enabled', 'murtadd_hardening_xmlrpc' );

/**
 * With XML-RPC off, no method answers at all, the authenticated ones
 * included (xmlrpc_enabled only covers those since WordPress 3.5).
 *
 * @param array $methods Registered methods.
 * @return array
 */
function murtadd_hardening_xmlrpc_methods( $methods ) {
	return murtadd_security( 'xmlrpc_off' ) ? array() : $methods;
}
add_filter( 'xmlrpc_methods', 'murtadd_hardening_xmlrpc_methods' );

/**
 * No X-Pingback header advertising the endpoint.
 *
 * @param array $headers Response headers.
 * @return array
 */
function murtadd_hardening_pingback_header( $headers ) {
	if ( murtadd_security( 'xmlrpc_off' ) ) {
		unset( $headers['X-Pingback'] );
	}
	return $headers;
}
add_filter( 'wp_headers', 'murtadd_hardening_pingback_header' );

/**
 * No RSD link in the head, which only points at xmlrpc.php.
 */
function murtadd_hardening_rsd() {
	if ( murtadd_security( 'xmlrpc_off' ) ) {
		remove_action( 'wp_head', 'rsd_link' );
	}
}
add_action( 'wp', 'murtadd_hardening_rsd' );

/* ---------- Security headers ---------- */

/**
 * The headers to send, minus any already in the response. Header names are
 * matched without regard to case, as HTTP treats them.
 *
 * @param string[] $already Header lines already sent, as headers_list() gives them.
 * @return array<string,string> Name => value.
 */
function murtadd_security_headers( $already = array() ) {
	$headers = array(
		'X-Content-Type-Options' => 'nosniff',
		'Referrer-Policy'        => 'strict-origin-when-cross-origin',
		'X-Frame-Options'        => 'SAMEORIGIN',
		'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
	);

	/**
	 * The security headers sent on front-end responses.
	 *
	 * @param array<string,string> $headers Name => value.
	 */
	$headers = (array) apply_filters( 'murtadd_security_headers', $headers );

	$sent = array();
	foreach ( (array) $already as $line ) {
		$sent[ strtolower( trim( strtok( (string) $line, ':' ) ) ) ] = true;
	}
	foreach ( array_keys( $headers ) as $name ) {
		if ( isset( $sent[ strtolower( $name ) ] ) ) {
			unset( $headers[ $name ] );
		}
	}
	return $headers;
}

/**
 * Send the security headers on front-end responses.
 */
function murtadd_send_security_headers() {
	if ( ! murtadd_security( 'headers' ) || is_admin() || headers_sent() ) {
		return;
	}
	foreach ( murtadd_security_headers( headers_list() ) as $name => $value ) {
		header( $name . ': ' . $value );
	}
}
add_action( 'send_headers', 'murtadd_send_security_headers' );
