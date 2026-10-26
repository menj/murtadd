<?php
/**
 * Starter content seeder.
 *
 * The theme scaffolds its own pages on activation, and content arrives the same
 * way. Requiring a separate importer plugin to populate a site with its own
 * content is a step that should not exist.
 *
 * The same two invariants as inc/activation.php govern this file:
 *
 *   1. Never overwrite. A post already present at a given slug is skipped
 *      entirely, whatever state it is in. Editing a seeded Doubt and then
 *      re-running the seeder does not undo the edit.
 *   2. Never destroy. Nothing here deletes, trashes, or unpublishes anything.
 *   3. Never resurrect. Every slug this file has ever created is recorded in
 *      the murtadd_seeded_slugs option, and a recorded slug is never seeded
 *      again — whatever happened to the post since. Before this record
 *      existed, the skip check was get_page_by_path() alone, and that check
 *      lies twice: WordPress renames a trashed post's slug to {slug}__trashed,
 *      and a deleted post is simply gone, so in both cases the "is it there"
 *      test said no and the seeder rebuilt content an editor had removed on
 *      purpose. Deleting a seeded post is a decision. The record honours it.
 *
 * Rebuttals are inserted before Doubts, because each Doubt points at a Rebuttal
 * by ID and the target has to exist before the pointer can be written.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

require_once get_stylesheet_directory() . '/inc/seed-data.php';

/**
 * Every slug the seeder has ever created (or adopted), keyed by post type.
 *
 * @return array<string,string[]>
 */
function murtadd_seeded_slugs() {
	$v = get_option( 'murtadd_seeded_slugs', array() );
	return is_array( $v ) ? $v : array();
}

/**
 * Whether a slug has already been seeded once, regardless of the post's fate.
 *
 * @param string $type Post type.
 * @param string $slug Slug.
 * @return bool
 */
function murtadd_slug_was_seeded( $type, $slug ) {
	$seeded = murtadd_seeded_slugs();
	return in_array( $slug, isset( $seeded[ $type ] ) ? $seeded[ $type ] : array(), true );
}

/**
 * Record a slug as seeded.
 *
 * Also called when the seeder meets a post that predates the record, so
 * installs seeded before 1.27.0 gain the same protection on their next run.
 *
 * @param string $type Post type.
 * @param string $slug Slug.
 * @return void
 */
function murtadd_mark_slug_seeded( $type, $slug ) {
	$seeded = murtadd_seeded_slugs();
	if ( ! in_array( $slug, isset( $seeded[ $type ] ) ? $seeded[ $type ] : array(), true ) ) {
		$seeded[ $type ][] = $slug;
		update_option( 'murtadd_seeded_slugs', $seeded );
	}
}

/**
 * Ensure the topic terms exist.
 *
 * @return void
 */
function murtadd_seed_terms() {
	foreach ( murtadd_starter_topics() as $slug => $name ) {
		if ( ! term_exists( $slug, 'murtadd_topic' ) ) {
			wp_insert_term( $name, 'murtadd_topic', array( 'slug' => $slug ) );
		}
	}
}

/**
 * Insert the rebuttals.
 *
 * @return int Number created on this run.
 */
function murtadd_seed_rebuttal_posts() {
	$created = 0;

	foreach ( murtadd_seed_rebuttals() as $r ) {
		if ( murtadd_slug_was_seeded( 'murtadd_rebuttal', $r['slug'] ) ) {
			continue; // Seeded once already. If it is gone, an editor removed it.
		}
		if ( get_page_by_path( $r['slug'], OBJECT, 'murtadd_rebuttal' ) ) {
			murtadd_mark_slug_seeded( 'murtadd_rebuttal', $r['slug'] ); // Pre-record install: adopt it.
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'murtadd_rebuttal',
				'post_name'    => $r['slug'],
				'post_title'   => $r['title'],
				'post_content' => $r['body'],
				'post_status'  => 'publish',
			)
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		murtadd_mark_slug_seeded( 'murtadd_rebuttal', $r['slug'] );
		update_post_meta( $post_id, '_murtadd_claim', $r['claim'] );
		if ( ! empty( $r['ce_slug'] ) ) {
			update_post_meta( $post_id, '_murtadd_ce_slug', sanitize_title( $r['ce_slug'] ) );
		}
		update_post_meta( $post_id, '_murtadd_claim_source_type', $r['source_type'] );
		update_post_meta( $post_id, '_murtadd_sources', $r['sources'] );
		wp_set_object_terms( $post_id, $r['topics'], 'murtadd_topic' );

		++$created;
	}

	return $created;
}

/**
 * Insert the doubts, wiring each one to its rebuttal.
 *
 * @return int Number created on this run.
 */
function murtadd_seed_doubt_posts() {
	$created = 0;

	foreach ( murtadd_seed_doubts() as $d ) {
		if ( murtadd_slug_was_seeded( 'murtadd_doubt', $d['slug'] ) ) {
			continue; // Seeded once already. If it is gone, an editor removed it.
		}
		if ( get_page_by_path( $d['slug'], OBJECT, 'murtadd_doubt' ) ) {
			murtadd_mark_slug_seeded( 'murtadd_doubt', $d['slug'] ); // Pre-record install: adopt it.
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => 'murtadd_doubt',
				'post_name'   => $d['slug'],
				'post_title'  => $d['title'],
				'post_status' => 'publish',
			)
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		murtadd_mark_slug_seeded( 'murtadd_doubt', $d['slug'] );
		update_post_meta( $post_id, '_murtadd_doubt_statement', $d['statement'] );
		if ( ! empty( $d['ce_slug'] ) ) {
			update_post_meta( $post_id, '_murtadd_ce_slug', sanitize_title( $d['ce_slug'] ) );
		}
		update_post_meta( $post_id, '_murtadd_short_response', $d['response'] );

		wp_set_object_terms( $post_id, $d['topics'], 'murtadd_topic' );
		wp_set_object_terms( $post_id, array( $d['category'] ), 'murtadd_doubt_category' );

		// The rebuttal exists by now, so the pointer can be written directly.
		// '' means the doubt deliberately has no rebuttal (the fear doubt).
		$rebuttal = $d['related'] ? get_page_by_path( $d['related'], OBJECT, 'murtadd_rebuttal' ) : null;
		if ( $rebuttal ) {
			update_post_meta( $post_id, '_murtadd_related_rebuttal', (int) $rebuttal->ID );
			update_post_meta( $rebuttal->ID, '_murtadd_related_doubt', (int) $post_id );
		}

		++$created;
	}

	return $created;
}

/**
 * Insert the site's open letters.
 *
 * Only letters the site writes in its own voice (murtadd_seed_letters()) are
 * seeded, never correspondence presented as from a reader. Each is marked
 * with the open-letter kind, so the single view frames it as such. The same
 * record as every other seeded type: a slug seeded once is never seeded again.
 *
 * @return int Number created on this run.
 */
function murtadd_seed_letter_posts() {
	$created = 0;

	foreach ( murtadd_seed_letters() as $l ) {
		if ( murtadd_slug_was_seeded( 'murtadd_letter', $l['slug'] ) ) {
			continue; // Seeded once already. If it is gone, an editor removed it.
		}
		if ( get_page_by_path( $l['slug'], OBJECT, 'murtadd_letter' ) ) {
			murtadd_mark_slug_seeded( 'murtadd_letter', $l['slug'] ); // A letter already at that slug is the editor's: adopt the slug, touch nothing.
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'murtadd_letter',
				'post_name'    => $l['slug'],
				'post_title'   => $l['title'],
				'post_content' => $l['body'],
				'post_status'  => 'publish',
			)
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		murtadd_mark_slug_seeded( 'murtadd_letter', $l['slug'] );
		update_post_meta( $post_id, '_murtadd_letter_kind', 'open' );
		update_post_meta( $post_id, '_murtadd_letter_date', $l['date'] );
		if ( ! empty( $l['note'] ) ) {
			update_post_meta( $post_id, '_murtadd_letter_note', $l['note'] );
		}

		++$created;
	}

	return $created;
}

/**
 * Seed the starter content. Idempotent.
 *
 * Letters: only the site's own open letters, never reader correspondence.
 *
 * @return array{doubts:int,rebuttals:int,letters:int}
 */
function murtadd_seed_content() {
	murtadd_seed_terms();

	$rebuttals = murtadd_seed_rebuttal_posts();
	$doubts    = murtadd_seed_doubt_posts();
	$letters   = murtadd_seed_letter_posts();

	update_option( 'murtadd_content_seeded', 1 );

	return array(
		'doubts'    => $doubts,
		'rebuttals' => $rebuttals,
		'letters'   => $letters,
	);
}

/**
 * How much starter content is missing.
 *
 * @return int
 */
function murtadd_seed_missing_count() {
	$missing = 0;

	foreach ( murtadd_seed_rebuttals() as $r ) {
		if ( ! murtadd_slug_was_seeded( 'murtadd_rebuttal', $r['slug'] ) && ! get_page_by_path( $r['slug'], OBJECT, 'murtadd_rebuttal' ) ) {
			++$missing;
		}
	}
	foreach ( murtadd_seed_doubts() as $d ) {
		if ( ! murtadd_slug_was_seeded( 'murtadd_doubt', $d['slug'] ) && ! get_page_by_path( $d['slug'], OBJECT, 'murtadd_doubt' ) ) {
			++$missing;
		}
	}
	foreach ( murtadd_seed_letters() as $l ) {
		if ( ! murtadd_slug_was_seeded( 'murtadd_letter', $l['slug'] ) && ! get_page_by_path( $l['slug'], OBJECT, 'murtadd_letter' ) ) {
			++$missing;
		}
	}

	return $missing;
}

/**
 * Backfill the counterpart slug on posts the seeder created.
 *
 * The seeder never touches a post it has already created, so a ce_slug added
 * to the seed data after a site was set up would never reach it. This fills
 * `_murtadd_ce_slug` only where it is EMPTY, only on slugs recorded in
 * murtadd_seeded_slugs, and only from the seed data. A value an editor typed,
 * or cleared and then retyped, is never replaced; a post the editor deleted is
 * simply not found. Re-running changes nothing, and the run is skipped
 * entirely once the current mapping has been applied.
 *
 * @return int Number of posts that gained a ce_slug on this run.
 */
function murtadd_backfill_ce_slugs() {
	$map = array(
		'murtadd_rebuttal' => array_column( murtadd_seed_rebuttals(), 'ce_slug', 'slug' ),
		'murtadd_doubt'    => array_column( murtadd_seed_doubts(), 'ce_slug', 'slug' ),
	);
	$hash = md5( (string) wp_json_encode( $map ) );
	if ( get_option( 'murtadd_ce_slug_backfill' ) === $hash ) {
		return 0;
	}

	$seeded = murtadd_seeded_slugs();
	$filled = 0;

	foreach ( $map as $type => $pairs ) {
		foreach ( $pairs as $slug => $ce_slug ) {
			if ( '' === (string) $ce_slug || ! in_array( $slug, isset( $seeded[ $type ] ) ? $seeded[ $type ] : array(), true ) ) {
				continue;
			}
			$post = get_page_by_path( $slug, OBJECT, $type );
			if ( ! $post || '' !== (string) get_post_meta( $post->ID, '_murtadd_ce_slug', true ) ) {
				continue;
			}
			update_post_meta( $post->ID, '_murtadd_ce_slug', sanitize_title( $ce_slug ) );
			++$filled;
		}
	}

	update_option( 'murtadd_ce_slug_backfill', $hash );
	return $filled;
}
add_action( 'admin_init', 'murtadd_backfill_ce_slugs' );
