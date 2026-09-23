<?php
/**
 * CPT: Fatwa (murtadd_fatwa).
 * Positions summarized in our own words; single pages on-site,
 * citation_link renders as a secondary outbound source button.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

function murtadd_register_cpt_fatwa() {
	register_post_type(
		'murtadd_fatwa',
		array(
			'labels'       => array(
				'name'          => __( 'Fatwa', 'murtadd' ),
				'singular_name' => __( 'Fatwa entry', 'murtadd' ),
				'add_new_item'  => __( 'Add New Fatwa Entry', 'murtadd' ),
				'edit_item'     => __( 'Edit Fatwa Entry', 'murtadd' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'fatwa' ),
			'menu_icon'    => 'dashicons-book-alt',
			'supports'     => array( 'title', 'revisions' ),
			'show_in_rest' => true,
			'taxonomies'   => array( 'murtadd_topic', 'murtadd_school' ),
		)
	);
}
add_action( 'init', 'murtadd_register_cpt_fatwa' );
