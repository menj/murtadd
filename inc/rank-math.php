<?php
/**
 * Rank Math compatibility.
 *
 * With Rank Math active the theme does not repeat what the plugin does. The
 * description, Open Graph and Twitter tags, robots and canonical belong to
 * Rank Math (inc/meta-tags.php stands down), and the page carries one JSON-LD
 * graph, Rank Math's, not two.
 *
 * What Rank Math cannot know comes from the content model, and is supplied
 * through the plugin's own hooks, by one rule: a theme node is added only
 * when the graph has no node of its family; where it has one, the theme
 * fills fields that node leaves empty and never overwrites one.
 *
 *  - Doubts:    the QAPage (question and accepted answer) is added. No SEO
 *               plugin builds one from a doubt's statement and short response.
 *  - Rebuttals: Rank Math's Article gains `about` (the Claim under review)
 *               and `citation` (the sources panel).
 *  - Fatwa:     Rank Math's Article gains `about` (the scholar or body) and
 *               `citation` (the primary source).
 *  - Front page: Rank Math's WebSite and Organization stand.
 *  - Articles:  Rank Math's Article gains the theme's image and the
 *               author's url where it has none.
 *  - Page frame: Rank Math's WebPage gains the theme's frame properties
 *               (inc/schema-graph.php); the sidebars, footer, verses and
 *               glossary are added, each under its own key, and a node
 *               whose @id Rank Math already defines is filled, not repeated.
 *  - All schema.org types stay, Google-supported or not (owner, 2026-11-03).
 *  - Title:     the tag reads "<page> | <site name>" under 60 characters,
 *               built by murtadd_document_title() (inc/seo-title.php).
 *  - Breadcrumbs: the theme's BreadcrumbList is added only when Rank Math's
 *               graph has none; with Rank Math's breadcrumbs on, its trail
 *               is also the one shown (murtadd_breadcrumbs()).
 *
 * A description Rank Math leaves empty is filled from the content model, and
 * search results and 404s stay noindex. If Rank Math's Schema module is
 * switched off, the theme prints its own graph as before. Without Rank Math
 * nothing here runs.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether Rank Math is active.
 *
 * @return bool
 */
function murtadd_rank_math_active() {
	return defined( 'RANK_MATH_VERSION' );
}

/**
 * Whether Rank Math is active and printing structured data.
 *
 * @return bool
 */
function murtadd_rank_math_schema_active() {
	if ( ! murtadd_rank_math_active() ) {
		return false;
	}
	if ( class_exists( '\RankMath\Helper' ) && method_exists( '\RankMath\Helper', 'is_module_active' ) ) {
		return (bool) \RankMath\Helper::is_module_active( 'rich-snippet' );
	}
	return true;
}

/**
 * Whether Rank Math's breadcrumbs are switched on. Then its trail is shown
 * and its BreadcrumbList stands; the theme's yields under the family rule.
 *
 * @return bool
 */
function murtadd_rank_math_breadcrumbs_active() {
	if ( ! murtadd_rank_math_active() ) {
		return false;
	}
	return class_exists( '\RankMath\Helper' ) && method_exists( '\RankMath\Helper', 'is_breadcrumbs_enabled' ) && (bool) \RankMath\Helper::is_breadcrumbs_enabled();
}

/**
 * The family a schema.org type belongs to, for the "add only when missing"
 * rule. Types that are not listed are their own family.
 *
 * @param string|string[] $type A node's @type.
 * @return string
 */
function murtadd_schema_family( $type ) {
	$type     = is_array( $type ) ? (string) reset( $type ) : (string) $type;
	$families = array(
		'Article'               => 'Article',
		'BlogPosting'           => 'Article',
		'NewsArticle'           => 'Article',
		'TechArticle'           => 'Article',
		'ScholarlyArticle'      => 'Article',
		'Organization'          => 'Organization',
		'NewsMediaOrganization' => 'Organization',
		'EducationalOrganization' => 'Organization',
		// One page node per page: Rank Math's WebPage stands and the theme's
		// subtype fills what it leaves empty. QAPage and FAQPage stay their
		// own families, as before.
		'WebPage'               => 'WebPage',
		'CollectionPage'        => 'WebPage',
		'SearchResultsPage'     => 'WebPage',
		'AboutPage'             => 'WebPage',
		'ContactPage'           => 'WebPage',
		'ItemPage'              => 'WebPage',
		'ProfilePage'           => 'WebPage',
	);
	return $families[ $type ] ?? $type;
}

/**
 * Merge the theme's nodes into Rank Math's graph by family.
 *
 * Kept free of WordPress calls so it can be tested on plain arrays.
 *
 * @param array      $data Rank Math's entities, keyed by name.
 * @param array|null $ours The theme's node or graph (with @context), or null.
 * @return array
 */
function murtadd_rank_math_merge( $data, $ours ) {
	if ( ! is_array( $data ) || ! is_array( $ours ) ) {
		return $data;
	}

	$nodes = isset( $ours['@graph'] ) && is_array( $ours['@graph'] ) ? $ours['@graph'] : array( $ours );

	// Rank Math's nodes by family, and the ids it already defines.
	$present = array();
	$ids     = array();
	foreach ( $data as $key => $node ) {
		if ( is_array( $node ) && isset( $node['@type'] ) ) {
			$present[ murtadd_schema_family( $node['@type'] ) ][] = $key;
			if ( isset( $node['@id'] ) ) {
				$ids[ (string) $node['@id'] ] = true;
			}
		}
	}

	foreach ( $nodes as $node ) {
		if ( ! is_array( $node ) || empty( $node['@type'] ) ) {
			continue;
		}
		unset( $node['@context'] );
		$family = murtadd_schema_family( $node['@type'] );

		// A node Rank Math already defines under the same @id is filled, not
		// added again, whatever its family.
		if ( isset( $node['@id'] ) && isset( $ids[ (string) $node['@id'] ] ) && ( empty( $present[ $family ] ) || in_array( $family, murtadd_schema_many_families(), true ) ) ) {
			foreach ( $data as $key => $existing ) {
				if ( is_array( $existing ) && isset( $existing['@id'] ) && (string) $existing['@id'] === (string) $node['@id'] ) {
					foreach ( $node as $field => $value ) {
						if ( '@' !== $field[0] && empty( $data[ $key ][ $field ] ) ) {
							$data[ $key ][ $field ] = $value;
						}
					}
				}
			}
			continue;
		}

		// Frame parts, verses and images are many per page: one with a new
		// @id is added beside Rank Math's, never folded into it.
		$many = in_array( $family, murtadd_schema_many_families(), true );
		if ( empty( $present[ $family ] ) || ( $many && isset( $node['@id'] ) && ! isset( $ids[ (string) $node['@id'] ] ) ) ) {
			// Embedded nodes Rank Math already defines become references.
			foreach ( array( 'publisher', 'author' ) as $ref ) {
				if ( isset( $node[ $ref ]['@id'] ) && isset( $ids[ (string) $node[ $ref ]['@id'] ] ) ) {
					$node[ $ref ] = array( '@id' => $node[ $ref ]['@id'] );
				}
			}
			// Several nodes of one family (two sidebars, many verses) each
			// get their own key.
			$key = 'murtadd_' . strtolower( $family );
			for ( $n = 2; isset( $data[ $key ] ); $n++ ) {
				$key = 'murtadd_' . strtolower( $family ) . '_' . $n;
			}
			$data[ $key ] = $node;
			if ( isset( $node['@id'] ) ) {
				$ids[ (string) $node['@id'] ] = true;
			}
			continue;
		}

		// The family is there: fill what it leaves empty, overwrite nothing.
		foreach ( $present[ $family ] as $key ) {
			foreach ( murtadd_rank_math_fill_fields() as $field ) {
				if ( isset( $node[ $field ] ) && empty( $data[ $key ][ $field ] ) ) {
					$data[ $key ][ $field ] = $node[ $field ];
				}
			}
			// A Journal post: Rank Math's plain Article becomes a BlogPosting and
			// gains the Journal (a Blog) in isPartOf, keeping what it lists.
			if ( 'BlogPosting' === $node['@type'] ) {
				if ( 'Article' === ( $data[ $key ]['@type'] ?? '' ) ) {
					$data[ $key ]['@type'] = 'BlogPosting';
				}
				$data = murtadd_rank_math_merge_part_of( $data, $key, $node['isPartOf'] ?? array() );
			}
			// The author's About address, where Rank Math's author has none.
			if ( ! empty( $node['author']['url'] ) && isset( $data[ $key ]['author'] ) && is_array( $data[ $key ]['author'] ) ) {
				$data = murtadd_rank_math_author_url( $data, $key, (string) $node['author']['url'] );
			}
		}
	}

	return $data;
}

/**
 * Add our isPartOf entries to a Rank Math node, skipping any @id it has.
 *
 * @param array $data  Rank Math's graph.
 * @param mixed $key   Node key.
 * @param array $parts Our isPartOf (one reference or a list).
 * @return array
 */
function murtadd_rank_math_merge_part_of( $data, $key, $parts ) {
	$parts = isset( $parts['@id'] ) ? array( $parts ) : (array) $parts;
	$have  = $data[ $key ]['isPartOf'] ?? array();
	$have  = isset( $have['@id'] ) ? array( $have ) : (array) $have;
	$ids   = array();
	foreach ( $have as $h ) {
		if ( isset( $h['@id'] ) ) {
			$ids[ (string) $h['@id'] ] = true;
		}
	}
	foreach ( $parts as $p ) {
		if ( is_array( $p ) && isset( $p['@id'] ) && ! isset( $ids[ (string) $p['@id'] ] ) ) {
			$have[] = $p;
		}
	}
	if ( $have ) {
		$data[ $key ]['isPartOf'] = 1 === count( $have ) ? $have[0] : $have;
	}
	return $data;
}

/**
 * Families a page holds several nodes of (frame parts, verses, images).
 *
 * @return string[]
 */
function murtadd_schema_many_families() {
	return array( 'WPSideBar', 'WPFooter', 'WPHeader', 'SiteNavigationElement', 'Quotation', 'Book', 'Chapter', 'DefinedTermSet', 'ImageObject' );
}

/**
 * The fields the theme fills on a node of a family Rank Math already has,
 * where Rank Math leaves them empty.
 *
 * @return string[]
 */
function murtadd_rank_math_fill_fields() {
	return array(
		'about', 'citation', 'image',
		// Article details and the page frame (inc/schema-graph.php).
		'mentions', 'genre', 'abstract', 'articleSection', 'wordCount', 'inLanguage',
		'isAccessibleForFree', 'copyrightHolder', 'copyrightYear', 'accessMode', 'accessModeSufficient',
		'dateCreated', 'isBasedOn', 'speakable', 'breadcrumb', 'primaryImageOfPage', 'lastReviewed',
		'significantLink',
	);
}

/**
 * Give the author of one of Rank Math's nodes a url where it has none: on
 * the author itself when embedded, else on the Person node it points to.
 *
 * @param array  $data Rank Math's entities.
 * @param string $key  The node whose author is filled.
 * @param string $url  The theme's author url.
 * @return array
 */
function murtadd_rank_math_author_url( $data, $key, $url ) {
	$author = $data[ $key ]['author'];
	if ( isset( $author['@type'] ) || ! isset( $author['@id'] ) ) {
		if ( empty( $author['url'] ) ) {
			$data[ $key ]['author']['url'] = $url;
		}
		return $data;
	}
	foreach ( $data as $k => $n ) {
		if ( is_array( $n ) && isset( $n['@id'] ) && (string) $n['@id'] === (string) $author['@id'] && empty( $n['url'] ) ) {
			$data[ $k ]['url'] = $url;
		}
	}
	return $data;
}

/**
 * Rank Math's graph, with the theme's nodes merged in.
 *
 * @param array $data   Rank Math's entities.
 * @param mixed $jsonld The plugin's JSON-LD object.
 * @return array
 */
function murtadd_rank_math_json_ld( $data, $jsonld = null ) {
	$data = murtadd_rank_math_merge( $data, murtadd_structured_data_node() );
	return murtadd_article_backfill( $data, murtadd_article_backfill_image(), murtadd_article_backfill_dates() );
}

/**
 * Give every Article-family node an image and, on a single post, its dates.
 *
 * Rank Math can print an Article on views where the theme has none of its
 * own (the front page, a doubt, the FAQ page), so the merge has nothing to
 * fill it from. Fields Rank Math set are kept. Kept free of WordPress calls
 * so it can be tested on plain arrays.
 *
 * @param array      $data  Rank Math's entities, after the merge.
 * @param array|null $image The image: a reference, an ImageObject or a URL list.
 * @param array      $dates datePublished and dateModified from the post, or empty.
 * @return array
 */
function murtadd_article_backfill( $data, $image, $dates ) {
	if ( ! is_array( $data ) ) {
		return $data;
	}
	foreach ( $data as $key => $node ) {
		if ( ! is_array( $node ) || empty( $node['@type'] ) ) {
			continue;
		}
		$is_article = false;
		foreach ( (array) $node['@type'] as $type ) {
			if ( 'Article' === murtadd_schema_family( $type ) ) {
				$is_article = true;
				break;
			}
		}
		if ( ! $is_article ) {
			continue;
		}
		if ( empty( $node['image'] ) && $image ) {
			$data[ $key ]['image'] = $image;
		}
		foreach ( (array) $dates as $field => $value ) {
			if ( empty( $node[ $field ] ) && '' !== (string) $value ) {
				$data[ $key ][ $field ] = $value;
			}
		}
	}
	return $data;
}

/**
 * The image for an Article that lacks one: the featured image as an
 * ImageObject where the post has one, else the share image the social tags
 * carry (the 1200 by 630 card on a content type, else the site icon).
 *
 * @return array|null
 */
function murtadd_article_backfill_image() {
	if ( is_singular() && function_exists( 'has_post_thumbnail' ) && has_post_thumbnail() ) {
		$src = wp_get_attachment_image_src( (int) get_post_thumbnail_id(), 'full' );
		if ( $src && ! empty( $src[0] ) ) {
			$node = array(
				'@type' => 'ImageObject',
				'url'   => $src[0],
			);
			if ( ! empty( $src[1] ) && ! empty( $src[2] ) ) {
				$node['width']  = (int) $src[1];
				$node['height'] = (int) $src[2];
			}
			return $node;
		}
	}
	$urls = murtadd_schema_image();
	return $urls ? $urls : null;
}

/**
 * The post's own dates on a single view, for an Article that lacks them.
 * Other views get none: the theme invents no date.
 *
 * @return array
 */
function murtadd_article_backfill_dates() {
	if ( ! is_singular() ) {
		return array();
	}
	return array(
		'datePublished' => (string) get_the_date( 'c' ),
		'dateModified'  => (string) get_the_modified_date( 'c' ),
	);
}

/**
 * The theme's description where Rank Math has none.
 *
 * @param string $description Rank Math's description.
 * @return string
 */
function murtadd_rank_math_description( $description ) {
	if ( '' !== trim( (string) $description ) ) {
		return $description;
	}
	$ours = murtadd_meta_description_text();
	return '' !== $ours ? $ours : $description;
}

/**
 * The title tag, by the theme's rule, whatever Rank Math built.
 *
 * Rank Math stores a title per post (the guides were seeded with a bare
 * one) and prints it without the site name or any length check. The rule
 * is "<page> | <site name>" under 60 characters, so the theme's builder
 * decides.
 *
 * @param string $title Rank Math's title.
 * @return string
 */
function murtadd_rank_math_title( $title ) {
	$ours = murtadd_document_title();
	return '' !== $ours ? $ours : $title;
}

/**
 * Search results and 404s stay out of the index, as they do without Rank Math.
 *
 * @param array $robots Rank Math's robots directives.
 * @return array
 */
function murtadd_rank_math_robots( $robots ) {
	if ( is_search() || is_404() ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'follow';
	}
	return $robots;
}

if ( murtadd_rank_math_active() ) {
	add_filter( 'rank_math/json_ld', 'murtadd_rank_math_json_ld', 99, 2 );
	add_filter( 'rank_math/frontend/description', 'murtadd_rank_math_description' );
	add_filter( 'rank_math/frontend/robots', 'murtadd_rank_math_robots' );
	add_filter( 'rank_math/frontend/title', 'murtadd_rank_math_title', 99 );
}
