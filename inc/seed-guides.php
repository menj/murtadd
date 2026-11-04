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
 * Run murtadd_seed_guide_posts_run() inside the seeding flag, which lets the spam gate
 * (inc/security.php) pass starter content that carries the seed marker.
 *
 * @return mixed What murtadd_seed_guide_posts_run() returns.
 */
function murtadd_seed_guide_posts() {
	$was                        = ! empty( $GLOBALS['murtadd_seeding'] );
	$GLOBALS['murtadd_seeding'] = true;
	try {
		return murtadd_seed_guide_posts_run();
	} finally {
		$GLOBALS['murtadd_seeding'] = $was;
	}
}

/**
 * Insert the guides. Idempotent.
 *
 * @return int Number created on this run.
 */
function murtadd_seed_guide_posts_run() {
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
				'meta_input'   => array( '_murtadd_seeded' => 1 ),
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

/**
 * Carry changed sentences into guides seeded before the change.
 *
 * inc/data/seed/guide-wording.json lists [old, new] sentence pairs. A guide
 * whose stored content matches the shipped content with every new sentence
 * put back to the old one is unedited: it takes the shipped content, through
 * wp_update_post(), which keeps a revision. A guide that differs in any other
 * way has been edited and is left alone. The file's version is stored in
 * murtadd_guide_wording once every write has landed.
 *
 * @return string[] Slugs updated on this run.
 */
function murtadd_update_guide_wording() {
	$done    = array();
	$wording = murtadd_seed_read_json( 'guide-wording' );
	$version = isset( $wording['version'] ) ? (int) $wording['version'] : 0;
	$pairs   = isset( $wording['pairs'] ) && is_array( $wording['pairs'] ) ? $wording['pairs'] : array();
	if ( $version < 1 || ! $pairs || (int) get_option( 'murtadd_guide_wording' ) >= $version ) {
		return $done;
	}
	if ( function_exists( 'post_type_exists' ) && ! post_type_exists( 'murtadd_guide' ) ) {
		return $done;
	}
	$guides = murtadd_seed_guides();
	if ( ! $guides ) {
		return $done; // guides.json unreadable for now: try again later.
	}
	// The Sources heading is translated, so a guide seeded under another
	// locale still counts as unedited: the heading is left out of the match.
	$plain  = static function ( $html ) {
		return murtadd_normalise_markup( (string) preg_replace( '#<h2>[^<]*</h2>(\s*<ol class="murtadd-guide-sources">)#', '<h2></h2>$1', (string) $html ) );
	};
	$failed = false;
	foreach ( $guides as $g ) {
		$post = get_page_by_path( $g['slug'], OBJECT, 'murtadd_guide' );
		if ( ! $post ) {
			continue;
		}
		$shipped = murtadd_guide_content( $g );
		$before  = $shipped;
		foreach ( $pairs as $pair ) {
			if ( is_array( $pair ) && 2 === count( $pair ) && is_string( $pair[0] ) && is_string( $pair[1] ) && '' !== $pair[1] ) {
				$before = str_replace( $pair[1], $pair[0], $before );
			}
		}
		if ( $before === $shipped ) {
			continue; // No changed sentence in this guide.
		}
		if ( $plain( $before ) !== $plain( $post->post_content ) ) {
			continue; // Already current, or edited. Leave it alone.
		}
		$written = wp_update_post(
			array(
				'ID'           => $post->ID,
				'post_content' => $shipped,
			)
		);
		if ( ! $written || is_wp_error( $written ) ) {
			$failed = true;
			continue;
		}
		$done[] = $g['slug'];
	}
	if ( ! $failed ) {
		update_option( 'murtadd_guide_wording', $version );
	}
	return $done;
}
add_action( 'admin_init', 'murtadd_update_guide_wording', 20 );
