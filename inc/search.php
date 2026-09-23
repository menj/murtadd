<?php
/**
 * Search across the meta fields where the content actually lives.
 *
 * A Doubt's statement and short response are postmeta; its post_content is
 * empty. WordPress search reads title, content, and excerpt — so a reader
 * typing the words of their own doubt into the site's search box found
 * nothing. On a site whose reader arrives carrying a question, that is the
 * search failing at the exact moment it exists for.
 *
 * Scope: front-end main-query searches only. Admin search and secondary
 * queries are untouched.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The meta keys worth searching, per post type.
 *
 * @return string[]
 */
function murtadd_searchable_meta_keys() {
	return array(
		'_murtadd_doubt_statement',
		'_murtadd_short_response',
		'_murtadd_claim',
		'_murtadd_position_summary',
		'_murtadd_scholar_or_body',
	);
}

/**
 * Whether this query is the one we extend.
 *
 * @param WP_Query $query The query.
 * @return bool
 */
function murtadd_is_front_search( $query ) {
	return ! is_admin() && $query->is_main_query() && $query->is_search() && trim( (string) $query->get( 's' ) ) !== '';
}

/**
 * Join postmeta once for the search.
 *
 * @param string   $join  JOIN clause.
 * @param WP_Query $query The query.
 * @return string
 */
function murtadd_search_join( $join, $query ) {
	global $wpdb;

	if ( ! murtadd_is_front_search( $query ) ) {
		return $join;
	}

	$keys = "'" . implode( "','", array_map( 'esc_sql', murtadd_searchable_meta_keys() ) ) . "'";

	$join .= " LEFT JOIN {$wpdb->postmeta} murtadd_meta
		ON {$wpdb->posts}.ID = murtadd_meta.post_id
		AND murtadd_meta.meta_key IN ( {$keys} ) ";

	return $join;
}
add_filter( 'posts_join', 'murtadd_search_join', 10, 2 );

/**
 * Extend each search term's OR group to include the joined meta value.
 *
 * Core builds one "(title LIKE x) OR (excerpt LIKE x) OR (content LIKE x)"
 * group per term; this appends "OR (meta_value LIKE x)" inside each group, so
 * multi-word searches keep their AND-between-terms behaviour.
 *
 * @param string   $search WHERE fragment.
 * @param WP_Query $query  The query.
 * @return string
 */
function murtadd_search_where( $search, $query ) {
	global $wpdb;

	if ( ! murtadd_is_front_search( $query ) || '' === $search ) {
		return $search;
	}

	$search = preg_replace(
		"/\({$wpdb->posts}.post_content (NOT LIKE|LIKE) (\'[^\']*\')\)/",
		"({$wpdb->posts}.post_content $1 $2) OR (murtadd_meta.meta_value $1 $2)",
		$search
	);

	return $search;
}
add_filter( 'posts_search', 'murtadd_search_where', 10, 2 );

/**
 * The join can duplicate rows when a post matches on several meta keys.
 *
 * @param string   $distinct DISTINCT clause.
 * @param WP_Query $query    The query.
 * @return string
 */
function murtadd_search_distinct( $distinct, $query ) {
	if ( murtadd_is_front_search( $query ) ) {
		return 'DISTINCT';
	}
	return $distinct;
}
add_filter( 'posts_distinct', 'murtadd_search_distinct', 10, 2 );
