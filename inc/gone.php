<?php
/**
 * 410 Gone for URLs that should never have existed, and the Upkeep tab's
 * settings reader.
 *
 * The 2024 compromise left the kind of URLs an SEO-spam injection leaves:
 * paths that never belonged to this site and that crawlers keep requesting.
 * Each rendered the full 404, which tells a crawler to come back. A 410 tells
 * it to drop the URL.
 *
 * Patterns are set on Appearance → Murtadd → Upkeep, one per line: an exact
 * path ("/cheap-pills/") or a prefix ending in * ("/shop/*"). The list ships
 * empty, because no spam path from the compromise survives in the record.
 *
 * Order matters, and two rules keep this from ever eating a real page:
 *  - It runs only on a 404. A pattern that matches a live post, page or
 *    archive does nothing, since WordPress found something to serve.
 *  - It runs at template_redirect priority 20, after the legacy redirects
 *    (priority 10), which exit when they redirect. A recovered pre-2024 URL
 *    is redirected, never answered 410.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defaults for the Upkeep tab (option murtadd_upkeep).
 *
 * @return array<string,int|string>
 */
function murtadd_upkeep_defaults() {
	return array(
		'gone_paths'  => '',
		'maintenance' => 0,
		'error_pages' => 1,
	);
}

/**
 * One Upkeep setting, its default when unsaved.
 *
 * @param string $key Setting key.
 * @return int|string
 */
function murtadd_upkeep( $key ) {
	$defaults = murtadd_upkeep_defaults();
	$saved    = get_option( 'murtadd_upkeep', array() );
	$saved    = is_array( $saved ) ? $saved : array();
	return array_key_exists( $key, $saved ) ? $saved[ $key ] : ( $defaults[ $key ] ?? '' );
}

/**
 * Clean a pasted list of patterns. A line must start with "/", hold only URL
 * path characters, and may end in "*"; "/" and "/*" alone are refused, as
 * they would answer the whole site 410. Duplicates are dropped.
 *
 * @param string $raw Textarea input.
 * @return string One pattern per line.
 */
function murtadd_gone_clean( $raw ) {
	$out = array();
	foreach ( preg_split( '/\R/', (string) $raw ) as $line ) {
		$line = strtolower( trim( $line ) );
		if ( ! preg_match( '#^/[a-z0-9\-._~%/]*\*?$#', $line ) ) {
			continue;
		}
		if ( '' === trim( rtrim( $line, '*' ), '/' ) ) {
			continue;
		}
		$out[ $line ] = true;
		if ( count( $out ) >= 200 ) {
			break;
		}
	}
	return implode( "\n", array_keys( $out ) );
}

/**
 * Whether a request path matches one of the patterns. Exact patterns ignore
 * a trailing slash; a pattern ending in * matches the path and everything
 * under it.
 *
 * @param string   $path     Request path.
 * @param string[] $patterns Cleaned patterns.
 * @return bool
 */
function murtadd_gone_matches( $path, $patterns ) {
	$path = '/' . trim( strtolower( rawurldecode( (string) $path ) ), '/' );
	foreach ( (array) $patterns as $pattern ) {
		$pattern = strtolower( rawurldecode( (string) $pattern ) );
		if ( '*' === substr( $pattern, -1 ) ) {
			// "/shop/*" takes /shop and /shop/x but not /shopping; "/spam*"
			// takes /spam and /spam-anything. The appended slash does both.
			$base = substr( $pattern, 0, -1 );
			if ( '' !== trim( $base, '/' ) && 0 === strpos( $path . '/', $base ) ) {
				return true;
			}
		} elseif ( '/' . trim( $pattern, '/' ) === $path ) {
			return true;
		}
	}
	return false;
}

/**
 * The saved patterns.
 *
 * @return string[]
 */
function murtadd_gone_patterns() {
	$raw = murtadd_gone_clean( (string) murtadd_upkeep( 'gone_paths' ) );
	return '' === $raw ? array() : explode( "\n", $raw );
}

/**
 * Answer a matching 404 with 410 and the branded 410 page.
 */
function murtadd_send_gone() {
	if ( ! is_404() ) {
		return;
	}
	$patterns = murtadd_gone_patterns();
	if ( ! $patterns ) {
		return;
	}
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	if ( '' === $path || ! murtadd_gone_matches( $path, $patterns ) ) {
		return;
	}
	$GLOBALS['murtadd_gone'] = true;
	status_header( 410 );
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow', true );
	include get_stylesheet_directory() . '/410.php';
	exit;
}
add_action( 'template_redirect', 'murtadd_send_gone', 20 );

/**
 * The tab title on a 410 says so.
 *
 * @param array $parts Title parts.
 * @return array
 */
function murtadd_gone_title( $parts ) {
	if ( ! empty( $GLOBALS['murtadd_gone'] ) ) {
		$parts['title'] = __( 'Page removed', 'murtadd' );
	}
	return $parts;
}
add_filter( 'document_title_parts', 'murtadd_gone_title' );
