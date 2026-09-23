<?php
/**
 * Relation resolver.
 *
 * A WXR import cannot carry a post ID, because WordPress assigns new IDs on
 * the way in. The theme's "related rebuttal" and "related doubt" fields store
 * IDs, so imported content would land with those links empty.
 *
 * The import therefore writes the target's SLUG into a temporary meta key.
 * This runs once in the admin, turns each slug into the ID it now refers to,
 * and deletes the temporary key behind it. After that it costs one cheap query
 * and does nothing.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Map of temporary slug keys to the ID keys the templates actually read.
 *
 * @return array<string,array{0:string,1:string}> slug_key => [ id_key, post_type ]
 */
function murtadd_relation_map() {
	return array(
		'_murtadd_related_rebuttal_slug' => array( '_murtadd_related_rebuttal', 'murtadd_rebuttal' ),
		'_murtadd_related_doubt_slug'    => array( '_murtadd_related_doubt', 'murtadd_doubt' ),
		'_murtadd_related_fatwa_slug'    => array( '_murtadd_related_fatwa', 'murtadd_fatwa' ),
	);
}

/**
 * Resolve any pending slug relations into post IDs.
 *
 * @return int Number of relations resolved.
 */
function murtadd_resolve_relations() {
	$resolved = 0;

	foreach ( murtadd_relation_map() as $slug_key => $conf ) {
		list( $id_key, $post_type ) = $conf;

		$pending = get_posts(
			array(
				'post_type'   => array( 'murtadd_doubt', 'murtadd_rebuttal', 'murtadd_fatwa' ),
				'post_status' => 'any',
				'numberposts' => 200,
				'meta_key'    => $slug_key,
				'fields'      => 'ids',
			)
		);

		foreach ( $pending as $post_id ) {
			$slug = get_post_meta( $post_id, $slug_key, true );
			$target = get_page_by_path( $slug, OBJECT, $post_type );

			if ( $target ) {
				update_post_meta( $post_id, $id_key, (int) $target->ID );
				++$resolved;
			}

			// Remove the temporary key either way: an unresolvable slug should
			// not be retried on every admin request forever.
			delete_post_meta( $post_id, $slug_key );
		}
	}

	return $resolved;
}
add_action( 'admin_init', 'murtadd_resolve_relations' );
