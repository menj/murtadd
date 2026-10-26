<?php
/**
 * CPT: Guide (murtadd_guide), at /guides/{slug}/.
 *
 * Long-form guides (1,500 words and more) for a reader working through a
 * question at length. They are not blog posts: a post on this site is dated
 * news under the Blog index, while a guide is evergreen reference that the
 * sister sites link to at a fixed address (/guides/islamic-dilemma/). A post
 * type gives that address without touching the site-wide permalink structure,
 * and keeps guides out of the dated blog. Single guides render through
 * single.php and the index through archive.php.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the post type.
 *
 * @return void
 */
function murtadd_register_cpt_guide() {
	register_post_type(
		'murtadd_guide',
		array(
			'labels'       => array(
				'name'          => __( 'Guides', 'murtadd' ),
				'singular_name' => __( 'Guide', 'murtadd' ),
				'add_new_item'  => __( 'Add New Guide', 'murtadd' ),
				'edit_item'     => __( 'Edit Guide', 'murtadd' ),
			),
			'public'       => true,
			'has_archive'  => 'guides',
			'rewrite'      => array(
				'slug'       => 'guides',
				'with_front' => false,
			),
			'menu_icon'    => 'dashicons-welcome-learn-more',
			'supports'     => array( 'title', 'editor', 'excerpt', 'revisions' ),
			'show_in_rest' => true,
			'taxonomies'   => array( 'murtadd_topic' ),
		)
	);
}
add_action( 'init', 'murtadd_register_cpt_guide' );

/**
 * Ask the existing rewrite flusher for one flush when the guide rules first ship.
 *
 * murtadd_maybe_flush_rewrites() (init, 99) flushes whenever
 * murtadd_rewrite_version differs from MURTADD_VERSION. A site already
 * stamped 1.43.0 before this file existed would never flush, so the stamp is
 * cleared once, here, and the flusher writes the complete rule set itself.
 *
 * @return void
 */
function murtadd_guide_rewrite_once() {
	if ( '1' === (string) get_option( 'murtadd_guide_rules' ) ) {
		return;
	}
	delete_option( 'murtadd_rewrite_version' );
	update_option( 'murtadd_guide_rules', '1' );
}
add_action( 'init', 'murtadd_guide_rewrite_once', 98 );
