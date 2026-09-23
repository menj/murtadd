<?php
/**
 * CPT: Doubt (murtadd_doubt).
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

function murtadd_register_cpt_doubt() {
	register_post_type(
		'murtadd_doubt',
		array(
			'labels'       => array(
				'name'          => __( 'Doubts', 'murtadd' ),
				'singular_name' => __( 'Doubt', 'murtadd' ),
				'add_new_item'  => __( 'Add New Doubt', 'murtadd' ),
				'edit_item'     => __( 'Edit Doubt', 'murtadd' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'doubts' ),
			'menu_icon'    => 'dashicons-editor-help',
			'supports'     => array( 'title', 'editor', 'revisions' ),
			'show_in_rest' => true,
			'taxonomies'   => array( 'murtadd_topic', 'murtadd_doubt_category' ),
		)
	);
}
add_action( 'init', 'murtadd_register_cpt_doubt' );
