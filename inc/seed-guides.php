<?php
/**
 * Long-form guides: loader and seeder.
 *
 * The guides are JSON in inc/data/seed/guides.json, decoded and validated as
 * the other seed files are (inc/seed-data.php): a missing, unreadable,
 * invalid or wrongly shaped file loads as an empty array, and one malformed
 * entry rejects the whole file. The seeder follows inc/seed-content.php: it
 * never overwrites, never destroys, and never resurrects. Every slug it
 * creates or adopts is recorded under murtadd_guide in murtadd_seeded_slugs.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

require_once get_stylesheet_directory() . '/inc/seed-content.php';

/**
 * The guides.
 *
 * @return array<int,array>
 */
function murtadd_seed_guides() {
	static $guides = null;
	if ( null === $guides ) {
		$guides = murtadd_seed_load_entries(
			'guides',
			array(
				'slug'    => 'string',
				'title'   => 'string',
				'excerpt' => 'string',
				'topics'  => 'strings',
				'body'    => 'string',
				'sources' => 'sources',
			),
			array(
				'focus_keyword' => 'string',
				'seo_title'     => 'string',
			)
		);
	}
	return $guides;
}

/**
 * The stored content of a guide: its body, then its sources, tokens resolved.
 *
 * @param array $g Guide entry.
 * @return string
 */
function murtadd_guide_content( $g ) {
	$html = $g['body'];
	if ( $g['sources'] ) {
		$html .= "\n<h2>" . esc_html__( 'Sources', 'murtadd' ) . "</h2>\n<ol class=\"murtadd-guide-sources\">\n";
		foreach ( $g['sources'] as $s ) {
			$text  = esc_html( $s['citation_text'] );
			$html .= '' !== $s['url']
				? '<li>' . $text . ' <a href="' . esc_url( $s['url'] ) . '" rel="noopener">' . esc_html( $s['url'] ) . "</a></li>\n"
				: '<li>' . $text . "</li>\n";
		}
		$html .= "</ol>\n";
	}
	return murtadd_seed_resolve_urls( $html );
}

/**
 * Insert the guides. Idempotent.
 *
 * @return int Number created on this run.
 */
function murtadd_seed_guide_posts() {
	$created = 0;

	foreach ( murtadd_seed_guides() as $g ) {
		if ( murtadd_slug_was_seeded( 'murtadd_guide', $g['slug'] ) ) {
			continue; // Seeded once already. If it is gone, an editor removed it.
		}
		if ( get_page_by_path( $g['slug'], OBJECT, 'murtadd_guide' ) ) {
			murtadd_mark_slug_seeded( 'murtadd_guide', $g['slug'] ); // Already there: adopt it.
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'murtadd_guide',
				'post_name'    => $g['slug'],
				'post_title'   => $g['title'],
				'post_excerpt' => $g['excerpt'],
				'post_content' => murtadd_guide_content( $g ),
				'post_status'  => 'publish',
			)
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		murtadd_mark_slug_seeded( 'murtadd_guide', $g['slug'] );
		update_post_meta( $post_id, '_murtadd_sources', $g['sources'] );
		if ( ! empty( $g['focus_keyword'] ) ) {
			update_post_meta( $post_id, 'rank_math_focus_keyword', $g['focus_keyword'] );
		}
		if ( ! empty( $g['seo_title'] ) ) {
			update_post_meta( $post_id, 'rank_math_title', $g['seo_title'] );
		}
		wp_set_object_terms( $post_id, $g['topics'], 'murtadd_topic' );

		++$created;
	}

	return $created;
}

/**
 * Run the guide seeder once per change to the guide list.
 *
 * Upgrading in place fires no activation hook, so the guides arrive on the
 * next admin request. The slug list's hash is stored in murtadd_guides_seeded;
 * nothing runs until the list changes, and a run never touches an existing
 * or deleted guide.
 *
 * @return int Number created on this run.
 */
function murtadd_maybe_seed_guides() {
	$hash = md5( (string) wp_json_encode( array_column( murtadd_seed_guides(), 'slug' ) ) );
	if ( get_option( 'murtadd_guides_seeded' ) === $hash ) {
		return 0;
	}
	if ( function_exists( 'post_type_exists' ) && ! post_type_exists( 'murtadd_guide' ) ) {
		return 0;
	}
	murtadd_seed_terms();
	$created = murtadd_seed_guide_posts();
	update_option( 'murtadd_guides_seeded', $hash );
	return $created;
}
add_action( 'admin_init', 'murtadd_maybe_seed_guides' );
