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
 * The view's main node: WebSite and Organization on the front page, a QAPage
 * for a doubt, an Article for a rebuttal or a fatwa entry. Null elsewhere.
 *
 * @return array|null A node or a graph, with its @context.
 */
function murtadd_structured_data_primary_node() {
	if ( is_front_page() ) {
		return array(
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
		);
	}

	if ( ! is_singular( array( 'murtadd_doubt', 'murtadd_rebuttal', 'murtadd_fatwa' ) ) ) {
		return null;
	}

	$post_id = get_the_ID();
	$type    = get_post_type();

	// A Doubt is a question with an answer. Mark it up as one.
	if ( 'murtadd_doubt' === $type ) {
		$question = get_post_meta( $post_id, '_murtadd_doubt_statement', true );
		$answer   = get_post_meta( $post_id, '_murtadd_short_response', true );

		if ( ! $question || ! $answer ) {
			return null;
		}

		return array(
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
		);
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

		return $node;
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

	return $node;
}

/**
 * The BreadcrumbList for the visible trail, or null where none is shown.
 *
 * @return array|null
 */
function murtadd_breadcrumb_node() {
	if ( ! function_exists( 'murtadd_breadcrumbs_enabled' ) || ! murtadd_breadcrumbs_enabled() ) {
		return null;
	}
	$trail = murtadd_breadcrumb_trail();
	if ( count( $trail ) < 2 ) {
		return null;
	}
	$here  = murtadd_current_url();
	$items = array();
	foreach ( array_values( $trail ) as $i => $crumb ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => murtadd_schema_text( $crumb[0] ),
			'item'     => '' !== $crumb[1] ? $crumb[1] : $here,
		);
	}
	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => $here . '#breadcrumb',
		'itemListElement' => $items,
	);
}

/**
 * Markup as plain text for a JSON-LD string: tags gone, entities decoded
 * (titles arrive texturised, term names escaped), whitespace collapsed.
 *
 * @param string $html Markup or escaped text.
 * @return string
 */
function murtadd_schema_text( $html ) {
	$text = html_entity_decode( wp_strip_all_tags( (string) $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	// Malformed UTF-8 makes the /u pattern return null: keep the text as is.
	return trim( preg_replace( '/[\s\x{00A0}]+/u', ' ', $text ) ?? $text );
}

/**
 * Question and answer pairs from content written as questions under h2
 * headings: each heading is a question, and the text up to the next heading
 * is its answer.
 *
 * @param string $html Stored content.
 * @return array<int,array> Question nodes.
 */
function murtadd_faq_pairs_from_html( $html ) {
	$parts = preg_split( '#(<h2\b[^>]*>.*?</h2>)#is', (string) $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	if ( ! is_array( $parts ) ) {
		return array();
	}
	$pairs = array();
	$count = count( $parts );
	for ( $i = 1; $i < $count; $i += 2 ) {
		$question = murtadd_schema_text( $parts[ $i ] );
		$answer   = murtadd_schema_text( preg_replace( '#<!--.*?-->#s', '', $parts[ $i + 1 ] ?? '' ) );
		if ( '' === $question || '' === $answer ) {
			continue;
		}
		$pairs[] = array(
			'@type'          => 'Question',
			'name'           => $question,
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $answer,
			),
		);
	}
	return $pairs;
}

/**
 * The FAQPage for a view written as questions and answers, or null.
 *
 * The FAQ page: its h2 questions and the answers under them. A doubt
 * category archive: each listed doubt's statement, answered by the same
 * excerpt of its short response the archive shows, so the data says nothing
 * the page does not. The Emotional and Identity categories get none: their
 * doubts are written care-first, a grief is not a question awaiting an
 * answer box (docs/ssot.md), and they stay out of every answer feature the
 * theme can keep them out of.
 *
 * @return array|null
 */
function murtadd_faq_node() {
	$pairs = array();

	if ( is_page( 'faq' ) ) {
		$page  = get_queried_object();
		$pairs = murtadd_faq_pairs_from_html( isset( $page->post_content ) ? $page->post_content : '' );
	} elseif ( is_tax( 'murtadd_doubt_category' ) ) {
		$term = get_queried_object();
		if ( ! $term || in_array( $term->slug, array( 'emotional', 'identity' ), true ) ) {
			return null;
		}
		$posts = isset( $GLOBALS['wp_query']->posts ) ? (array) $GLOBALS['wp_query']->posts : array();
		foreach ( $posts as $post ) {
			$question = trim( wp_strip_all_tags( (string) get_post_meta( $post->ID, '_murtadd_doubt_statement', true ) ) );
			$response = (string) get_post_meta( $post->ID, '_murtadd_short_response', true );
			if ( '' === $question || '' === trim( $response ) ) {
				continue;
			}
			$pairs[] = array(
				'@type'          => 'Question',
				'name'           => $question,
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => wp_trim_words( $response, 24 ),
					'url'   => get_permalink( $post ),
				),
			);
		}
	}

	if ( ! $pairs ) {
		return null;
	}
	return array(
		'@type'      => 'FAQPage',
		'@id'        => murtadd_current_url() . '#faq',
		'mainEntity' => $pairs,
	);
}

/**
 * The address of the current view, for @id values and the last crumb.
 *
 * @return string
 */
function murtadd_current_url() {
	if ( is_singular() ) {
		return (string) get_permalink( get_queried_object_id() );
	}
	if ( is_tax() || is_category() || is_tag() ) {
		$link = get_term_link( get_queried_object() );
		return is_wp_error( $link ) ? home_url( '/' ) : $link;
	}
	if ( is_post_type_archive() ) {
		$link = get_post_type_archive_link( (string) get_query_var( 'post_type' ) );
		return $link ? $link : home_url( '/' );
	}
	return home_url( '/' );
}

/**
 * The graph for the current view, or null where the view has none: the main
 * node, the BreadcrumbList and, where the page is written as questions and
 * answers, an FAQPage.
 *
 * Built apart from printing so the same nodes can be printed as the theme's
 * own JSON-LD or handed to Rank Math's graph (inc/rank-math.php). A single
 * node is returned as itself, so a view with only its main node prints as
 * it always has.
 *
 * @return array|null A node or a graph, with its @context.
 */
function murtadd_structured_data_node() {
	$nodes   = array();
	$primary = murtadd_structured_data_primary_node();
	if ( $primary ) {
		foreach ( isset( $primary['@graph'] ) ? $primary['@graph'] : array( $primary ) as $node ) {
			unset( $node['@context'] );
			$nodes[] = $node;
		}
	}
	foreach ( array( murtadd_breadcrumb_node(), function_exists( 'murtadd_faq_node' ) ? murtadd_faq_node() : null ) as $extra ) {
		if ( $extra ) {
			$nodes[] = $extra;
		}
	}
	if ( ! $nodes ) {
		return null;
	}
	if ( 1 === count( $nodes ) ) {
		return array( '@context' => 'https://schema.org' ) + $nodes[0];
	}
	return array(
		'@context' => 'https://schema.org',
		'@graph'   => $nodes,
	);
}

/**
 * Print the graph, unless Rank Math is printing structured data: then the
 * nodes it lacks are added to its graph instead (inc/rank-math.php), so the
 * page carries one graph, not two.
 */
function murtadd_structured_data() {
	if ( function_exists( 'murtadd_rank_math_schema_active' ) && murtadd_rank_math_schema_active() ) {
		return;
	}
	$node = murtadd_structured_data_node();
	if ( $node ) {
		murtadd_print_jsonld( $node );
	}
}
add_action( 'wp_head', 'murtadd_structured_data' );
