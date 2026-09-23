<?php
/**
 * CPT: Rebuttal (murtadd_rebuttal).
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

function murtadd_register_cpt_rebuttal() {
	register_post_type(
		'murtadd_rebuttal',
		array(
			'labels'       => array(
				'name'          => __( 'Rebuttals', 'murtadd' ),
				'singular_name' => __( 'Rebuttal', 'murtadd' ),
				'add_new_item'  => __( 'Add New Rebuttal', 'murtadd' ),
				'edit_item'     => __( 'Edit Rebuttal', 'murtadd' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'rebuttals' ),
			'menu_icon'    => 'dashicons-shield',
			'supports'     => array( 'title', 'editor', 'revisions' ),
			'show_in_rest' => true,
			'taxonomies'   => array( 'murtadd_topic' ),
		)
	);
}
add_action( 'init', 'murtadd_register_cpt_rebuttal' );
