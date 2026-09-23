<?php
/**
 * Generated social cards.
 *
 * Every doubt, rebuttal, and fatwa entry gets its own share image, generated
 * from content already in the database. No stock photography.
 *
 * The reasoning is not decorative. The site this one answers put a photograph
 * of a person at the moment of leaving on its front page, and that image did
 * the emotional work. The honest equivalent here is not a face: it is the
 * doubt itself, set as an object, in the reader's own words. When someone
 * sends a page to a friend who is struggling, the doubt arrives in the
 * message thread rather than a bare site icon.
 *
 * The card is an SVG, rendered on the fly and cached. SVG rather than a
 * raster: no GD or Imagick dependency, no font-loading at runtime, and it
 * stays crisp. Platforms that will not unfurl SVG fall back to the site icon,
 * which is the behaviour that existed before this file.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The line a card should carry: the reader's own sentence where there is one.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function murtadd_card_line( $post_id ) {
	$type = get_post_type( $post_id );

	if ( 'murtadd_doubt' === $type ) {
		$line = get_post_meta( $post_id, '_murtadd_doubt_statement', true );
	} elseif ( 'murtadd_rebuttal' === $type ) {
		$line = get_post_meta( $post_id, '_murtadd_claim', true );
	} else {
		$line = '';
	}

	if ( ! $line ) {
		$line = get_the_title( $post_id );
	}

	return wp_strip_all_tags( $line );
}

/**
 * The eyebrow above the line.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function murtadd_card_eyebrow( $post_id ) {
	if ( 'murtadd_doubt' === get_post_type( $post_id ) ) {
		$terms = get_the_terms( $post_id, 'murtadd_doubt_category' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			return $terms[0]->name;
		}
	}
	return murtadd_type_label( get_post_type( $post_id ) );
}

/**
 * Wrap a line to a character budget, for SVG tspans.
 *
 * @param string $text     Text.
 * @param int    $per_line Approximate characters per line.
 * @param int    $max      Maximum lines.
 * @return string[]
 */
function murtadd_card_wrap( $text, $per_line = 34, $max = 4 ) {
	$words = preg_split( '/\s+/', trim( $text ) );
	$lines = array();
	$cur   = '';

	foreach ( $words as $word ) {
		$try = trim( $cur . ' ' . $word );
		if ( mb_strlen( $try ) <= $per_line ) {
			$cur = $try;
			continue;
		}
		if ( $cur ) {
			$lines[] = $cur;
		}
		$cur = $word;
		if ( count( $lines ) >= $max ) {
			break;
		}
	}

	if ( $cur && count( $lines ) < $max ) {
		$lines[] = $cur;
	}

	// Truncate rather than overflow the plate.
	if ( count( $lines ) === $max ) {
		$last = array_pop( $lines );
		if ( mb_strlen( $last ) > $per_line - 1 ) {
			$last = mb_substr( $last, 0, $per_line - 1 );
		}
		$lines[] = rtrim( $last, ' ,;:' ) . '…';
	}

	return $lines;
}

/**
 * Build the SVG for a post's card.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function murtadd_card_svg( $post_id ) {
	$colours = get_option( 'murtadd_colours', array() );
	$accent  = isset( $colours['accent'] ) ? $colours['accent'] : '#0f6e56';

	$line    = murtadd_card_line( $post_id );
	$eyebrow = murtadd_card_eyebrow( $post_id );
	$minutes = murtadd_reading_minutes( $post_id );

	$lines = murtadd_card_wrap( '“' . $line . '”' );
	$y     = 250 - ( ( count( $lines ) - 1 ) * 34 );

	$tspans = '';
	foreach ( $lines as $i => $l ) {
		$tspans .= sprintf(
			'<tspan x="80" y="%d">%s</tspan>',
			$y + ( $i * 68 ),
			esc_html( $l )
		);
	}

	$meta = $minutes
		? sprintf(
			/* translators: %d: minutes */
			_n( '%d min read · sourced', '%d min read · sourced', $minutes, 'murtadd' ),
			$minutes
		)
		: __( 'Sourced', 'murtadd' );

	return sprintf(
		'<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630" role="img" aria-label="%1$s">
	<rect width="1200" height="630" fill="#fcf8ee"/>
	<rect width="26" height="630" fill="%2$s"/>
	<text x="80" y="80" font-family="Public Sans, system-ui, sans-serif" font-size="17" font-weight="700" letter-spacing="2" fill="#5a5a5a">%3$s</text>
	<rect x="80" y="100" width="60" height="3" fill="#c0392b"/>
	<text font-family="Source Serif 4, Georgia, serif" font-size="52" font-weight="700" fill="#1a1a1a">%4$s</text>
	<text x="80" y="530" font-family="Public Sans, system-ui, sans-serif" font-size="16" font-weight="700" letter-spacing="1.5" fill="%2$s">%5$s</text>
	<text x="80" y="558" font-family="Public Sans, system-ui, sans-serif" font-size="18" fill="#5a5a5a">%6$s</text>
	<text x="1120" y="558" text-anchor="end" font-family="Source Serif 4, Georgia, serif" font-size="30" font-weight="700" fill="#1a1a1a">murtadd</text>
	<rect x="972" y="537" width="150" height="3" fill="#c0392b"/>
</svg>',
		esc_attr( $line ),
		esc_attr( $accent ),
		esc_html( mb_strtoupper( $eyebrow ) ),
		$tspans,
		esc_html__( 'ANSWERED', 'murtadd' ),
		esc_html( $meta )
	);
}

/**
 * Serve a card at /murtadd-card/{id}.svg.
 */
function murtadd_card_rewrite() {
	add_rewrite_rule( '^murtadd-card/([0-9]+)\.svg$', 'index.php?murtadd_card=$matches[1]', 'top' );
}
add_action( 'init', 'murtadd_card_rewrite', 11 );

/**
 * Register the query var.
 *
 * @param array $vars Query vars.
 * @return array
 */
function murtadd_card_query_var( $vars ) {
	$vars[] = 'murtadd_card';
	return $vars;
}
add_filter( 'query_vars', 'murtadd_card_query_var' );

/**
 * Render the card.
 */
function murtadd_card_render() {
	$post_id = (int) get_query_var( 'murtadd_card' );
	if ( ! $post_id ) {
		return;
	}

	$post = get_post( $post_id );
	if ( ! $post || 'publish' !== $post->post_status || ! in_array( $post->post_type, array( 'murtadd_doubt', 'murtadd_rebuttal', 'murtadd_fatwa' ), true ) ) {
		status_header( 404 );
		exit;
	}

	header( 'Content-Type: image/svg+xml; charset=utf-8' );
	header( 'Cache-Control: public, max-age=86400' );
	echo murtadd_card_svg( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts.
	exit;
}
add_action( 'template_redirect', 'murtadd_card_render', 1 );

/**
 * The card URL for a post.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function murtadd_card_url( $post_id ) {
	return home_url( '/murtadd-card/' . (int) $post_id . '.svg' );
}

/**
 * Use the generated card as the share image.
 *
 * A featured image, where one has been set deliberately, outranks the
 * generated card: a folio, a court document, or the claim as it actually
 * circulates is worth more than a typographic plate.
 *
 * @param string $image Current image URL.
 * @return string
 */
function murtadd_card_as_share_image( $image ) {
	if ( ! is_singular( array( 'murtadd_doubt', 'murtadd_rebuttal', 'murtadd_fatwa' ) ) ) {
		return $image;
	}

	if ( has_post_thumbnail() ) {
		$thumb = get_the_post_thumbnail_url( get_the_ID(), 'full' );
		if ( $thumb ) {
			return $thumb;
		}
	}

	return murtadd_card_url( get_the_ID() );
}
add_filter( 'murtadd_share_image', 'murtadd_card_as_share_image' );
