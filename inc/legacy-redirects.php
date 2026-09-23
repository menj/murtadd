<?php
/**
 * Redirects for the pre-2024 murtadd.org URLs.
 *
 * The site that stood at this domain until August 2024 was lost — its database
 * was replaced during a compromise, and no dump of the posts survives. What
 * does survive is the URL list, recovered from the Rank Math sitemap caches
 * left in wp-content/uploads. Nine posts and a handful of pages, all of which
 * search engines and inbound links still request, and all of which currently
 * 404.
 *
 * A 404 is the worst answer available here. The reader who typed or followed
 * one of these links was looking for the definition of irtidad, or for a
 * ruling on apostasy, and the site has better answers to both questions than
 * it did before. So each old URL is mapped to its closest live equivalent
 * rather than to the homepage, and the four pages that carried named scholars'
 * positions on apostasy land on the topic page that treats the question as
 * history and open debate.
 *
 * Runs only on a 404, so a normal request never touches it.
 *
 * @package Murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The map: old path fragment => target descriptor.
 *
 * Targets are resolved late, by kind, so the map holds no URLs. A site whose
 * pages or terms are renamed keeps working; a target that does not exist is
 * skipped rather than redirected to a second 404.
 *
 * Kinds:
 *   page  — a page by slug.
 *   topic — a murtadd_topic term archive.
 *   home  — the front page.
 *
 * @return array<string,array{kind:string,slug:string,note:string}>
 */
function murtadd_legacy_redirect_map() {
	$map = array(
		// The definitional cluster: what irtidad is, and who it applies to.
		'definition'                     => array(
			'kind' => 'page',
			'slug' => 'irtidad',
			'note' => 'Definition of apostasy.',
		),
		'related-definitions'            => array(
			'kind' => 'page',
			'slug' => 'irtidad',
			'note' => 'Adjacent terminology.',
		),
		'irtidad-ridda'                  => array(
			'kind' => 'page',
			'slug' => 'irtidad',
			'note' => 'The two Arabic terms.',
		),
		'who-murtadd'                    => array(
			'kind' => 'page',
			'slug' => 'irtidad',
			'note' => 'Who the term applies to.',
		),

		/*
		 * The four scholar pages. Each carried a named public figure's position
		 * on apostasy, and the topic page is where that subject now lives —
		 * treated as legal history with the contemporary debate stated as open,
		 * rather than as one man's ruling reproduced without context.
		 */
		'yasir-qadhi-on-murtadd'         => array(
			'kind' => 'topic',
			'slug' => 'apostasy-law',
			'note' => 'Scholar position on apostasy.',
		),
		'bilal-philips-on-murtadd'       => array(
			'kind' => 'topic',
			'slug' => 'apostasy-law',
			'note' => 'Scholar position on apostasy.',
		),
		'zakir-naik-on-murtadd'          => array(
			'kind' => 'topic',
			'slug' => 'apostasy-law',
			'note' => 'Scholar position on apostasy.',
		),
		'sheikh-assim-al-hakeem-on-murtadd' => array(
			'kind' => 'topic',
			'slug' => 'apostasy-law',
			'note' => 'Scholar position on apostasy.',
		),

		// The old closing page, and the retired utility pages.
		'conclusion'                     => array(
			'kind' => 'page',
			'slug' => 'start-here',
			'note' => 'Closing page of the old site.',
		),
		'sitemap'                        => array(
			'kind' => 'home',
			'slug' => '',
			'note' => 'Retired HTML sitemap.',
		),
		'privacy-policy'                 => array(
			'kind' => 'page',
			'slug' => 'privacy',
			'note' => 'Renamed in this theme.',
		),
	);

	/**
	 * Filters the legacy redirect map.
	 *
	 * @param array $map Old path fragment => target descriptor.
	 */
	return apply_filters( 'murtadd_legacy_redirect_map', $map );
}

/**
 * Resolve a target descriptor to a URL.
 *
 * @param array $target Descriptor from the map.
 * @return string URL, or '' if the target does not exist on this site.
 */
function murtadd_legacy_redirect_target( $target ) {
	if ( empty( $target['kind'] ) ) {
		return '';
	}

	if ( 'home' === $target['kind'] ) {
		return home_url( '/' );
	}

	if ( 'page' === $target['kind'] ) {
		$page = get_page_by_path( $target['slug'] );
		return $page ? (string) get_permalink( $page ) : '';
	}

	if ( 'topic' === $target['kind'] ) {
		$term = get_term_by( 'slug', $target['slug'], 'murtadd_topic' );
		if ( $term && ! is_wp_error( $term ) ) {
			$link = get_term_link( $term );
			return is_wp_error( $link ) ? '' : (string) $link;
		}
	}

	return '';
}

/**
 * Send the redirect.
 *
 * Matches on the first path segment, so /definition/, /definition, and
 * /definition/?utm_source=x all resolve alike.
 *
 * @return void
 */
function murtadd_legacy_redirects() {
	if ( ! is_404() ) {
		return;
	}

	$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	if ( ! $uri ) {
		return;
	}

	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$path = trim( $path, '/' );
	if ( '' === $path || false !== strpos( $path, '/' ) ) {
		return; // Only top-level paths were ever used.
	}

	$map = murtadd_legacy_redirect_map();
	$key = sanitize_title( $path );
	if ( ! isset( $map[ $key ] ) ) {
		return;
	}

	$url = murtadd_legacy_redirect_target( $map[ $key ] );
	if ( ! $url ) {
		return; // Target missing: let the 404 stand rather than chain to another.
	}

	wp_safe_redirect( $url, 301 );
	exit;
}
add_action( 'template_redirect', 'murtadd_legacy_redirects' );
