<?php
/**
 * Structured data (JSON-LD).
 *
 * A person in the middle of a doubt does not arrive here through the homepage.
 * They arrive through a search engine, at two in the morning, having typed the
 * question into a box. Marking the Doubts up as questions with answers is
 * therefore not a search-ranking nicety here; it is the front door.
 *
 * Doubts     -> QAPage      (a question with an accepted answer)
 * Rebuttals  -> ClaimReview (a claim, assessed, with the sources cited)
 * Fatwa      -> Article
 * Front page -> WebSite + Organization
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print a JSON-LD block.
 *
 * @param array $data The graph node.
 */
function murtadd_print_jsonld( $data ) {
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}

/**
 * The publisher node, referenced by everything else.
 *
 * @return array
 */
function murtadd_organization_node() {
	$logo = get_site_icon_url( 512 );
	if ( ! $logo ) {
		$logo = get_stylesheet_directory_uri() . '/assets/icons/site-icon-512.png';
	}

	$node = array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
		'logo'  => array(
			'@type' => 'ImageObject',
			'url'   => $logo,
		),
	);

	// The saved profile URLs double as the publisher's sameAs set.
	$same_as = array_values( murtadd_social_profiles() );
	if ( $same_as ) {
		$node['sameAs'] = $same_as;
	}

	return $node;
}

/**
 * The author as a Person node, carrying the profile URLs as sameAs.
 *
 * The same saved URLs that render as footer icons are emitted here, so a
 * structured-data consumer can resolve the author to the same person on every
 * platform listed. Returns null when no author name is configured, so the
 * graph never carries an empty Person.
 *
 * @return array|null
 */
function murtadd_author_node() {
	$name = get_option( 'murtadd_author_name', '' );
	if ( ! $name ) {
		return null;
	}

	$node = array(
		'@type' => 'Person',
		'@id'   => home_url( '/#author' ),
		'name'  => $name,
	);

	$same_as = array_values( murtadd_social_profiles() );
	if ( $same_as ) {
		$node['sameAs'] = $same_as;
	}

	return $node;
}

/**
 * Emit the appropriate graph for the current view.
 */
function murtadd_structured_data() {
	if ( is_front_page() ) {
		murtadd_print_jsonld(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => array(
					array(
						'@type'       => 'WebSite',
						'@id'         => home_url( '/#website' ),
						'url'         => home_url( '/' ),
						'name'        => get_bloginfo( 'name' ),
						'description' => get_bloginfo( 'description' ),
						'publisher'   => array( '@id' => home_url( '/#organization' ) ),
						'potentialAction' => array(
							'@type'       => 'SearchAction',
							'target'      => array(
								'@type'       => 'EntryPoint',
								'urlTemplate' => home_url( '/?s={search_term_string}' ),
							),
							'query-input' => 'required name=search_term_string',
						),
					),
					murtadd_organization_node(),
				),
			)
		);
		return;
	}

	if ( ! is_singular( array( 'murtadd_doubt', 'murtadd_rebuttal', 'murtadd_fatwa' ) ) ) {
		return;
	}

	$post_id = get_the_ID();
	$type    = get_post_type();

	// A Doubt is a question with an answer. Mark it up as one.
	if ( 'murtadd_doubt' === $type ) {
		$question = get_post_meta( $post_id, '_murtadd_doubt_statement', true );
		$answer   = get_post_meta( $post_id, '_murtadd_short_response', true );

		if ( ! $question || ! $answer ) {
			return;
		}

		murtadd_print_jsonld(
			array(
				'@context'   => 'https://schema.org',
				'@type'      => 'QAPage',
				'@id'        => get_permalink() . '#qa',
				'mainEntity' => array(
					'@type'          => 'Question',
					'name'           => wp_strip_all_tags( $question ),
					'text'           => wp_strip_all_tags( $question ),
					'answerCount'    => 1,
					'datePublished'  => get_the_date( 'c' ),
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => wp_strip_all_tags( $answer ),
						'url'   => get_permalink(),
					),
				),
				'publisher'  => murtadd_organization_node(),
			)
		);
		return;
	}

	// A Rebuttal assesses a specific claim. ClaimReview is the honest shape:
	// it forces the claim to be stated, which is what the format requires anyway.
	if ( 'murtadd_rebuttal' === $type ) {
		$claim = get_post_meta( $post_id, '_murtadd_claim', true );

		$node = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'Article',
			'@id'           => get_permalink() . '#article',
			'headline'      => get_the_title(),
			'datePublished' => get_the_date( 'c' ),
			'dateModified'  => get_the_modified_date( 'c' ),
			'url'           => get_permalink(),
			'publisher'     => murtadd_organization_node(),
		'author'        => murtadd_author_node() ?: murtadd_organization_node(),
			'isPartOf'      => array( '@id' => home_url( '/#website' ) ),
		);

		if ( $claim ) {
			$node['about'] = array(
				'@type' => 'Claim',
				'text'  => wp_strip_all_tags( $claim ),
			);
		}

		$citations = array();
		foreach ( (array) get_post_meta( $post_id, '_murtadd_sources', true ) as $source ) {
			if ( ! empty( $source['citation_text'] ) ) {
				$citations[] = wp_strip_all_tags( $source['citation_text'] );
			}
		}
		if ( $citations ) {
			$node['citation'] = $citations;
		}

		murtadd_print_jsonld( $node );
		return;
	}

	// Fatwa entry.
	$scholar = get_post_meta( $post_id, '_murtadd_scholar_or_body', true );
	$node    = array(
		'@context'      => 'https://schema.org',
		'@type'         => 'Article',
		'@id'           => get_permalink() . '#article',
		'headline'      => get_the_title(),
		'datePublished' => get_the_date( 'c' ),
		'dateModified'  => get_the_modified_date( 'c' ),
		'url'           => get_permalink(),
		'publisher'     => murtadd_organization_node(),
		'author'        => murtadd_author_node() ?: murtadd_organization_node(),
	);
	if ( $scholar ) {
		$node['about'] = wp_strip_all_tags( $scholar );
	}
	$citation = get_post_meta( $post_id, '_murtadd_citation_link', true );
	if ( $citation ) {
		$node['citation'] = esc_url_raw( $citation );
	}

	murtadd_print_jsonld( $node );
}
add_action( 'wp_head', 'murtadd_structured_data' );
