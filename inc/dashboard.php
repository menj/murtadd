<?php
/**
 * Dashboard widget: content gaps.
 *
 * Adapted rather than copied from Book-WP, whose widget lists literary forms.
 * The equivalent that earns its place here is a gap report, because every
 * defect it looks for is one that actually shipped on this site and was
 * caught by a person rather than by the software:
 *
 *   - a doubt pointing at a rebuttal that did not exist;
 *   - a doubt category with nothing in it, so one of the four doors on the
 *     homepage read "0 responses";
 *   - a rebuttal with no sources filed, on a site whose entire promise is
 *     that the reader can check the sources.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the widget.
 */
function murtadd_dashboard_widget() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	wp_add_dashboard_widget(
		'murtadd_gaps',
		__( 'Murtadd — content gaps', 'murtadd' ),
		'murtadd_dashboard_widget_render'
	);
}
add_action( 'wp_dashboard_setup', 'murtadd_dashboard_widget' );

/**
 * Find every gap worth reporting.
 *
 * @return array<int,array{label:string,items:string[]}>
 */
function murtadd_content_gaps() {
	$gaps = array();

	// 1. Doubt categories with nothing in them. Each is a dead door on the homepage.
	$empty_cats = array();
	foreach ( get_terms(
		array(
			'taxonomy'   => 'murtadd_doubt_category',
			'hide_empty' => false,
		)
	) as $term ) {
		if ( ! is_wp_error( $term ) && 0 === (int) $term->count ) {
			$empty_cats[] = $term->name;
		}
	}
	if ( $empty_cats ) {
		$gaps[] = array(
			'label' => __( 'Doubt categories with no doubts. Each shows “0 responses” on the homepage — a door that opens onto nothing.', 'murtadd' ),
			'items' => $empty_cats,
		);
	}

	// 2. Doubts with no rebuttal attached: the "go deeper" link renders empty.
	$orphans = get_posts(
		array(
			'post_type'      => 'murtadd_doubt',
			'posts_per_page' => 20,
			'post_status'    => 'publish',
			'no_found_rows'  => true,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- admin dashboard, capped.
				'relation' => 'OR',
				array(
					'key'     => '_murtadd_related_rebuttal',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'   => '_murtadd_related_rebuttal',
					'value' => '',
				),
			),
		)
	);
	if ( $orphans ) {
		$gaps[] = array(
			'label' => __( 'Doubts with no rebuttal attached. The reader reaches the end of the short answer and has nowhere to go.', 'murtadd' ),
			'items' => wp_list_pluck( $orphans, 'post_title' ),
		);
	}

	// 3. Rebuttals with no sources. The one thing this site promises.
	$unsourced = array();
	foreach ( get_posts(
		array(
			'post_type'      => 'murtadd_rebuttal',
			'posts_per_page' => 50,
			'post_status'    => 'publish',
			'no_found_rows'  => true,
		)
	) as $rebuttal ) {
		$sources = get_post_meta( $rebuttal->ID, '_murtadd_sources', true );
		if ( empty( $sources ) || ! is_array( $sources ) ) {
			$unsourced[] = $rebuttal->post_title;
		}
	}
	if ( $unsourced ) {
		$gaps[] = array(
			'label' => __( 'Rebuttals with no sources filed. The site promises the reader can check for themselves; these cannot be checked.', 'murtadd' ),
			'items' => $unsourced,
		);
	}

	return $gaps;
}

/**
 * Render the widget.
 */
function murtadd_dashboard_widget_render() {
	$gaps = murtadd_content_gaps();

	if ( ! $gaps ) {
		echo '<p style="color:#0f6e56;font-weight:600;">' . esc_html__( 'No gaps. Every door is populated, every doubt routes onward, every rebuttal is sourced.', 'murtadd' ) . '</p>';
		return;
	}

	foreach ( $gaps as $gap ) {
		echo '<p style="margin:14px 0 6px;"><strong>' . esc_html( $gap['label'] ) . '</strong></p>';
		echo '<ul style="margin:0 0 4px 18px;list-style:disc;">';
		foreach ( $gap['items'] as $item ) {
			echo '<li>' . esc_html( $item ) . '</li>';
		}
		echo '</ul>';
	}
}
