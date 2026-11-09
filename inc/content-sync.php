<?php
/**
 * Seeded content that follows the bundle, safely.
 *
 * The seeders (seed-content.php, seed-guides.php, seed-journal.php) create each item once and never
 * overwrite it, so a corrected text in a release never reached a site and a dropped item stayed live.
 * This file closes both gaps without ever touching anything the owner wrote.
 *
 * Fingerprints. For every item the theme creates, the stored text (title, body, the fields it owns) is
 * fingerprinted and recorded in post meta (_murtadd_sync_fp) when the theme writes it. The item counts as
 * edited when its stored text no longer matches that record, and also when no record exists: without proof
 * that it is untouched, it is treated as edited.
 *
 * What a sync does with an item the theme created (marked _murtadd_seeded):
 *
 *   current     the stored text equals the bundle: nothing to do (the fingerprint is recorded)
 *   update      untouched by the owner, bundle text changed: takes the new text
 *   newer       edited by the owner, bundle text changed: listed in one notice, with a diff, and the owner
 *               takes the bundled text or keeps their own
 *   trash       dropped from the bundle and untouched: moved to the Trash, never deleted
 *   removed     dropped from the bundle and edited: listed in the same notice
 *
 * Supersedes. The map in inc/data/seed/supersedes.json (and the murtadd_supersedes filter) is
 * { post type: { old slug: new slug } }. While the new item is published and the old one is untouched, the
 * old one answers with a 301, is hidden from lists, search, feeds and sitemaps (through the unlist module),
 * carries noindex, and is moved to the Trash. An old item the owner edited is listed in the notice instead.
 * The map ships empty.
 *
 * Guards. Removals (the Trash steps) stop when a seed file fails its checksum
 * (inc/data/seed/checksums.json, written by bin/seed-checksums.php), when the number of bundled items fell
 * by more than 10 percent since the last good sync, or when MURTADD_DISABLE_CONTENT_SYNC is true. The
 * constant also switches off updates, the redirect and the hiding.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/** The post types the seeders fill. */
function murtadd_sync_types() {
	return array( 'murtadd_rebuttal', 'murtadd_doubt', 'murtadd_guide', 'post' );
}

/**
 * The kill switch.
 *
 * @return bool
 */
function murtadd_sync_disabled() {
	return defined( 'MURTADD_DISABLE_CONTENT_SYNC' ) && MURTADD_DISABLE_CONTENT_SYNC;
}

/**
 * The texts the theme owns for one bundled entry: what a sync compares, writes and fingerprints.
 *
 * @param string $type  Post type.
 * @param array  $entry Seed entry.
 * @return array<string,mixed>
 */
function murtadd_sync_fields_from_bundle( $type, $entry ) {
	switch ( $type ) {
		case 'murtadd_rebuttal':
			return array(
				'title'   => (string) $entry['title'],
				'content' => (string) $entry['body'],
				'claim'   => (string) $entry['claim'],
				'sources' => (array) $entry['sources'],
			);
		case 'murtadd_doubt':
			return array(
				'title'     => (string) $entry['title'],
				'statement' => (string) $entry['statement'],
				'response'  => (string) $entry['response'],
			);
		case 'murtadd_guide':
			return array(
				'title'   => (string) $entry['title'],
				'excerpt' => (string) $entry['excerpt'],
				'content' => murtadd_guide_content( $entry ),
			);
		default:
			return array(
				'title'   => (string) $entry['title'],
				'excerpt' => (string) $entry['excerpt'],
				'content' => murtadd_seed_resolve_urls( (string) $entry['body'] ),
			);
	}
}

/**
 * The same fields as the post stores them.
 *
 * @param string  $type Post type.
 * @param WP_Post $post Post.
 * @return array<string,mixed>
 */
function murtadd_sync_fields_from_post( $type, $post ) {
	switch ( $type ) {
		case 'murtadd_rebuttal':
			return array(
				'title'   => (string) ( $post->post_title ?? '' ),
				'content' => (string) ( $post->post_content ?? '' ),
				'claim'   => (string) get_post_meta( $post->ID, '_murtadd_claim', true ),
				'sources' => (array) get_post_meta( $post->ID, '_murtadd_sources', true ),
			);
		case 'murtadd_doubt':
			return array(
				'title'     => (string) ( $post->post_title ?? '' ),
				'statement' => (string) get_post_meta( $post->ID, '_murtadd_doubt_statement', true ),
				'response'  => (string) get_post_meta( $post->ID, '_murtadd_short_response', true ),
			);
		default:
			return array(
				'title'   => (string) ( $post->post_title ?? '' ),
				'excerpt' => (string) ( $post->post_excerpt ?? '' ),
				'content' => (string) ( $post->post_content ?? '' ),
			);
	}
}

/**
 * Fingerprint of a set of fields.
 *
 * @param array $fields Fields.
 * @return string
 */
function murtadd_sync_fp( $fields ) {
	return md5( (string) wp_json_encode( $fields ) );
}

/**
 * What a sync does with one existing item. Pure.
 *
 * @param string $bundle_fp   Fingerprint of the bundled text.
 * @param string $stored_fp   Fingerprint of the stored text now.
 * @param string $recorded_fp Fingerprint recorded when the theme last wrote it, '' when none.
 * @param string $ack         Bundle fingerprint the owner chose to keep their text against.
 * @return string current|update|newer|kept
 */
function murtadd_sync_decide( $bundle_fp, $stored_fp, $recorded_fp, $ack ) {
	if ( $stored_fp === $bundle_fp ) {
		return 'current';
	}
	$edited = '' === $recorded_fp || $recorded_fp !== $stored_fp;
	if ( ! $edited ) {
		return 'update';
	}
	return $ack === $bundle_fp ? 'kept' : 'newer';
}

/**
 * What a sync does with an item the bundle no longer carries. Pure.
 *
 * @param string $stored_fp   Fingerprint of the stored text now.
 * @param string $recorded_fp Fingerprint recorded when the theme last wrote it, '' when none.
 * @param string $ack         The owner's earlier choice ('removed' keeps it quiet).
 * @return string trash|removed|kept
 */
function murtadd_sync_decide_removed( $stored_fp, $recorded_fp, $ack ) {
	if ( '' !== $recorded_fp && $recorded_fp === $stored_fp ) {
		return 'trash';
	}
	return 'removed' === $ack ? 'kept' : 'removed';
}

/**
 * Whether removals may run. Pure.
 *
 * @param bool $disabled    The kill switch.
 * @param bool $intact      Every seed file matches its checksum and loaded.
 * @param int  $count_now   Bundled items now.
 * @param int  $count_prior Bundled items at the last good sync, 0 when none.
 * @return string '' when allowed, otherwise the reason.
 */
function murtadd_sync_guard( $disabled, $intact, $count_now, $count_prior ) {
	if ( $disabled ) {
		return 'the kill switch MURTADD_DISABLE_CONTENT_SYNC is on';
	}
	if ( ! $intact ) {
		return 'a seed file is missing or fails its checksum';
	}
	if ( $count_prior > 0 && $count_now < (int) floor( $count_prior * 0.9 ) ) {
		return 'the bundle lists more than 10 percent fewer items than at the last good sync';
	}
	return '';
}

/**
 * The seed files covered by checksums.
 *
 * @return string[]
 */
function murtadd_sync_checked_files() {
	return array( 'rebuttals', 'doubts', 'guides', 'journal', 'supersedes' );
}

/**
 * Whether every seed file matches the checksum shipped with the theme.
 *
 * @return bool
 */
function murtadd_sync_checksums_ok() {
	$sums = murtadd_seed_read_json( 'checksums' );
	if ( ! $sums ) {
		return false;
	}
	foreach ( murtadd_sync_checked_files() as $name ) {
		$file = __DIR__ . '/data/seed/' . $name . '.json';
		if ( ! isset( $sums[ $name ] ) || ! is_readable( $file ) || md5_file( $file ) !== $sums[ $name ] ) {
			return false;
		}
	}
	return true;
}

/**
 * Every bundled item: key "type:slug" => array( type, slug, title, fields, fp ).
 *
 * @return array<string,array>
 */
function murtadd_sync_bundle() {
	$sets = array(
		'murtadd_rebuttal' => murtadd_seed_rebuttals(),
		'murtadd_doubt'    => murtadd_seed_doubts(),
		'murtadd_guide'    => murtadd_seed_guides(),
		'post'             => murtadd_seed_journal(),
	);
	$out  = array();
	foreach ( $sets as $type => $entries ) {
		foreach ( $entries as $entry ) {
			$fields = murtadd_sync_fields_from_bundle( $type, $entry );
			$out[ $type . ':' . $entry['slug'] ] = array(
				'type'   => $type,
				'slug'   => (string) $entry['slug'],
				'title'  => (string) $entry['title'],
				'fields' => $fields,
				'fp'     => murtadd_sync_fp( $fields ),
			);
		}
	}
	return $out;
}

/**
 * Record the stored text of a post as the theme's own. Called when the theme writes or creates an item.
 *
 * @param int    $post_id Post ID.
 * @param string $type    Post type.
 * @return void
 */
function murtadd_sync_record( $post_id, $type ) {
	$post = get_post( $post_id );
	if ( $post ) {
		update_post_meta( $post_id, '_murtadd_sync_fp', murtadd_sync_fp( murtadd_sync_fields_from_post( $type, $post ) ) );
		delete_post_meta( $post_id, '_murtadd_sync_ack' );
	}
}

/**
 * The supersedes map, cleaned: type => ( old slug => new slug ).
 *
 * @return array<string,array<string,string>>
 */
function murtadd_supersedes() {
	$raw = murtadd_seed_read_json( 'supersedes' );
	$raw = apply_filters( 'murtadd_supersedes', is_array( $raw ) ? $raw : array() );
	$map = array();
	foreach ( is_array( $raw ) ? $raw : array() as $type => $pairs ) {
		if ( ! in_array( $type, murtadd_sync_types(), true ) || ! is_array( $pairs ) ) {
			continue;
		}
		foreach ( $pairs as $old => $new ) {
			$old = sanitize_title( (string) $old );
			$new = sanitize_title( (string) $new );
			if ( '' !== $old && '' !== $new && $old !== $new ) {
				$map[ $type ][ $old ] = $new;
			}
		}
	}
	return $map;
}

/**
 * The post a theme-created item lives in, or null. Items the owner created are never returned.
 *
 * @param string $type Post type.
 * @param string $slug Slug.
 * @return WP_Post|null
 */
function murtadd_sync_post( $type, $slug ) {
	$post = get_page_by_path( $slug, OBJECT, $type );
	return ( $post && get_post_meta( $post->ID, '_murtadd_seeded', true ) ) ? $post : null;
}

/**
 * The state of a post against its own record: stored fingerprint, recorded fingerprint, owner's choice.
 *
 * @param string  $type Post type.
 * @param WP_Post $post Post.
 * @return array{stored:string,recorded:string,ack:string}
 */
function murtadd_sync_state( $type, $post ) {
	return array(
		'stored'   => murtadd_sync_fp( murtadd_sync_fields_from_post( $type, $post ) ),
		'recorded' => (string) get_post_meta( $post->ID, '_murtadd_sync_fp', true ),
		'ack'      => (string) get_post_meta( $post->ID, '_murtadd_sync_ack', true ),
	);
}

/**
 * Superseded pairs that qualify now: old post ID => new post ID. Both live, the old one made by the theme and
 * untouched by the owner.
 *
 * @return array<int,int>
 */
function murtadd_superseded_pairs() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$cache = array();
	if ( murtadd_sync_disabled() ) {
		return $cache;
	}
	foreach ( murtadd_supersedes() as $type => $pairs ) {
		foreach ( $pairs as $old => $new ) {
			$old_post = murtadd_sync_post( $type, $old );
			$new_post = get_page_by_path( $new, OBJECT, $type );
			if ( ! $old_post || ! $new_post || 'publish' !== $old_post->post_status || 'publish' !== $new_post->post_status || $old_post->ID === $new_post->ID ) {
				continue;
			}
			$state = murtadd_sync_state( $type, $old_post );
			if ( '' !== $state['recorded'] && $state['recorded'] === $state['stored'] ) {
				$cache[ (int) $old_post->ID ] = (int) $new_post->ID;
			}
		}
	}
	return $cache;
}

/**
 * IDs of the superseded posts, for the unlist module.
 *
 * @return int[]
 */
function murtadd_superseded_ids() {
	return array_keys( murtadd_superseded_pairs() );
}

/**
 * The plan: one row per item that needs attention or can be told about.
 *
 * @return array<int,array{key:string,type:string,slug:string,title:string,post_id:int,action:string}>
 */
function murtadd_sync_plan() {
	$bundle = murtadd_sync_bundle();
	$rows   = array();
	foreach ( $bundle as $key => $item ) {
		$post = murtadd_sync_post( $item['type'], $item['slug'] );
		if ( ! $post ) {
			continue; // Never created, or removed by the owner: the seeders decide, never this file.
		}
		$s        = murtadd_sync_state( $item['type'], $post );
		$action   = murtadd_sync_decide( $item['fp'], $s['stored'], $s['recorded'], $s['ack'] );
		$rows[]   = array( 'key' => $key, 'type' => $item['type'], 'slug' => $item['slug'], 'title' => $post->post_title, 'post_id' => (int) $post->ID, 'action' => $action );
	}
	$seeded = murtadd_seeded_slugs();
	foreach ( murtadd_sync_types() as $type ) {
		foreach ( (array) ( $seeded[ $type ] ?? array() ) as $slug ) {
			$key = $type . ':' . $slug;
			if ( isset( $bundle[ $key ] ) || ( function_exists( 'murtadd_slug_rename_pending' ) && murtadd_slug_rename_pending( $type, $slug ) ) ) {
				continue;
			}
			$post = murtadd_sync_post( $type, $slug );
			if ( ! $post || 'trash' === $post->post_status ) {
				continue;
			}
			$s       = murtadd_sync_state( $type, $post );
			$rows[]  = array( 'key' => $key, 'type' => $type, 'slug' => $slug, 'title' => $post->post_title, 'post_id' => (int) $post->ID, 'action' => murtadd_sync_decide_removed( $s['stored'], $s['recorded'], $s['ack'] ) );
		}
	}
	// A superseded item that qualifies is retired; one the owner edited waits for a choice.
	$pairs = murtadd_superseded_pairs();
	foreach ( murtadd_supersedes() as $type => $map ) {
		foreach ( $map as $old => $new ) {
			$old_post = murtadd_sync_post( $type, $old );
			$new_post = get_page_by_path( $new, OBJECT, $type );
			if ( ! $old_post || ! $new_post || 'publish' !== $old_post->post_status || 'publish' !== $new_post->post_status ) {
				continue;
			}
			$ack      = (string) get_post_meta( $old_post->ID, '_murtadd_sync_ack', true );
			$rows[]   = array(
				'key'     => $type . ':' . $old,
				'type'    => $type,
				'slug'    => $old,
				'title'   => $old_post->post_title,
				'post_id' => (int) $old_post->ID,
				'action'  => isset( $pairs[ $old_post->ID ] ) ? 'supersede' : ( 'superseded' === $ack ? 'kept' : 'superseded' ),
			);
		}
	}
	return $rows;
}

/**
 * Append to the change log (the last 100 lines).
 *
 * @param string $key    Item.
 * @param string $action What happened.
 * @return void
 */
function murtadd_sync_log( $key, $action ) {
	$log   = (array) get_option( 'murtadd_sync_log', array() );
	$log[] = array( 't' => time(), 'key' => $key, 'action' => $action );
	update_option( 'murtadd_sync_log', array_slice( $log, -100 ), false );
}

/**
 * Write a bundled item's text into its post, inside the seeding flag so the spam gate lets it pass, and
 * record the fingerprint.
 *
 * @param WP_Post $post Post.
 * @param array   $item Bundle item.
 * @return bool
 */
function murtadd_sync_write( $post, $item ) {
	$was                        = ! empty( $GLOBALS['murtadd_seeding'] );
	$GLOBALS['murtadd_seeding'] = true;
	try {
		$f    = $item['fields'];
		$args = array( 'ID' => $post->ID, 'post_title' => $f['title'] );
		if ( isset( $f['content'] ) ) {
			$args['post_content'] = $f['content'];
		}
		if ( isset( $f['excerpt'] ) ) {
			$args['post_excerpt'] = $f['excerpt'];
		}
		$done = wp_update_post( $args );
		if ( ! $done || is_wp_error( $done ) ) {
			return false;
		}
		if ( 'murtadd_rebuttal' === $item['type'] ) {
			update_post_meta( $post->ID, '_murtadd_claim', $f['claim'] );
			update_post_meta( $post->ID, '_murtadd_sources', $f['sources'] );
		} elseif ( 'murtadd_doubt' === $item['type'] ) {
			update_post_meta( $post->ID, '_murtadd_doubt_statement', $f['statement'] );
			update_post_meta( $post->ID, '_murtadd_short_response', $f['response'] );
		}
		murtadd_sync_record( $post->ID, $item['type'] );
		return true;
	} finally {
		$GLOBALS['murtadd_seeding'] = $was;
	}
}

/**
 * Whether removals may run now.
 *
 * @return string '' when allowed, otherwise the reason.
 */
function murtadd_sync_removal_block() {
	return murtadd_sync_guard( murtadd_sync_disabled(), murtadd_sync_checksums_ok() && murtadd_seed_rebuttals() && murtadd_seed_doubts(), count( murtadd_sync_bundle() ), (int) get_option( 'murtadd_sync_count', 0 ) );
}

/**
 * Run a sync: update untouched items, retire what the bundle dropped or superseded when no guard stops it, and
 * list everything that waits for the owner.
 *
 * @return array{updated:int,trashed:int,waiting:int,blocked:string}
 */
function murtadd_sync_apply() {
	$out = array( 'updated' => 0, 'trashed' => 0, 'waiting' => 0, 'blocked' => '' );
	if ( murtadd_sync_disabled() ) {
		$out['blocked'] = murtadd_sync_removal_block();
		return $out;
	}
	$bundle  = murtadd_sync_bundle();
	$blocked = murtadd_sync_removal_block();
	$out['blocked'] = $blocked;
	foreach ( murtadd_sync_plan() as $row ) {
		$post = get_post( $row['post_id'] );
		if ( ! $post ) {
			continue;
		}
		switch ( $row['action'] ) {
			case 'current':
				if ( '' === (string) get_post_meta( $post->ID, '_murtadd_sync_fp', true ) ) {
					murtadd_sync_record( $post->ID, $row['type'] ); // Equal to the bundle: proof enough to record it.
				}
				break;
			case 'update':
				if ( isset( $bundle[ $row['key'] ] ) && murtadd_sync_write( $post, $bundle[ $row['key'] ] ) ) {
					++$out['updated'];
					murtadd_sync_log( $row['key'], 'updated to the bundled text' );
				}
				break;
			case 'trash':
			case 'supersede':
				if ( '' === $blocked && 'trash' !== $post->post_status ) {
					if ( wp_trash_post( $post->ID ) ) {
						++$out['trashed'];
						murtadd_sync_log( $row['key'], 'supersede' === $row['action'] ? 'moved to the Trash, replaced' : 'moved to the Trash, dropped from the bundle' );
					}
				}
				break;
		}
	}
	$pending = array();
	foreach ( murtadd_sync_plan() as $row ) {
		if ( in_array( $row['action'], array( 'newer', 'removed', 'superseded', 'trash', 'supersede' ), true ) ) {
			if ( in_array( $row['action'], array( 'trash', 'supersede' ), true ) && '' === $blocked ) {
				continue;
			}
			$pending[ $row['key'] ] = $row;
		}
	}
	update_option( 'murtadd_sync_pending', $pending, false );
	$out['waiting'] = count( $pending );
	if ( '' === $blocked ) {
		update_option( 'murtadd_sync_count', count( $bundle ), false );
	}
	return $out;
}

/**
 * Run the sync when the bundle or the theme changed, and only for someone who may manage options.
 *
 * @return void
 */
function murtadd_sync_maybe_run() {
	if ( murtadd_sync_disabled() || wp_doing_ajax() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$fps = array();
	foreach ( murtadd_sync_bundle() as $key => $item ) {
		$fps[ $key ] = $item['fp'];
	}
	$fp = md5( MURTADD_VERSION . wp_json_encode( $fps ) . wp_json_encode( murtadd_supersedes() ) );
	if ( get_option( 'murtadd_sync_fp' ) === $fp ) {
		return;
	}
	murtadd_sync_apply();
	update_option( 'murtadd_sync_fp', $fp, false );
}
add_action( 'admin_init', 'murtadd_sync_maybe_run', 30 );

/* -------------------------------------------------------------------------
 * The owner's choices, the notice and the screen.
 * ---------------------------------------------------------------------- */

/** Where the review lives: Appearance, Murtadd, Upkeep. */
function murtadd_sync_screen_url() {
	return add_query_arg( array( 'page' => 'murtadd', 'tab' => 'upkeep' ), admin_url( 'themes.php' ) ) . '#murtadd-content-sync';
}

/**
 * Take the bundled text for one item, retire it, or keep the owner's own. All are logged.
 */
function murtadd_sync_handle_item() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to do that.', 'murtadd' ), 403 );
	}
	check_admin_referer( 'murtadd_sync_item' );
	$key    = isset( $_POST['item'] ) ? sanitize_text_field( wp_unslash( $_POST['item'] ) ) : '';
	$choice = isset( $_POST['choice'] ) && 'take' === $_POST['choice'] ? 'take' : 'keep';
	$parts  = explode( ':', $key, 2 );
	$bundle = murtadd_sync_bundle();
	$post   = 2 === count( $parts ) && in_array( $parts[0], murtadd_sync_types(), true ) ? murtadd_sync_post( $parts[0], $parts[1] ) : null;
	if ( $post ) {
		$item = $bundle[ $key ] ?? null;
		if ( 'take' === $choice && $item ) {
			murtadd_sync_write( $post, $item );
			murtadd_sync_log( $key, 'took the bundled text' );
		} elseif ( 'take' === $choice ) {
			wp_trash_post( $post->ID ); // Dropped or superseded: the bundled choice is the Trash, never a deletion.
			murtadd_sync_log( $key, 'moved to the Trash by choice' );
		} else {
			update_post_meta( $post->ID, '_murtadd_sync_ack', $item ? $item['fp'] : ( murtadd_superseded_key( $parts[0], $parts[1] ) ? 'superseded' : 'removed' ) );
			murtadd_sync_log( $key, 'kept the owner text' );
		}
		murtadd_sync_apply();
	}
	wp_safe_redirect( murtadd_sync_screen_url() );
	exit;
}
add_action( 'admin_post_murtadd_sync_item', 'murtadd_sync_handle_item' );

/**
 * Whether a slug is the old side of a supersedes pair.
 *
 * @param string $type Post type.
 * @param string $slug Slug.
 * @return bool
 */
function murtadd_superseded_key( $type, $slug ) {
	return isset( murtadd_supersedes()[ $type ][ $slug ] );
}

/** Run the sync now, with the same rules as the automatic one. */
function murtadd_sync_handle_now() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to do that.', 'murtadd' ), 403 );
	}
	check_admin_referer( 'murtadd_sync_now' );
	murtadd_sync_apply();
	wp_safe_redirect( murtadd_sync_screen_url() );
	exit;
}
add_action( 'admin_post_murtadd_sync_now', 'murtadd_sync_handle_now' );

/** One grouped notice for everything that waits for the owner. */
function murtadd_sync_notice() {
	if ( ! current_user_can( 'manage_options' ) || murtadd_sync_disabled() ) {
		return;
	}
	$newer = array();
	$gone  = array();
	foreach ( (array) get_option( 'murtadd_sync_pending', array() ) as $row ) {
		if ( 'newer' === ( $row['action'] ?? '' ) ) {
			$newer[] = (string) $row['title'];
		} elseif ( in_array( $row['action'] ?? '', array( 'removed', 'superseded' ), true ) ) {
			$gone[] = (string) $row['title'];
		}
	}
	if ( ! $newer && ! $gone ) {
		return;
	}
	echo '<div class="notice notice-warning"><p>' . esc_html__( 'Starter content has changes for items you edited. Nothing was overwritten or trashed.', 'murtadd' ) . '</p>';
	if ( $newer ) {
		echo '<p>' . esc_html__( 'Newer version available:', 'murtadd' ) . ' ' . esc_html( implode( '; ', $newer ) ) . '</p>';
	}
	if ( $gone ) {
		echo '<p>' . esc_html__( 'Removed from the bundle or replaced:', 'murtadd' ) . ' ' . esc_html( implode( '; ', $gone ) ) . '</p>';
	}
	printf( '<p><a href="%s">%s</a></p></div>', esc_url( murtadd_sync_screen_url() ), esc_html__( 'Review them under Upkeep', 'murtadd' ) );
}
add_action( 'admin_notices', 'murtadd_sync_notice' );

/** The text of a field set as one block, for a diff. */
function murtadd_sync_blob( $fields ) {
	$out = '';
	foreach ( $fields as $name => $value ) {
		$out .= '[' . $name . "]\n" . ( is_array( $value ) ? wp_json_encode( $value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) : (string) $value ) . "\n\n";
	}
	return $out;
}

/** The Upkeep card: a dry run, the owner's choices, a log. */
function murtadd_render_content_sync() {
	$labels = array(
		'update'     => __( 'Would update (you have not edited it)', 'murtadd' ),
		'newer'      => __( 'Newer version available (you edited it, so it stays as it is)', 'murtadd' ),
		'trash'      => __( 'Would move to the Trash (dropped from the bundle, you have not edited it)', 'murtadd' ),
		'removed'    => __( 'Dropped from the bundle (you edited it, so it stays)', 'murtadd' ),
		'supersede'  => __( 'Would be redirected, hidden and moved to the Trash: its replacement is live', 'murtadd' ),
		'superseded' => __( 'Replaced, but you edited it or the theme has no record of its text, so it stays', 'murtadd' ),
		'kept'       => __( 'Kept as it is', 'murtadd' ),
	);
	echo '<div id="murtadd-content-sync">';
	murtadd_admin_card_open( __( 'Starter content and the bundle', 'murtadd' ), __( 'What the next sync would do. Nothing here has been changed. An item you edited is never overwritten or trashed.', 'murtadd' ), 'update' );
	if ( murtadd_sync_disabled() ) {
		echo '<p>' . esc_html__( 'The content sync is switched off by MURTADD_DISABLE_CONTENT_SYNC.', 'murtadd' ) . '</p>';
	}
	$blocked = murtadd_sync_removal_block();
	if ( '' !== $blocked && ! murtadd_sync_disabled() ) {
		echo '<div class="notice notice-warning inline"><p>' . esc_html( sprintf( /* translators: %s: reason. */ __( 'Removals are stopped: %s.', 'murtadd' ), $blocked ) ) . '</p></div>';
	}
	$rows = array_filter( murtadd_sync_plan(), static fn( $r ) => 'current' !== $r['action'] );
	if ( ! $rows ) {
		echo '<p>' . esc_html__( 'Every item the theme created matches the bundle.', 'murtadd' ) . '</p>';
	} else {
		$bundle = murtadd_sync_bundle();
		echo '<table class="widefat striped murtadd-admin-table"><thead><tr><th scope="col">' . esc_html__( 'Item', 'murtadd' ) . '</th><th scope="col">' . esc_html__( 'The next sync', 'murtadd' ) . '</th><th scope="col">' . esc_html__( 'Your choice', 'murtadd' ) . '</th></tr></thead><tbody>';
		foreach ( $rows as $row ) {
			echo '<tr><td>' . esc_html( $row['title'] ) . '<br><code>' . esc_html( $row['key'] ) . '</code>';
			if ( 'newer' === $row['action'] && isset( $bundle[ $row['key'] ] ) ) {
				$post = get_post( $row['post_id'] );
				if ( $post && function_exists( 'wp_text_diff' ) ) {
					$diff = wp_text_diff( murtadd_sync_blob( murtadd_sync_fields_from_post( $row['type'], $post ) ), murtadd_sync_blob( $bundle[ $row['key'] ]['fields'] ), array( 'title_left' => __( 'Yours', 'murtadd' ), 'title_right' => __( 'The bundled text', 'murtadd' ) ) );
					if ( $diff ) {
						echo '<details><summary>' . esc_html__( 'Show the differences', 'murtadd' ) . '</summary>' . $diff . '</details>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built by core's wp_text_diff(), which escapes the text.
					}
				}
			}
			echo '</td><td>' . esc_html( $labels[ $row['action'] ] ?? $row['action'] ) . '</td><td>';
			if ( in_array( $row['action'], array( 'newer', 'removed', 'superseded', 'kept' ), true ) ) {
				$take = isset( $bundle[ $row['key'] ] ) ? __( 'Take the bundled text', 'murtadd' ) : __( 'Move to the Trash', 'murtadd' );
				foreach ( array( 'take' => $take, 'keep' => __( 'Keep mine', 'murtadd' ) ) as $choice => $text ) {
					printf(
						'<form method="post" action="%1$s" class="murtadd-inline-form"><input type="hidden" name="action" value="murtadd_sync_item"><input type="hidden" name="item" value="%2$s"><input type="hidden" name="choice" value="%3$s">%4$s<button type="submit" class="button button-small">%5$s</button></form> ',
						esc_url( admin_url( 'admin-post.php' ) ),
						esc_attr( $row['key'] ),
						esc_attr( $choice ),
						wp_nonce_field( 'murtadd_sync_item', '_wpnonce', true, false ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core's nonce field.
						esc_html( $text )
					);
				}
			}
			echo '</td></tr>';
		}
		echo '</tbody></table>';
		printf(
			'<form method="post" action="%s" class="murtadd-inline-form"><input type="hidden" name="action" value="murtadd_sync_now">%s<button type="submit" class="button">%s</button></form>',
			esc_url( admin_url( 'admin-post.php' ) ),
			wp_nonce_field( 'murtadd_sync_now', '_wpnonce', true, false ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core's nonce field.
			esc_html__( 'Run the sync now', 'murtadd' )
		);
	}
	$log = array_reverse( (array) get_option( 'murtadd_sync_log', array() ) );
	echo '<h3>' . esc_html__( 'What changed', 'murtadd' ) . '</h3>';
	if ( ! $log ) {
		echo '<p>' . esc_html__( 'Nothing yet.', 'murtadd' ) . '</p>';
	} else {
		echo '<table class="widefat striped murtadd-admin-table"><thead><tr><th scope="col">' . esc_html__( 'When', 'murtadd' ) . '</th><th scope="col">' . esc_html__( 'Item', 'murtadd' ) . '</th><th scope="col">' . esc_html__( 'What happened', 'murtadd' ) . '</th></tr></thead><tbody>';
		foreach ( $log as $line ) {
			echo '<tr><td>' . esc_html( wp_date( 'Y-m-d H:i', (int) $line['t'] ) ) . '</td><td>' . esc_html( (string) $line['key'] ) . '</td><td>' . esc_html( (string) $line['action'] ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}
	murtadd_admin_card_close();
	echo '</div>';
}

/* -------------------------------------------------------------------------
 * Superseded items: the 301, and the hiding when the unlist module stands down.
 * ---------------------------------------------------------------------- */

/** The 301 from a superseded item to its replacement, ahead of the theme's other redirects. */
function murtadd_superseded_redirect() {
	if ( murtadd_sync_disabled() || is_admin() || is_preview() || is_feed() ) {
		return;
	}
	if ( is_singular() ) {
		$pairs = murtadd_superseded_pairs();
		$id    = (int) get_queried_object_id();
		if ( isset( $pairs[ $id ] ) ) {
			wp_safe_redirect( get_permalink( $pairs[ $id ] ), 301 );
			exit;
		}
	}
}
add_action( 'template_redirect', 'murtadd_superseded_redirect', 1 );

/** After the old item is in the Trash its address is a 404; it still reaches the new one. */
function murtadd_superseded_redirect_trashed() {
	if ( murtadd_sync_disabled() || ! is_404() ) {
		return;
	}
	$slug = (string) get_query_var( 'name' );
	if ( '' === $slug ) {
		return;
	}
	foreach ( murtadd_supersedes() as $type => $pairs ) {
		if ( isset( $pairs[ $slug ] ) ) {
			$new = get_page_by_path( $pairs[ $slug ], OBJECT, $type );
			if ( $new && 'publish' === $new->post_status ) {
				wp_safe_redirect( get_permalink( $new ), 301 );
				exit;
			}
		}
	}
}
add_action( 'template_redirect', 'murtadd_superseded_redirect_trashed', 2 );

/**
 * Take the superseded IDs out of a query's include list, or ask for a post that cannot exist when that empties
 * it: WordPress ignores post__not_in whenever post__in is set. Pure.
 *
 * @param array $in  Include list.
 * @param int[] $ids Superseded IDs.
 * @return int[]|null The new list, or null when the query has none.
 */
function murtadd_superseded_cut_includes( $in, $ids ) {
	$in = array_filter( array_map( 'intval', (array) $in ) );
	if ( ! $in ) {
		return null;
	}
	$in = array_values( array_diff( $in, $ids ) );
	return $in ? $in : array( 0 );
}

/**
 * While the unlist module is in charge, superseded items are part of its list (see murtadd_unlisted_ids()).
 * When it stands down (the Unlist Posts plugin is active), this hides them: out of lists, search and feeds, out
 * of the sitemaps, and noindex on their own page. An editor at work sees everything.
 */
function murtadd_superseded_fallback() {
	if ( murtadd_unlist_enabled() ) {
		return;
	}
	$hides = static function () {
		if ( murtadd_sync_disabled() || ( is_admin() && ! wp_doing_ajax() ) ) {
			return false;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST && current_user_can( 'edit_posts' ) ) {
			return false;
		}
		if ( wp_doing_ajax() && current_user_can( 'edit_posts' ) ) {
			$referer = (string) wp_get_referer();
			if ( '' !== $referer && 0 === strpos( $referer, admin_url() ) ) {
				return false;
			}
		}
		return true;
	};
	add_action(
		'pre_get_posts',
		static function ( $q ) use ( $hides ) {
			if ( ! $hides() || $q->is_singular() || $q->get( 'p' ) || $q->get( 'page_id' ) || $q->get( 'name' ) || $q->get( 'pagename' ) ) {
				return;
			}
			$ids = murtadd_superseded_ids();
			if ( ! $ids ) {
				return;
			}
			$in = murtadd_superseded_cut_includes( $q->get( 'post__in' ), $ids );
			if ( null !== $in ) {
				$q->set( 'post__in', $in );
				return;
			}
			$q->set( 'post__not_in', array_values( array_unique( array_merge( (array) $q->get( 'post__not_in' ), $ids ) ) ) );
		}
	);
	add_filter(
		'wp_sitemaps_posts_query_args',
		static function ( $args ) {
			$ids = murtadd_superseded_ids();
			if ( $ids ) {
				$args['post__not_in'] = array_merge( (array) ( $args['post__not_in'] ?? array() ), $ids );
			}
			return $args;
		}
	);
	add_filter(
		'wp_robots',
		static function ( $robots ) {
			return is_singular() && in_array( (int) get_queried_object_id(), murtadd_superseded_ids(), true ) ? wp_robots_no_robots( $robots ) : $robots;
		}
	);
	add_filter(
		'rank_math/frontend/robots',
		static function ( $robots ) {
			if ( is_singular() && in_array( (int) get_queried_object_id(), murtadd_superseded_ids(), true ) ) {
				$robots['index'] = 'noindex';
			}
			return $robots;
		}
	);
	add_filter(
		'rank_math/sitemap/posts_to_exclude',
		static function ( $ids ) {
			$ids = is_array( $ids ) ? $ids : preg_split( '/[\\s,]+/', (string) $ids, -1, PREG_SPLIT_NO_EMPTY );
			return array_values( array_unique( array_merge( array_filter( array_map( 'absint', $ids ) ), murtadd_superseded_ids() ) ) );
		}
	);
	add_filter(
		'rank_math/sitemap/entry',
		static function ( $url, $type, $object ) {
			return 'post' === $type && $object instanceof WP_Post && in_array( (int) $object->ID, murtadd_superseded_ids(), true ) ? false : $url;
		},
		10,
		3
	);
}
add_action( 'init', 'murtadd_superseded_fallback', 20 );
