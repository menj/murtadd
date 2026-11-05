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
 * Patterns are set on Appearance → Theme Options → Upkeep, one per line: an exact
 * path ("/cheap-pills/"), a prefix ending in * ("/shop/*"), a path with * in
 * the middle ("/feed-*.xml"), or a path and a query string ("/?items/*.sthml").
 * The owner's list ships empty, because no spam path from the 2024 compromise
 * survives in the record.
 *
 * The spam the November 2026 logs show is shipped as presets, on by default
 * (murtadd_gone_presets()): /?items/<number>.sthml, /index.php/sitemap<n>.xml,
 * /sitemapimages<n>.xml, /contents/… and /goods.php. WordPress answered most
 * of them 200 with the home page, since it ignores a query or a path it
 * cannot read, so these are answered 410 on the home page as well as on a
 * 404 (murtadd_send_gone_spam()), and before the canonical redirect. A
 * pattern with a query string from the owner's list does the same. The home
 * page answers 410 only for an address that carries such a query string or
 * path; a post, page or archive WordPress resolves is never answered 410.
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
		'gone_paths'   => '',
		'gone_presets' => 1,
		'maintenance'  => 0,
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
 * Query keys a pattern may never name: WordPress's own query variables and
 * the tracking keys links carry. A pattern on one of them would answer a
 * real post, a search or a shared link 410.
 *
 * @return string[]
 */
function murtadd_gone_reserved_keys() {
	return array( 'p', 'page_id', 'pagename', 'name', 's', 'cat', 'category_name', 'tag', 'tag_id', 'author', 'author_name', 'preview', 'preview_id', 'paged', 'page', 'cpage', 'feed', 'm', 'year', 'monthnum', 'day', 'hour', 'minute', 'second', 'w', 'attachment', 'attachment_id', 'post_type', 'taxonomy', 'term', 'rest_route', 'sitemap', 'sitemap-subtype', 'sitemap-stylesheet', 'embed', 'favicon', 'robots', 'error', 'static', 'subpost', 'subpost_id', 'tb', 'pb', 'withcomments', 'withoutcomments', 'more', 'lang', 'amp', 'replytocom', 'unapproved', 'moderation-hash', 'doing_wp_cron', '_wpnonce', 'ver', 'v', 'ref', 'fbclid', 'gclid', 'msclkid', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content' );
}

/**
 * A glob (* for any run of characters) as an anchored regular expression.
 *
 * @param string $glob Pattern.
 * @return string
 */
function murtadd_gone_glob( $glob ) {
	return '#^' . implode( '.*', array_map( static fn( $part ) => preg_quote( $part, '#' ), explode( '*', $glob ) ) ) . '$#';
}

/**
 * Clean a pasted list of patterns. A line must start with "/", hold only URL
 * path characters, may hold * anywhere, and may end in a query string after
 * "?". "/", "/*" and "/?*" alone are refused, as they would answer the whole
 * site 410, and so is a query string whose key could be one of
 * murtadd_gone_reserved_keys(). Duplicates are dropped.
 *
 * @param string $raw Textarea input.
 * @return string One pattern per line.
 */
function murtadd_gone_clean( $raw ) {
	$out      = array();
	$reserved = murtadd_gone_reserved_keys();
	foreach ( preg_split( '/\R/', (string) $raw ) as $line ) {
		$line = strtolower( trim( $line ) );
		if ( ! preg_match( '#^/[a-z0-9\-._~%/*]*(\?[a-z0-9\-._~%/*=&+]+)?$#', $line ) ) {
			continue;
		}
		if ( '' === trim( str_replace( array( '*', '?' ), '', $line ), '/' ) ) {
			continue;
		}
		$query = strpos( $line, '?' );
		if ( false !== $query ) {
			// Read the key as the matcher will, decoded: "utm%5fsource" is utm_source.
			$key = (string) preg_split( '#[=&/]#', rawurldecode( substr( $line, $query + 1 ) ) )[0];
			if ( '' === trim( $key, '*' ) || '*' === $key[0] || preg_grep( murtadd_gone_glob( $key ), $reserved ) ) {
				continue;
			}
		}
		$out[ $line ] = true;
		if ( count( $out ) >= 200 ) {
			break;
		}
	}
	return implode( "\n", array_keys( $out ) );
}

/**
 * Whether a request path, and its query string, matches one of the
 * patterns. Exact patterns ignore a trailing slash; a pattern ending in *
 * matches the path and everything under it; a * elsewhere matches any run
 * of characters. A pattern with a query string matches only a request with
 * a query string that matches it whole.
 *
 * @param string   $path     Request path.
 * @param string[] $patterns Cleaned patterns.
 * @param string   $query    Request query string, without the "?".
 * @return bool
 */
function murtadd_gone_matches( $path, $patterns, $query = '' ) {
	$path  = '/' . trim( strtolower( rawurldecode( (string) $path ) ), '/' );
	$query = strtolower( rawurldecode( (string) $query ) );
	foreach ( (array) $patterns as $pattern ) {
		$pattern = strtolower( rawurldecode( (string) $pattern ) );
		$at      = strpos( $pattern, '?' );
		if ( false !== $at ) {
			if ( '' !== $query
				&& preg_match( murtadd_gone_glob( '/' . trim( substr( $pattern, 0, $at ), '/' ) ), $path )
				&& preg_match( murtadd_gone_glob( substr( $pattern, $at + 1 ) ), $query ) ) {
				return true;
			}
			continue;
		}
		$star = strpos( $pattern, '*' );
		if ( false !== $star && strlen( $pattern ) - 1 === $star ) {
			// "/shop/*" takes /shop and /shop/x but not /shopping; "/spam*"
			// takes /spam and /spam-anything. The appended slash does both.
			$base = substr( $pattern, 0, -1 );
			if ( '' !== trim( $base, '/' ) && 0 === strpos( $path . '/', $base ) ) {
				return true;
			}
		} elseif ( false !== $star ) {
			if ( preg_match( murtadd_gone_glob( '/' . trim( $pattern, '/' ) ), $path ) ) {
				return true;
			}
		} elseif ( '/' . trim( $pattern, '/' ) === $path ) {
			return true;
		}
	}
	return false;
}

/**
 * The spam addresses the November 2026 logs show, answered 410 while the
 * Upkeep switch "Spam presets" is on. /index.php/sitemap* is left out on a
 * site whose permalinks begin with /index.php/, where a real sitemap may
 * live under that address.
 *
 * @return string[]
 */
function murtadd_gone_presets() {
	if ( ! murtadd_upkeep( 'gone_presets' ) ) {
		return array();
	}
	$presets = array( '/?items/*.sthml', '/index.php/sitemap*', '/sitemapimages*', '/contents/*', '/goods.php' );
	if ( 0 === strpos( (string) get_option( 'permalink_structure', '' ), '/index.php' ) ) {
		$presets = array_values( array_diff( $presets, array( '/index.php/sitemap*' ) ) );
	}
	return $presets;
}

/**
 * The request's path and query string.
 *
 * @return string[] Path, query string.
 */
function murtadd_gone_request() {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	return array( (string) wp_parse_url( $uri, PHP_URL_PATH ), (string) wp_parse_url( $uri, PHP_URL_QUERY ) );
}

/**
 * Answer 410 with the branded 410 page, and stop.
 */
function murtadd_gone_respond() {
	global $wp_query;
	if ( isset( $wp_query ) && is_object( $wp_query ) && method_exists( $wp_query, 'set_404' ) ) {
		// The page is rendered as a 404 is, whatever WordPress matched.
		$wp_query->set_404();
	}
	$GLOBALS['murtadd_gone'] = true;
	status_header( 410 );
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow', true );
	include get_stylesheet_directory() . '/410.php';
	exit;
}

/**
 * The spam presets and the owner's query-string patterns, on a 404 and on
 * the home page WordPress serves for an address it cannot read. Runs before
 * the legacy redirects and the canonical redirect, which would otherwise
 * send /index.php/sitemap5.xml and the like on with a 301. The home page is
 * answered 410 only when the address matches a pattern; a request WordPress
 * resolved to a post, page or archive is left alone.
 */
function murtadd_send_gone_spam() {
	if ( ! is_404() && ! is_front_page() && ! is_home() ) {
		return;
	}
	list( $path, $query ) = murtadd_gone_request();
	if ( '' === $path ) {
		return;
	}
	$owner    = array_values( array_filter( murtadd_gone_patterns(), static fn( $p ) => false !== strpos( $p, '?' ) ) );
	$patterns = array_merge( murtadd_gone_presets(), $owner );
	if ( $patterns && murtadd_gone_matches( $path, $patterns, $query ) ) {
		murtadd_gone_respond();
	}
}
add_action( 'template_redirect', 'murtadd_send_gone_spam', 1 );

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
	$patterns             = murtadd_gone_patterns();
	list( $path, $query ) = murtadd_gone_request();
	if ( '' === $path ) {
		return;
	}
	$hit = $patterns && murtadd_gone_matches( $path, $patterns, $query );
	/**
	 * Whether this 404 path should answer 410 Gone. inc/security.php adds the
	 * addresses of spam moved to the trash from the Security tab.
	 *
	 * @param bool   $hit  Whether the Upkeep list matched.
	 * @param string $path Request path.
	 */
	if ( ! apply_filters( 'murtadd_gone_match', $hit, $path ) ) {
		return;
	}
	murtadd_gone_respond();
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
