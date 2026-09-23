<?php
/**
 * Taxonomy: Topic (murtadd_topic) — shared across all three CPTs.
 * Terms admin-managed via native term screens.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

function murtadd_register_taxonomy_topic() {
	register_taxonomy(
		'murtadd_topic',
		array( 'murtadd_doubt', 'murtadd_rebuttal', 'murtadd_fatwa' ),
		array(
			'labels'            => array(
				'name'          => __( 'Topics', 'murtadd' ),
				'singular_name' => __( 'Topic', 'murtadd' ),
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'topic' ),
		)
	);
}
add_action( 'init', 'murtadd_register_taxonomy_topic' );

/**
 * Seed default terms once.
 */
function murtadd_seed_topics() {
	if ( get_option( 'murtadd_topics_seeded' ) ) {
		return;
	}
	$terms = array( 'theodicy', 'hadith-authenticity', 'apostasy-law', 'moral-scriptural', 'identity', 'testimony-patterns' );
	foreach ( $terms as $slug ) {
		if ( ! term_exists( $slug, 'murtadd_topic' ) ) {
			wp_insert_term( ucwords( str_replace( '-', ' ', $slug ) ), 'murtadd_topic', array( 'slug' => $slug ) );
		}
	}
	update_option( 'murtadd_topics_seeded', 1 );
}
add_action( 'init', 'murtadd_seed_topics', 20 );
