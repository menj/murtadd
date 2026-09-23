<?php
/**
 * Feeds.
 *
 * The main feed carried 'post' only, so nothing this site is actually for —
 * the doubts and rebuttals — reached a subscriber. Two problems to solve:
 * the post types were absent from the feed query, and a Doubt has an empty
 * post_content (its substance is postmeta), so simply adding it would have
 * produced empty feed items.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Include the content types in the main feed.
 *
 * Only when no explicit post_type was requested: per-type feeds like
 * /doubts/feed/ keep their own scope.
 *
 * @param array $query_vars Request vars.
 * @return array
 */
function murtadd_feed_post_types( $query_vars ) {
	if ( isset( $query_vars['feed'] ) && ! isset( $query_vars['post_type'] ) ) {
		$query_vars['post_type'] = array( 'post', 'murtadd_doubt', 'murtadd_rebuttal', 'murtadd_fatwa' );
	}
	return $query_vars;
}
add_filter( 'request', 'murtadd_feed_post_types' );

/**
 * Feed body for the meta-driven types.
 *
 * @param string $content Item content.
 * @return string
 */
function murtadd_feed_content( $content ) {
	if ( ! is_feed() ) {
		return $content;
	}

	$post_id = get_the_ID();

	if ( 'murtadd_doubt' === get_post_type() ) {
		$statement = get_post_meta( $post_id, '_murtadd_doubt_statement', true );
		$response  = get_post_meta( $post_id, '_murtadd_short_response', true );

		$out = '';
		if ( $statement ) {
			$out .= '<blockquote>' . esc_html( $statement ) . '</blockquote>';
		}
		$out .= wp_kses_post( $response );
		return $out ? $out : $content;
	}

	if ( 'murtadd_rebuttal' === get_post_type() ) {
		$claim = get_post_meta( $post_id, '_murtadd_claim', true );
		$out   = '';
		if ( $claim ) {
			/* translators: %s: the claim under review */
			$out .= '<p><strong>' . sprintf( esc_html__( 'The claim: %s', 'murtadd' ), esc_html( $claim ) ) . '</strong></p>';
		}
		return $out . $content;
	}

	if ( 'murtadd_fatwa' === get_post_type() ) {
		$scholar = get_post_meta( $post_id, '_murtadd_scholar_or_body', true );
		$summary = get_post_meta( $post_id, '_murtadd_position_summary', true );

		$out = '';
		if ( $scholar ) {
			$out .= '<p><strong>' . esc_html( $scholar ) . '</strong></p>';
		}
		if ( $summary ) {
			$out .= '<p>' . esc_html( $summary ) . '</p>';
		}
		return $out ? $out : $content;
	}

	return $content;
}
add_filter( 'the_content_feed', 'murtadd_feed_content' );
add_filter( 'the_excerpt_rss', 'murtadd_feed_content' );
