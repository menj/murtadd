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

		if ( empty( $present[ $family ] ) ) {
			// Embedded nodes Rank Math already defines become references.
			foreach ( array( 'publisher', 'author' ) as $ref ) {
				if ( isset( $node[ $ref ]['@id'] ) && isset( $ids[ (string) $node[ $ref ]['@id'] ] ) ) {
					$node[ $ref ] = array( '@id' => $node[ $ref ]['@id'] );
				}
			}
			$data[ 'murtadd_' . strtolower( $family ) ] = $node;
			continue;
		}

		// The family is there: fill what it leaves empty, overwrite nothing.
		foreach ( $present[ $family ] as $key ) {
			foreach ( array( 'about', 'citation' ) as $field ) {
				if ( isset( $node[ $field ] ) && empty( $data[ $key ][ $field ] ) ) {
					$data[ $key ][ $field ] = $node[ $field ];
				}
			}
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
	return murtadd_rank_math_merge( $data, murtadd_structured_data_node() );
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
	$ours = wp_strip_all_tags( (string) murtadd_meta_description() );
	return '' !== $ours ? $ours : $description;
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
}
