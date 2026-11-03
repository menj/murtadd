<?php
/**
 * Structured data (JSON-LD).
 *
 * Only types on Google's list of supported structured data are emitted, each
 * for the feature it serves and matching what the page shows:
 *
 * Doubts     -> Article (the statement, which is the page's h1, as headline)
 * Rebuttals  -> Article, with the claim under review in `about`
 * Fatwa      -> Article
 * Front page -> WebSite + Organization
 * Inner views -> BreadcrumbList, built from the visible trail
 *
 * A doubt is not a QAPage: Google's Q&A feature is for pages where readers
 * submit answers, and every answer here is the site's own. FAQPage and the
 * sitelinks SearchAction are not on Google's list and are not emitted.
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
		'url'   => murtadd_author_url(),
	);

	$same_as = array_values( murtadd_social_profiles() );
	if ( $same_as ) {
		$node['sameAs'] = $same_as;
	}

	return $node;
}

/**
 * The author's address: the About page where it is published, else home.
 *
 * Google's Article guidance asks for an author url; the account archive is
 * closed (inc/hardening.php), so the page that says who writes is the one.
 *
 * @return string
 */
function murtadd_author_url() {
	$about = function_exists( 'get_page_by_path' ) ? get_page_by_path( 'about' ) : null;
	if ( $about && isset( $about->post_status ) && 'publish' === $about->post_status ) {
		return (string) get_permalink( $about );
	}
	return home_url( '/' );
}

/**
 * The image for an Article: the share image (the featured image where one is
 * set, else the generated card), the same one the social tags carry.
 *
 * @return string[]
 */
function murtadd_schema_image() {
	$image = get_site_icon_url( 512 );
	if ( ! $image ) {
		$image = get_stylesheet_directory_uri() . '/assets/icons/site-icon-512.png';
	}
	/** This filter is documented in inc/meta-tags.php */
	$image = (string) apply_filters( 'murtadd_share_image', $image );
	return '' !== $image ? array( $image ) : array();
}

/**
 * The view's main node: WebSite and Organization on the front page, an
 * Article for a doubt, a rebuttal or a fatwa entry. Null elsewhere.
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

	// A Doubt: the statement is the page's h1, so it is the headline.
	if ( 'murtadd_doubt' === $type ) {
		$question = get_post_meta( $post_id, '_murtadd_doubt_statement', true );
		$answer   = get_post_meta( $post_id, '_murtadd_short_response', true );

		if ( ! $question || ! $answer ) {
			return null;
		}

		return array(
			'@context'      => 'https://schema.org',
			'@type'         => 'Article',
			'@id'           => get_permalink() . '#article',
			'headline'      => murtadd_schema_text( $question ),
			'description'   => wp_trim_words( murtadd_schema_text( $answer ), 40 ),
			'image'         => murtadd_schema_image(),
			'datePublished' => get_the_date( 'c' ),
			'dateModified'  => get_the_modified_date( 'c' ),
			'url'           => get_permalink(),
			'mainEntityOfPage' => get_permalink(),
			'publisher'     => murtadd_organization_node(),
			'author'        => murtadd_author_node() ?: murtadd_organization_node(),
			'isPartOf'      => array( '@id' => home_url( '/#website' ) ),
		);
	}

	// A Rebuttal assesses a specific claim, named in `about`.
	if ( 'murtadd_rebuttal' === $type ) {
		$claim = get_post_meta( $post_id, '_murtadd_claim', true );

		$node = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'Article',
			'@id'           => get_permalink() . '#article',
			'headline'      => murtadd_schema_text( get_the_title() ),
			'image'         => murtadd_schema_image(),
			'datePublished' => get_the_date( 'c' ),
			'dateModified'  => get_the_modified_date( 'c' ),
			'url'           => get_permalink(),
			'mainEntityOfPage' => get_permalink(),
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
		'headline'      => murtadd_schema_text( get_the_title() ),
		'image'         => murtadd_schema_image(),
		'datePublished' => get_the_date( 'c' ),
		'dateModified'  => get_the_modified_date( 'c' ),
		'url'           => get_permalink(),
		'mainEntityOfPage' => get_permalink(),
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
 * node and the BreadcrumbList.
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
	$breadcrumbs = murtadd_breadcrumb_node();
	if ( $breadcrumbs ) {
		$nodes[] = $breadcrumbs;
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
