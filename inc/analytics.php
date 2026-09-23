<?php
/**
 * Reading time.
 *
 * Adapted from Book-WP's analytics helper. The literary counters (stanzas,
 * lines) are not carried across; they answer a question this site never asks.
 *
 * The word count has to read postmeta, because a Doubt's substance is its
 * statement and short response, and its post_content is empty. Counting the
 * editor body would report every doubt as a nought-minute read.
 *
 * Why this matters here rather than as a vanity metric: the reader is
 * frightened and it is late. "3 min read" tells them the thing in front of
 * them is finite and survivable. The Doubt post type already caps the short
 * response; this surfaces that cap as a promise.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Words per minute. Deliberately conservative: this is careful reading, not skimming.
 */
define( 'MURTADD_WPM', 200 );

/**
 * The full readable text of a post, wherever it actually lives.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function murtadd_readable_text( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( ! $post_id ) {
		return '';
	}

	$parts = array( get_post_field( 'post_content', $post_id ) );

	foreach ( array(
		'_murtadd_doubt_statement',
		'_murtadd_short_response',
		'_murtadd_claim',
		'_murtadd_position_summary',
	) as $key ) {
		$value = get_post_meta( $post_id, $key, true );
		if ( is_string( $value ) && $value ) {
			$parts[] = $value;
		}
	}

	return wp_strip_all_tags( implode( ' ', $parts ) );
}

/**
 * Word count.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function murtadd_word_count( $post_id = 0 ) {
	$text = murtadd_readable_text( $post_id );
	if ( '' === trim( $text ) ) {
		return 0;
	}
	return str_word_count( $text );
}

/**
 * Reading time in whole minutes, never less than one.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function murtadd_reading_minutes( $post_id = 0 ) {
	$words = murtadd_word_count( $post_id );
	if ( ! $words ) {
		return 0;
	}
	return max( 1, (int) ceil( $words / MURTADD_WPM ) );
}

/**
 * Print the reading-time line.
 *
 * @param int $post_id Post ID.
 */
function murtadd_the_reading_time( $post_id = 0 ) {
	$minutes = murtadd_reading_minutes( $post_id );
	if ( ! $minutes ) {
		return;
	}

	printf(
		'<span class="murtadd-reading-time">%s</span>',
		esc_html(
			sprintf(
				/* translators: %d: number of minutes */
				_n( '%d min read', '%d min read', $minutes, 'murtadd' ),
				$minutes
			)
		)
	);
}
