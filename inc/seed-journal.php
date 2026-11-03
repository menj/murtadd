<?php
/**
 * The Journal: dated posts at /journal/, and the page that holds them.
 *
 * Guides stay a post type of their own (inc/cpt-guide.php): they are
 * evergreen and the sister sites link to fixed addresses. The Journal holds
 * what is dated: standard posts, rendered by home.php and single.php, listed
 * on the posts page, which is the "journal" page (owner, 2026-11-03).
 *
 * The posts are JSON in inc/data/seed/journal.json, decoded and validated as
 * the other seed files are (inc/seed-data.php). Each carries a fixed
 * post_date. A date already past is published; a date still ahead is
 * stored as "future", and WordPress publishes it on its day. The seeder
 * never overwrites, never destroys and never resurrects: every slug it
 * creates or adopts is recorded under "post" in murtadd_seeded_slugs, so a
 * second run changes nothing.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

require_once get_stylesheet_directory() . '/inc/seed-content.php';

/**
 * The Journal posts.
 *
 * @return array<int,array>
 */
function murtadd_seed_journal() {
	static $posts = null;
	if ( null === $posts ) {
		$posts = murtadd_seed_load_entries(
			'journal',
			array(
				'slug'    => 'string',
				'title'   => 'string',
				'date'    => 'string',
				'excerpt' => 'string',
				'body'    => 'string',
			),
			array(
				'focus_keyword' => 'string',
			)
		);
		// A post without a readable date would be published "now", which is
		// the drift fixed dates exist to prevent: the whole file is refused.
		foreach ( $posts as $p ) {
			if ( ! preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $p['date'] ) ) {
				$posts = array();
				break;
			}
		}
	}
	return $posts;
}

/**
 * The status a Journal post is inserted with: "future" while its date is
 * ahead, "publish" once it has passed.
 *
 * @param string   $date_gmt Post date, GMT, "Y-m-d H:i:s".
 * @param int|null $now      Unix time; the current time when null.
 * @return string
 */
function murtadd_journal_status( $date_gmt, $now = null ) {
	$now = null === $now ? time() : (int) $now;
	$ts  = strtotime( $date_gmt . ' UTC' );
	return ( false !== $ts && $ts > $now ) ? 'future' : 'publish';
}

/**
 * The GMT form of a seed date, which is written in the site's time zone.
 *
 * @param string $date Local "Y-m-d H:i:s".
 * @return string
 */
function murtadd_journal_date_gmt( $date ) {
	return function_exists( 'get_gmt_from_date' ) ? (string) get_gmt_from_date( $date ) : $date;
}

/**
 * Run murtadd_seed_journal_posts_run() inside the seeding flag, which lets the spam gate
 * (inc/security.php) pass starter content that carries the seed marker.
 *
 * @param int|null $now Unix time; the current time when null.
 * @return mixed What murtadd_seed_journal_posts_run() returns.
 */
function murtadd_seed_journal_posts( $now = null ) {
	$was                        = ! empty( $GLOBALS['murtadd_seeding'] );
	$GLOBALS['murtadd_seeding'] = true;
	try {
		return murtadd_seed_journal_posts_run( $now );
	} finally {
		$GLOBALS['murtadd_seeding'] = $was;
	}
}

/**
 * Insert the Journal posts. Idempotent.
 *
 * @param int|null $now Unix time; the current time when null.
 * @return int Number created on this run.
 */
function murtadd_seed_journal_posts_run( $now = null ) {
	$created = 0;

	foreach ( murtadd_seed_journal() as $p ) {
		if ( murtadd_slug_was_seeded( 'post', $p['slug'] ) ) {
			continue; // Seeded once already. If it is gone, an editor removed it.
		}
		if ( get_page_by_path( $p['slug'], OBJECT, 'post' ) ) {
			murtadd_mark_slug_seeded( 'post', $p['slug'] ); // Already there: adopt it.
			continue;
		}

		$gmt     = murtadd_journal_date_gmt( $p['date'] );
		$post_id = wp_insert_post(
			array(
				'post_type'     => 'post',
				'post_name'     => $p['slug'],
				'post_title'    => $p['title'],
				'post_excerpt'  => $p['excerpt'],
				'post_content'  => murtadd_seed_resolve_urls( $p['body'] ),
				'post_status'   => murtadd_journal_status( $gmt, $now ),
				'post_date'     => $p['date'],
				'post_date_gmt' => $gmt,
				'edit_date'     => true,
				'meta_input'    => array( '_murtadd_seeded' => 1 ),
			)
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		murtadd_mark_slug_seeded( 'post', $p['slug'] );
		if ( ! empty( $p['focus_keyword'] ) ) {
			update_post_meta( $post_id, 'rank_math_focus_keyword', $p['focus_keyword'] );
		}

		++$created;
	}

	return $created;
}

/**
 * Run the Journal seeder once per change to the post list.
 *
 * @return int Number created on this run.
 */
function murtadd_maybe_seed_journal() {
	$hash = md5( (string) wp_json_encode( array_column( murtadd_seed_journal(), 'slug' ) ) );
	if ( get_option( 'murtadd_journal_seeded' ) === $hash ) {
		return 0;
	}
	murtadd_journal_page_once();
	$created = murtadd_seed_journal_posts();
	update_option( 'murtadd_journal_seeded', $hash );
	return $created;
}
add_action( 'admin_init', 'murtadd_maybe_seed_journal' );

/**
 * The Journal's address: the posts page, or /journal/ until one is set.
 *
 * @return string
 */
function murtadd_journal_url() {
	$page = (int) get_option( 'page_for_posts' );
	$link = $page ? get_permalink( $page ) : '';
	return $link ? (string) $link : home_url( '/journal/' );
}

/**
 * Move a site set up before 1.51.0 from the seeded Blog page to the Journal.
 *
 * Before 1.51.0 activation created a "blog" page as the posts page. Its text
 * is never displayed (home.php renders the index), so the seeded page is
 * renamed in place: same ID, new slug and title. A blog page an editor made,
 * without the seed marker, is left alone and a Journal page is created
 * beside it. The posts page is pointed at the Journal only when it is unset
 * or still the seeded Blog page; a choice the owner made is kept. /blog/
 * answers with a 301 from the legacy map (inc/legacy-redirects.php).
 *
 * @return string What was done: "renamed", "created", "present" or "".
 */
function murtadd_journal_page_once() {
	$done = '';
	$blog = get_page_by_path( 'blog' );
	$old  = ( $blog && get_post_meta( $blog->ID, '_murtadd_seeded', true ) ) ? (int) $blog->ID : 0;

	$journal = get_page_by_path( 'journal' );
	if ( $journal ) {
		$done = 'present';
	} elseif ( $old ) {
		$id = wp_update_post(
			array(
				'ID'         => $old,
				'post_name'  => 'journal',
				'post_title' => __( 'Journal', 'murtadd' ),
			)
		);
		if ( $id ) {
			$journal = get_page_by_path( 'journal' );
			$done    = 'renamed';
		}
	} elseif ( function_exists( 'murtadd_create_missing_pages' ) ) {
		murtadd_create_missing_pages();
		$journal = get_page_by_path( 'journal' );
		$done    = $journal ? 'created' : '';
	}

	if ( $journal ) {
		$current = (int) get_option( 'page_for_posts' );
		if ( ! $current || ( $old && $current === $old ) ) {
			update_option( 'page_for_posts', (int) $journal->ID );
		}
	}
	return $done;
}
