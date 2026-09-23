<?php
/**
 * Meta description, Open Graph, Twitter cards, and robots directives.
 *
 * The JSON-LD in inc/schema.php speaks to structured-data parsers; nothing
 * was speaking to snippet builders or link unfurlers. Every share to a chat
 * app rendered bare, and search snippets were whatever the crawler scraped.
 *
 * The description sources are the fields the content model already forces:
 * a Doubt's statement, a Rebuttal's claim, a Fatwa entry's position summary.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The description for the current view, or empty string.
 *
 * @return string
 */
function murtadd_meta_description() {
	if ( is_front_page() ) {
		$desc = get_bloginfo( 'description' );
		return $desc ? $desc : __( 'Honest, sourced answers to the questions that shake faith — stated fairly, answered without dismissal.', 'murtadd' );
	}

	if ( is_singular( 'murtadd_doubt' ) ) {
		return (string) get_post_meta( get_the_ID(), '_murtadd_doubt_statement', true );
	}

	if ( is_singular( 'murtadd_rebuttal' ) ) {
		$claim = (string) get_post_meta( get_the_ID(), '_murtadd_claim', true );
		if ( $claim ) {
			/* translators: %s: the claim under review */
			return sprintf( __( 'The claim: "%s" — stated fairly, answered with sources.', 'murtadd' ), $claim );
		}
		return '';
	}

	if ( is_singular( 'murtadd_fatwa' ) ) {
		return (string) get_post_meta( get_the_ID(), '_murtadd_position_summary', true );
	}

	if ( is_singular() ) {
		$post = get_post();
		if ( $post && $post->post_excerpt ) {
			return $post->post_excerpt;
		}
		if ( $post ) {
			return wp_trim_words( wp_strip_all_tags( $post->post_content ), 32 );
		}
		return '';
	}

	if ( is_post_type_archive( 'murtadd_doubt' ) || is_tax( 'murtadd_doubt_category' ) ) {
		return __( 'Doubts, taken seriously: short, sourced answers to the questions that shake faith.', 'murtadd' );
	}
	if ( is_post_type_archive( 'murtadd_rebuttal' ) ) {
		return __( 'Circulated claims about Islam, stated fairly and answered with sources.', 'murtadd' );
	}
	if ( is_post_type_archive( 'murtadd_fatwa' ) || is_tax( 'murtadd_school' ) ) {
		return __( 'Scholarly positions summarised in our own words, each linked to its primary source. A reference, not a verdict.', 'murtadd' );
	}
	if ( is_tax( 'murtadd_topic' ) ) {
		$term = get_queried_object();
		if ( $term && ! is_wp_error( $term ) ) {
			/* translators: %s: topic name */
			return sprintf( __( 'Doubts, rebuttals, and fatwa entries on %s.', 'murtadd' ), $term->name );
		}
	}
	if ( is_home() ) {
		return __( 'Essays and notes from murtadd.org.', 'murtadd' );
	}

	return '';
}

/**
 * Print description, Open Graph, and Twitter tags.
 *
 * Skipped on search results and 404s, which are noindexed below and have
 * nothing worth unfurling.
 */
function murtadd_meta_tags() {
	if ( is_search() || is_404() ) {
		return;
	}

	$desc = murtadd_meta_description();
	$desc = $desc ? wp_strip_all_tags( $desc ) : '';
	if ( function_exists( 'mb_substr' ) && mb_strlen( $desc ) > 300 ) {
		$desc = mb_substr( $desc, 0, 297 ) . '…';
	}

	$title = is_singular() ? get_the_title() : wp_get_document_title();
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( array(), $GLOBALS['wp']->request ? '/' . $GLOBALS['wp']->request . '/' : '/' ) );

	$image = get_site_icon_url( 512 );
	if ( ! $image ) {
		$image = get_stylesheet_directory_uri() . '/assets/icons/site-icon-512.png';
	}

	/**
	 * The share image. inc/social-card.php substitutes a generated card on the
	 * content types, and a featured image outranks the card where one is set.
	 *
	 * @param string $image Image URL.
	 */
	$image = apply_filters( 'murtadd_share_image', $image );

	if ( $desc ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	}

	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:type" content="%s">' . "\n", is_singular() ? 'article' : 'website' );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	if ( $desc ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	}
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );

	echo '<meta name="twitter:card" content="summary">' . "\n";
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
	if ( $desc ) {
		printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $desc ) );
	}
	printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image ) );
}
add_action( 'wp_head', 'murtadd_meta_tags', 5 );

/**
 * Robots: search results and 404s are thin, infinite-variant pages.
 *
 * @param array $robots Directives.
 * @return array
 */
function murtadd_robots( $robots ) {
	if ( is_search() || is_404() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'murtadd_robots' );
