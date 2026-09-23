<?php
/**
 * Taxonomy: School (murtadd_school) — Fatwa only.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

function murtadd_register_taxonomy_school() {
	register_taxonomy(
		'murtadd_school',
		array( 'murtadd_fatwa' ),
		array(
			'labels'            => array(
				'name'          => __( 'Schools', 'murtadd' ),
				'singular_name' => __( 'School', 'murtadd' ),
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'school' ),
		)
	);
}
add_action( 'init', 'murtadd_register_taxonomy_school' );

function murtadd_seed_schools() {
	if ( get_option( 'murtadd_schools_seeded' ) ) {
		return;
	}
	$terms = array(
		'hanafi'       => __( 'Hanafi', 'murtadd' ),
		'maliki'       => __( 'Maliki', 'murtadd' ),
		'shafii'       => __( 'Shafi’i', 'murtadd' ),
		'hanbali'      => __( 'Hanbali', 'murtadd' ),
		'contemporary' => __( 'Contemporary', 'murtadd' ),
	);
	foreach ( $terms as $slug => $name ) {
		if ( ! term_exists( $slug, 'murtadd_school' ) ) {
			wp_insert_term( $name, 'murtadd_school', array( 'slug' => $slug ) );
		}
	}
	update_option( 'murtadd_schools_seeded', 1 );
}
add_action( 'init', 'murtadd_seed_schools', 20 );
