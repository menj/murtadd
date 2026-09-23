<?php
/**
 * Taxonomy: Doubt Category (murtadd_doubt_category).
 * FOUR FIXED TERMS — Intellectual, Scriptural, Emotional, Identity.
 * They power the homepage grid and are locked against term management.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

function murtadd_register_taxonomy_doubt_category() {
	register_taxonomy(
		'murtadd_doubt_category',
		array( 'murtadd_doubt' ),
		array(
			'labels'            => array(
				'name'          => __( 'Doubt Categories', 'murtadd' ),
				'singular_name' => __( 'Doubt Category', 'murtadd' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			/*
			 * Public URL: /doubts/intellectual/ — no interior segment.
			 *
			 * A generated rewrite cannot do this, because a wildcard one segment
			 * after /doubts/ collides with single doubts at /doubts/{slug}/. This
			 * taxonomy is exactly four locked terms, so the four URLs are written
			 * as explicit rules in murtadd_doubt_category_rewrites() below, placed
			 * ahead of the CPT rules, and the generated rewrite is disabled.
			 */
			'rewrite'           => false,
			'capabilities'      => array(
				'manage_terms' => 'do_not_allow', // Locked: four fixed terms.
				'edit_terms'   => 'do_not_allow',
				'delete_terms' => 'do_not_allow',
				'assign_terms' => 'edit_posts',
			),
		)
	);
}
add_action( 'init', 'murtadd_register_taxonomy_doubt_category' );

function murtadd_seed_doubt_categories() {
	if ( get_option( 'murtadd_doubt_categories_seeded' ) ) {
		return;
	}
	$terms = array(
		'intellectual' => array( __( 'Intellectual', 'murtadd' ), __( 'Reason, theodicy, philosophy.', 'murtadd' ) ),
		'scriptural'   => array( __( 'Scriptural', 'murtadd' ), __( 'Hadith authenticity, textual variants, hard verses.', 'murtadd' ) ),
		'emotional'    => array( __( 'Emotional', 'murtadd' ), __( 'Unanswered du‘a, burnout, grief.', 'murtadd' ) ),
		'identity'     => array( __( 'Identity', 'murtadd' ), __( 'Belonging and community hurt.', 'murtadd' ) ),
	);
	foreach ( $terms as $slug => $data ) {
		if ( ! term_exists( $slug, 'murtadd_doubt_category' ) ) {
			wp_insert_term( $data[0], 'murtadd_doubt_category', array( 'slug' => $slug, 'description' => $data[1] ) );
		}
	}
	update_option( 'murtadd_doubt_categories_seeded', 1 );
}
add_action( 'init', 'murtadd_seed_doubt_categories', 20 );

/**
 * Permanent redirect from the retired /doubt-category/ URLs.
 *
 * Terms were public under the old slug, so those links may exist in the wild.
 * Runs only on a 404, so it costs nothing on a normal request.
 */
function murtadd_redirect_legacy_doubt_category() {
	if ( ! is_404() ) {
		return;
	}

	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '';
	if ( ! $path || ! preg_match( '#/doubt-category/([^/]+)/?#', $path, $m ) ) {
		return;
	}

	$term = get_term_by( 'slug', sanitize_title( $m[1] ), 'murtadd_doubt_category' );
	if ( $term && ! is_wp_error( $term ) ) {
		wp_safe_redirect( get_term_link( $term ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'murtadd_redirect_legacy_doubt_category' );

/**
 * Flush rewrite rules once, after the slug change in 1.7.0.
 * Saves the site owner a manual trip to Settings → Permalinks.
 */
function murtadd_maybe_flush_rewrites() {
	if ( get_option( 'murtadd_rewrite_version' ) === MURTADD_VERSION ) {
		return;
	}
	flush_rewrite_rules();
	update_option( 'murtadd_rewrite_version', MURTADD_VERSION );
}
add_action( 'init', 'murtadd_maybe_flush_rewrites', 99 );

/**
 * The four canonical category slugs. One list, used by the rules, the link
 * filter, and the slug reservation below.
 *
 * @return string[]
 */
function murtadd_doubt_category_slugs() {
	return array( 'intellectual', 'scriptural', 'emotional', 'identity' );
}

/**
 * Explicit rewrite rules: /doubts/intellectual/ and its pagination.
 *
 * Added 'top', so they are consulted before the murtadd_doubt CPT rules.
 * Only these four exact slugs match; everything else under /doubts/ falls
 * through to the CPT single rule as before.
 */
function murtadd_doubt_category_rewrites() {
	$slugs = implode( '|', murtadd_doubt_category_slugs() );

	add_rewrite_rule(
		'^doubts/(' . $slugs . ')/page/([0-9]{1,})/?$',
		'index.php?murtadd_doubt_category=$matches[1]&paged=$matches[2]',
		'top'
	);
	add_rewrite_rule(
		'^doubts/(' . $slugs . ')/?$',
		'index.php?murtadd_doubt_category=$matches[1]',
		'top'
	);
}
add_action( 'init', 'murtadd_doubt_category_rewrites', 11 );

/**
 * Make every generated term link use the clean URL.
 *
 * With 'rewrite' => false, get_term_link() would produce a query-string URL.
 * This filter is the single source of the public shape, so the sidebar, the
 * cards, the tabs, and the legacy redirects all emit /doubts/{slug}/.
 *
 * @param string  $link The term link.
 * @param WP_Term $term Term object.
 * @param string  $taxonomy Taxonomy name.
 * @return string
 */
function murtadd_doubt_category_term_link( $link, $term, $taxonomy ) {
	if ( 'murtadd_doubt_category' !== $taxonomy ) {
		return $link;
	}
	return home_url( '/doubts/' . $term->slug . '/' );
}
add_filter( 'term_link', 'murtadd_doubt_category_term_link', 10, 3 );

/**
 * Reserve the four category slugs against doubt posts.
 *
 * A doubt published with the slug "emotional" would be unreachable: the
 * explicit rule above would route /doubts/emotional/ to the term archive
 * before the CPT single rule ever ran. WordPress appends -2 instead.
 *
 * @param string $slug      Proposed slug.
 * @param int    $post_id   Post ID.
 * @param string $status    Post status.
 * @param string $post_type Post type.
 * @return string
 */
function murtadd_reserve_category_slugs( $slug, $post_id, $status, $post_type ) {
	if ( 'murtadd_doubt' === $post_type && in_array( $slug, murtadd_doubt_category_slugs(), true ) ) {
		return $slug . '-2';
	}
	return $slug;
}
add_filter( 'wp_unique_post_slug', 'murtadd_reserve_category_slugs', 10, 4 );

/**
 * 301 from the retired /doubts/kind/ URLs (1.7.0 to 1.17.0).
 *
 * Runs only on a 404, so a normal request pays nothing. get_term_link()
 * passes through the filter above, so the redirect lands directly on the
 * clean URL in a single hop — as does the older /doubt-category/ redirect.
 */
function murtadd_redirect_legacy_kind_urls() {
	if ( ! is_404() ) {
		return;
	}

	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '';
	if ( ! $path || ! preg_match( '#/doubts/kind/([^/]+)/?#', $path, $m ) ) {
		return;
	}

	$term = get_term_by( 'slug', sanitize_title( $m[1] ), 'murtadd_doubt_category' );
	if ( $term && ! is_wp_error( $term ) ) {
		wp_safe_redirect( get_term_link( $term ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'murtadd_redirect_legacy_kind_urls' );
