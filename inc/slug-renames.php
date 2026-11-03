<?php
/**
 * Renamed addresses: a one-time rename of live posts, and permanent 301s.
 *
 * An address names its subject, never a verse number, a count or a year
 * (owner's rule, 3 November 2026). The seeder never touches a post it has
 * already created, so a slug changed in the seed data would never reach a
 * live site; worse, the seeder would then create a second post at the new
 * slug. This file closes both gaps:
 *
 *   1. murtadd_rename_seeded_slugs() runs once per change to the map, before
 *      the seeder, and renames the existing post in place. Its ID, content,
 *      edits, comments and relations carry over. The new slug is recorded in
 *      murtadd_seeded_slugs, so a post an editor deleted is not resurrected
 *      under its new name either.
 *   2. murtadd_redirect_renamed_slugs() answers the old address with a 301 to
 *      the post's current permalink. It reads the map in this file, never
 *      WordPress's _wp_old_slug note, so it survives any later cleanup. It
 *      runs only on a 404, so a post that later takes an old slug keeps it.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Old slug => new slug, per post type. Entries stay here for good.
 *
 * @return array<string,array<string,string>>
 */
function murtadd_slug_renames() {
	$map = array(
		'murtadd_doubt'    => array(
			'quran-4-34-wife-beating' => 'quran-wife-beating-verse',
		),
		'murtadd_rebuttal' => array(
			'quran-4-34-in-context' => 'quran-wife-beating-verse-in-context',
		),
	);

	/**
	 * Filters the rename map.
	 *
	 * @param array $map Post type => (old slug => new slug).
	 */
	return apply_filters( 'murtadd_slug_renames', $map );
}

/**
 * The archive base each renamed post type sits under.
 *
 * @return array<string,string> Post type => URL base.
 */
function murtadd_slug_rename_bases() {
	return array(
		'murtadd_doubt'    => 'doubts',
		'murtadd_rebuttal' => 'rebuttals',
	);
}

/**
 * Which renamed post a request path names.
 *
 * @param string $path Request path, with or without slashes.
 * @return array{type:string,old:string,new:string}|null
 */
function murtadd_renamed_slug_for_path( $path ) {
	$parts = explode( '/', strtolower( trim( (string) $path, '/' ) ) );
	if ( 2 !== count( $parts ) ) {
		return null;
	}

	$type = array_search( $parts[0], murtadd_slug_rename_bases(), true );
	if ( false === $type ) {
		return null;
	}

	$map = murtadd_slug_renames();
	if ( empty( $map[ $type ][ $parts[1] ] ) ) {
		return null;
	}

	return array(
		'type' => $type,
		'old'  => $parts[1],
		'new'  => $map[ $type ][ $parts[1] ],
	);
}

/**
 * Rename live posts to their new slugs. Runs once per change to the map.
 *
 * @return int Number of posts renamed on this run.
 */
function murtadd_rename_seeded_slugs() {
	$map  = murtadd_slug_renames();
	$hash = md5( (string) wp_json_encode( $map ) );
	if ( get_option( 'murtadd_slug_renames_done' ) === $hash ) {
		return 0;
	}

	$renamed = 0;
	$pending = false;

	foreach ( $map as $type => $pairs ) {
		if ( function_exists( 'post_type_exists' ) && ! post_type_exists( $type ) ) {
			return 0; // Too early: try again on a later request.
		}

		foreach ( $pairs as $old => $new ) {
			$was_seeded = murtadd_slug_was_seeded( $type, $old );
			$current    = get_page_by_path( $new, OBJECT, $type );
			$post       = $current ? null : get_page_by_path( $old, OBJECT, $type );

			if ( $post ) {
				wp_update_post(
					array(
						'ID'        => $post->ID,
						'post_name' => $new,
					),
					true
				);
				// Success means the post now sits at exactly the new slug.
				// WordPress may have failed, or given it a -2 suffix.
				$moved = get_page_by_path( $new, OBJECT, $type );
				if ( ! $moved || (int) $moved->ID !== (int) $post->ID ) {
					// The post keeps its old address, which still serves it, and
					// the new one sends a 302 there (see the redirect below). The
					// new slug is left unrecorded and the run is retried.
					$pending = true;
					continue;
				}
				++$renamed;
				$was_seeded = true;
			}

			// The new slug inherits the old one's record, so the seeder adopts
			// the renamed post, or leaves a deleted one deleted.
			if ( $was_seeded || $current ) {
				murtadd_mark_slug_seeded( $type, $new );
			}
		}
	}

	if ( ! $pending ) {
		update_option( 'murtadd_slug_renames_done', $hash );
	}
	return $renamed;
}

/**
 * Whether a slug is the target of a rename that has not happened yet: a post
 * still sits at one of its old slugs. The seeder skips such a slug, so it
 * never installs a second copy beside the post waiting to be renamed.
 *
 * @param string $type Post type.
 * @param string $slug New slug.
 * @return bool
 */
function murtadd_slug_rename_pending( $type, $slug ) {
	$map = murtadd_slug_renames();
	foreach ( array_keys( isset( $map[ $type ] ) ? $map[ $type ] : array(), $slug, true ) as $old ) {
		if ( get_page_by_path( $old, OBJECT, $type ) && ! get_page_by_path( $slug, OBJECT, $type ) ) {
			return true;
		}
	}
	return false;
}

/**
 * The post still waiting at an old slug for a request to a new address.
 *
 * @param string $path Request path, with or without slashes.
 * @return array{type:string,old:string}|null
 */
function murtadd_pending_rename_for_path( $path ) {
	$parts = explode( '/', strtolower( trim( (string) $path, '/' ) ) );
	if ( 2 !== count( $parts ) ) {
		return null;
	}
	$type = array_search( $parts[0], murtadd_slug_rename_bases(), true );
	if ( false === $type ) {
		return null;
	}
	$map = murtadd_slug_renames();
	foreach ( array_keys( isset( $map[ $type ] ) ? $map[ $type ] : array(), $parts[1], true ) as $old ) {
		return array(
			'type' => $type,
			'old'  => (string) $old,
		);
	}
	return null;
}
add_action( 'init', 'murtadd_rename_seeded_slugs', 80 );

/**
 * Send the 301 for a renamed address.
 *
 * @return void
 */
function murtadd_redirect_renamed_slugs() {
	if ( ! is_404() ) {
		return;
	}

	$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	if ( ! $uri ) {
		return;
	}

	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( '' !== trim( $home, '/' ) && 0 === strpos( $path, $home ) ) {
		$path = substr( $path, strlen( $home ) );
	}

	$status = 301;
	$hit    = murtadd_renamed_slug_for_path( $path );
	if ( $hit ) {
		$post = get_page_by_path( $hit['new'], OBJECT, $hit['type'] );
	} else {
		// A new address whose post is still at its old slug, because the
		// rename failed: send a temporary redirect, since the rename is retried.
		$hit = murtadd_pending_rename_for_path( $path );
		if ( ! $hit ) {
			return;
		}
		$post   = get_page_by_path( $hit['old'], OBJECT, $hit['type'] );
		$status = 302;
	}

	$url = ( $post && 'publish' === $post->post_status ) ? (string) get_permalink( $post ) : '';
	if ( ! $url ) {
		return; // Target missing: let the 404 stand rather than chain to another.
	}

	$query = (string) wp_parse_url( $uri, PHP_URL_QUERY );
	if ( '' !== $query ) {
		$url .= ( false === strpos( $url, '?' ) ? '?' : '&' ) . $query;
	}

	wp_safe_redirect( $url, $status );
	exit;
}
add_action( 'template_redirect', 'murtadd_redirect_renamed_slugs' );
