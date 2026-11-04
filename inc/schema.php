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
 * Rebuttals  -> Article about a Claim, with the sources cited
 * Fatwa      -> Article
 * Letters    -> Article, genre "open letter"
 * Journal    -> BlogPosting, part of the Journal (a Blog)
 * The page frame, scripture, glossary and images: inc/schema-graph.php
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
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . '</script>' . "\n";
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

	// The saved profile URLs double as the publisher's sameAs set, with
	// https://menj.bio appended.
	$same_as = array_values( murtadd_social_profiles() );
	if ( $same_as ) {
		$node['sameAs'] = murtadd_schema_with_menj( $same_as );
	}

	return $node;
}

/**
 * The author as a Person node, carrying the profile URLs as sameAs.
 *
 * The owner ruled on 4 November 2026 that the structured data names him,
 * Mohd Elfie Nieshaem Juferi, at https://menj.bio, as the author of every
 * entry. Nothing visible names him. The saved profile URLs that render as
 * footer icons stay in sameAs, ahead of https://menj.bio. The Author name
 * setting is no longer read here.
 *
 * @return array
 */
function murtadd_author_node() {
	$node = array(
		'@type' => 'Person',
		'@id'   => murtadd_author_id(),
		'name'  => 'Mohd Elfie Nieshaem Juferi',
		'url'   => 'https://menj.bio',
	);

	// The author always carries https://menj.bio, after any saved profiles.
	$node['sameAs'] = murtadd_schema_with_menj( array_values( murtadd_social_profiles() ) );

	return $node;
}

/**
 * The author Person's @id, which every author reference uses.
 *
 * @return string
 */
function murtadd_author_id() {
	return home_url( '/#author' );
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

	if ( ! is_singular( array( 'murtadd_doubt', 'murtadd_rebuttal', 'murtadd_fatwa', 'murtadd_letter', 'post' ) ) ) {
		return null;
	}

	$post_id = get_the_ID();
	$type    = get_post_type();

	// A Journal post is a dated entry in the Journal (inc/seed-journal.php).
	if ( 'post' === $type ) {
		$journal = function_exists( 'murtadd_journal_url' ) ? murtadd_journal_url() : home_url( '/journal/' );
		$node    = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'BlogPosting',
			'@id'              => get_permalink() . '#article',
			'headline'         => murtadd_schema_text( get_the_title() ),
			'image'            => murtadd_schema_image(),
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'url'              => get_permalink(),
			'mainEntityOfPage' => get_permalink(),
			'articleSection'   => __( 'Journal', 'murtadd' ),
			'publisher'        => murtadd_organization_node(),
			'author'           => array( '@id' => murtadd_author_id() ),
			'isPartOf'         => array(
				array( '@id' => home_url( '/#website' ) ),
				array(
					'@type' => 'Blog',
					'@id'   => $journal . '#blog',
					'name'  => __( 'Journal', 'murtadd' ),
					'url'   => $journal,
				),
			),
		);
		$excerpt = (string) get_post_field( 'post_excerpt', $post_id );
		if ( '' !== trim( $excerpt ) ) {
			$node['description'] = murtadd_schema_text( $excerpt );
		}
		return $node;
	}

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
			'headline'      => murtadd_schema_text( get_the_title() ),
			'image'         => murtadd_schema_image(),
			'datePublished' => get_the_date( 'c' ),
			'dateModified'  => get_the_modified_date( 'c' ),
			'url'           => get_permalink(),
			'mainEntityOfPage' => get_permalink(),
			'publisher'     => murtadd_organization_node(),
			'author'        => array( '@id' => murtadd_author_id() ),
			'isPartOf'      => array( '@id' => home_url( '/#website' ) ),
		);

		if ( $claim ) {
			// The claim box shows the claim as circulated, on this page: the
			// page is where it appears. No firstAppearance: the claim's own
			// origin is described by type, never linked.
			$node['about'] = array(
				'@type'      => 'Claim',
				'@id'        => get_permalink() . '#claim',
				'text'       => wp_strip_all_tags( $claim ),
				'appearance' => array( '@id' => get_permalink() . '#article' ),
			);
		}

		// The sources panel: each entry as a work, with its link where shown.
		$citations = array();
		foreach ( (array) get_post_meta( $post_id, '_murtadd_sources', true ) as $source ) {
			if ( ! empty( $source['citation_text'] ) ) {
				$work = array(
					'@type' => 'CreativeWork',
					'name'  => murtadd_schema_text( $source['citation_text'] ),
				);
				if ( ! empty( $source['url'] ) ) {
					$work['url'] = esc_url_raw( $source['url'] );
				}
				$citations[] = $work;
			}
		}
		if ( $citations ) {
			$node['citation'] = $citations;
		}

		return $node;
	}

	// A letter is an archived open letter, reproduced as it was published.
	if ( 'murtadd_letter' === $type ) {
		$node = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'Article',
			'@id'              => get_permalink() . '#article',
			'genre'            => 'open letter',
			'headline'         => murtadd_schema_text( get_the_title() ),
			'image'            => murtadd_schema_image(),
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'url'              => get_permalink(),
			'mainEntityOfPage' => get_permalink(),
			'publisher'        => murtadd_organization_node(),
			'author'           => array( '@id' => murtadd_author_id() ),
			'isPartOf'         => array( '@id' => home_url( '/#website' ) ),
		);
		// The dateline shows the date the letter first ran.
		$date = (string) get_post_meta( $post_id, '_murtadd_letter_date', true );
		$ts   = '' !== $date ? strtotime( $date ) : false;
		if ( $ts ) {
			$node['dateCreated'] = gmdate( 'Y-m-d', $ts );
		}
		// "Read at the original publication" links the letter as first run.
		$original = (string) get_post_meta( $post_id, '_murtadd_letter_url', true );
		if ( '' !== $original ) {
			$node['isBasedOn'] = esc_url_raw( $original );
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
		'author'        => array( '@id' => murtadd_author_id() ),
	);
	$about = array();
	if ( $scholar ) {
		$about[] = wp_strip_all_tags( $scholar );
	}
	// The school chip the entry shows.
	$schools = get_the_terms( $post_id, 'murtadd_school' );
	if ( $schools && ! is_wp_error( $schools ) ) {
		$school  = reset( $schools );
		$link    = get_term_link( $school );
		$about[] = array(
			'@type' => 'Thing',
			'@id'   => ( is_wp_error( $link ) ? home_url( '/' ) : $link ) . '#school',
			'name'  => murtadd_schema_text( $school->name ),
			'url'   => is_wp_error( $link ) ? '' : $link,
		);
	}
	if ( $about ) {
		$node['about'] = 1 === count( $about ) ? $about[0] : $about;
	}
	// The position summary, shown in the site's own words.
	$summary = (string) get_post_meta( $post_id, '_murtadd_position_summary', true );
	if ( '' !== trim( $summary ) ) {
		$node['abstract'] = murtadd_schema_text( $summary );
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
	if ( function_exists( 'is_search' ) && is_search() ) {
		return (string) get_search_link();
	}
	if ( function_exists( 'is_home' ) && is_home() && ! is_front_page() ) {
		$page = (int) get_option( 'page_for_posts' );
		$link = $page ? get_permalink( $page ) : '';
		return $link ? (string) $link : home_url( '/journal/' );
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
	if ( function_exists( 'murtadd_schema_complete_graph' ) ) {
		$nodes = murtadd_schema_complete_graph( $nodes );
	}
	if ( ! $nodes ) {
		return null;
	}
	$nodes = murtadd_schema_one_author( $nodes );
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

/**
 * A sameAs list with https://menj.bio appended when it is missing.
 *
 * Existing entries keep their order. The owner ruled on 4 November 2026 that
 * every sameAs the theme builds includes this address.
 *
 * @param string[] $same_as Existing entries.
 * @return string[]
 */
function murtadd_schema_with_menj( $same_as ) {
	$same_as = array_values( (array) $same_as );
	if ( ! in_array( 'https://menj.bio', $same_as, true ) ) {
		$same_as[] = 'https://menj.bio';
	}
	return $same_as;
}

/**
 * Types that carry the author: articles and the pages of the site.
 *
 * @return string[]
 */
function murtadd_schema_authored_types() {
	return array( 'Article', 'BlogPosting', 'NewsArticle', 'ScholarlyArticle', 'WebPage', 'AboutPage', 'ProfilePage', 'FAQPage', 'CollectionPage', 'ContactPage', 'QAPage', 'ItemPage' );
}

/**
 * One author across a graph, the theme's own or Rank Math's after the merge.
 *
 * Every author value, at any depth, points to murtadd_author_id(); every
 * top-level article or page node without an author gets that reference; a
 * Person that Rank Math made for the WordPress user, which nothing points to
 * once the authors are repointed, is dropped and its sameAs entries join the
 * author's; and the author Person is added where the graph lacks it. The
 * publisher is left as it is.
 *
 * @param array $nodes Top-level nodes (a list, or Rank Math's keyed array).
 * @return array
 */
function murtadd_schema_one_author( $nodes ) {
	if ( ! is_array( $nodes ) ) {
		return $nodes;
	}
	$id   = murtadd_author_id();
	$old  = array();
	$walk = function ( $node ) use ( &$walk, &$old, $id ) {
		if ( ! is_array( $node ) ) {
			return $node;
		}
		foreach ( $node as $k => $v ) {
			if ( 'author' === $k && is_array( $v ) ) {
				if ( ! empty( $v['@id'] ) && $id !== $v['@id'] ) {
					$old[ (string) $v['@id'] ] = true;
				}
				$node[ $k ] = array( '@id' => $id );
			} elseif ( is_array( $v ) ) {
				$node[ $k ] = $walk( $v );
			}
		}
		return $node;
	};
	$nodes = $walk( $nodes );
	$extra = array();
	$out   = array();
	$have  = null;
	foreach ( $nodes as $key => $node ) {
		if ( ! is_array( $node ) ) {
			$out[ $key ] = $node;
			continue;
		}
		$types = (array) ( $node['@type'] ?? array() );
		if ( ! empty( $node['@id'] ) && isset( $old[ (string) $node['@id'] ] ) && in_array( 'Person', $types, true ) ) {
			$extra = array_merge( $extra, array_filter( (array) ( $node['sameAs'] ?? array() ), 'is_string' ) );
			continue;
		}
		if ( array_intersect( $types, murtadd_schema_authored_types() ) && empty( $node['author'] ) ) {
			$node['author'] = array( '@id' => $id );
		}
		if ( $id === ( $node['@id'] ?? '' ) ) {
			$have = $key;
		}
		$out[ $key ] = $node;
	}
	$person = murtadd_author_node();
	if ( null === $have ) {
		if ( array_values( $nodes ) === $nodes ) {
			$out[] = $person;
			$have  = count( $out ) - 1;
		} else {
			$have         = 'murtadd_author';
			$out[ $have ] = $person;
		}
	} else {
		$out[ $have ] = array_merge( $out[ $have ], $person );
	}
	$same = array_merge( (array) ( $out[ $have ]['sameAs'] ?? array() ), $extra );
	$same = array_values( array_unique( array_diff( $same, array( 'https://menj.bio' ) ) ) );
	$out[ $have ]['sameAs'] = murtadd_schema_with_menj( $same );
	return array_values( $nodes ) === $nodes ? array_values( $out ) : $out;
}
