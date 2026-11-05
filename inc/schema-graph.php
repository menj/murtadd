<?php
/**
 * Structured data beyond the main node: the page frame, scripture, the
 * glossary, images and article details.
 *
 * inc/schema.php builds each view's main node (QAPage, Article, WebSite) and
 * calls the functions here to complete the graph. Every node and property
 * describes something the page shows, or a plain fact about it (language,
 * dates, word count). Where the page does not show a thing, it is not marked.
 *
 *  - Page frame: a WebPage of the right subtype (CollectionPage for archives
 *    and taxonomies, SearchResultsPage, AboutPage, ContactPage), the sidebar
 *    as a WPSideBar holding the primary SiteNavigationElement, the rail on
 *    doubt and rebuttal singles as a second WPSideBar, and the footer bar as
 *    a WPFooter. There is no header element in this theme, so no WPHeader.
 *  - Scripture: a verse shown on the page is a Quotation, isPartOf a Chapter
 *    (the surah) that isPartOf the Book "The Qur'an". A shown translation is
 *    its own Quotation with translationOfWork pointing at the Arabic.
 *  - Glossary: the terms an entry defines inline are DefinedTerms in one
 *    DefinedTermSet, reached from the entry through mentions.
 *  - Images: the featured image a doubt or rebuttal shows is an ImageObject,
 *    the page's primaryImageOfPage, with the caption only where one shows.
 *
 * All schema.org types stay, Google-supported or not (owner, 2026-11-03).
 * Pinned in tests/SchemaGraphTest.php.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The site language as a BCP 47 tag.
 *
 * @return string
 */
function murtadd_schema_language() {
	$locale = function_exists( 'get_locale' ) ? (string) get_locale() : 'en_US';
	return str_replace( '_', '-', '' !== $locale ? $locale : 'en_US' );
}

/**
 * Whether the current view is a single entry of one of the given types.
 *
 * @param string[] $types Post types.
 * @return bool
 */
function murtadd_schema_single( $types ) {
	return is_singular( $types );
}

/* ---------------------------------------------------------------------
 * Scripture
 * ------------------------------------------------------------------ */

/**
 * The Qur'an as a Book node.
 *
 * @return array
 */
function murtadd_quran_book_node() {
	return array(
		'@type'      => 'Book',
		'@id'        => home_url( '/#quran' ),
		'name'       => "The Qur'an",
		'genre'      => 'Scripture',
		'inLanguage' => 'ar',
		'sameAs'     => array( 'https://www.wikidata.org/wiki/Q428' ),
	);
}

/**
 * Parse a Qur'an reference such as "Qur’an 2:256" or "Quran 2:255-256".
 *
 * @param string $ref Reference as shown.
 * @return array{surah:int,ayah:string}|null
 */
function murtadd_parse_quran_ref( $ref ) {
	if ( preg_match( '/Qur.?an\s+(\d{1,3})\s*:\s*(\d{1,3}(?:\s*[-\x{2013}]\s*\d{1,3})?)/iu', (string) $ref, $m ) ) {
		$surah = (int) $m[1];
		if ( $surah >= 1 && $surah <= 114 ) {
			return array(
				'surah' => $surah,
				'ayah'  => preg_replace( '/\s*[-\x{2013}]\s*/u', '-', $m[2] ),
			);
		}
	}
	return null;
}

/**
 * Quotation nodes for a verse as shown: the verse in the language it is
 * shown in and, where a translation is shown beside the Arabic, the
 * translation as a second Quotation with translationOfWork.
 *
 * @param string $text        The verse as shown.
 * @param string $ref         The reference as shown ("Qur’an 2:256").
 * @param string $fragment    @id fragment, unique on the page.
 * @param string $translation An English rendering shown beside an Arabic verse.
 * @return array[] Quotation nodes, the first the verse itself.
 */
function murtadd_quran_quotation_nodes( $text, $ref, $fragment, $translation = '' ) {
	$text = murtadd_schema_text( $text );
	if ( '' === $text ) {
		return array();
	}
	$here   = murtadd_current_url();
	$parsed = murtadd_parse_quran_ref( $ref );
	$part   = array( '@id' => home_url( '/#quran' ) );
	if ( $parsed ) {
		$part = array(
			'@type'    => 'Chapter',
			'@id'      => home_url( '/#quran-' . $parsed['surah'] ),
			'name'     => 'Surah ' . $parsed['surah'],
			'position' => $parsed['surah'],
			'isPartOf' => array( '@id' => home_url( '/#quran' ) ),
		);
	}
	$arabic = function_exists( 'murtadd_is_arabic' ) && murtadd_is_arabic( $text );
	$verse  = array(
		'@type'      => 'Quotation',
		'@id'        => $here . '#' . $fragment,
		'text'       => $text,
		'inLanguage' => $arabic ? 'ar' : murtadd_schema_language(),
		'isPartOf'   => $part,
	);
	if ( $parsed ) {
		$verse['name'] = murtadd_schema_text( $ref );
	}
	$nodes = array( $verse );
	$translation = murtadd_schema_text( $translation );
	if ( $arabic && '' !== $translation ) {
		$nodes[] = array(
			'@type'             => 'Quotation',
			'@id'               => $here . '#' . $fragment . '-translation',
			'text'              => $translation,
			'inLanguage'        => murtadd_schema_language(),
			'translationOfWork' => array( '@id' => $verse['@id'] ),
		);
	}
	return $nodes;
}

/**
 * The verse the rail shows, or null where the rail shows none.
 *
 * Mirrors template-parts/rail/rail-single.php.
 *
 * @return array|null
 */
function murtadd_rail_verse() {
	$display = get_option( 'murtadd_content_display', array() );
	$display = is_array( $display ) ? $display : array();
	if ( isset( $display['show_scripture'] ) && ! $display['show_scripture'] ) {
		return null;
	}
	$verse = isset( $display['scripture_text'] ) ? $display['scripture_text'] : __( 'Let there be no compulsion in religion.', 'murtadd' );
	$ref   = isset( $display['scripture_ref'] ) ? $display['scripture_ref'] : __( 'Qur’an 2:256', 'murtadd' );
	if ( ! $verse ) {
		return null;
	}
	return array(
		'text' => (string) $verse,
		'ref'  => (string) $ref,
	);
}

/**
 * Verses marked up in stored content (class murtadd-ayah), as Quotations.
 * Only marked elements count; free text is never guessed at.
 *
 * @param string $html Stored content.
 * @return array[]
 */
function murtadd_content_verse_nodes( $html ) {
	$nodes = array();
	if ( false === strpos( (string) $html, 'murtadd-ayah' ) ) {
		return $nodes;
	}
	if ( preg_match_all( '#<(p|span|div|blockquote)\b[^>]*\bclass=(["\'])(?:(?!\2).)*\bmurtadd-ayah\b(?!-)(?:(?!\2).)*\2[^>]*>(.*?)</\1>#s', (string) $html, $m ) ) {
		foreach ( $m[3] as $i => $inner ) {
			foreach ( murtadd_quran_quotation_nodes( $inner, '', 'verse-' . ( $i + 1 ) ) as $node ) {
				$nodes[] = $node;
			}
		}
	}
	return $nodes;
}

/* ---------------------------------------------------------------------
 * Glossary
 * ------------------------------------------------------------------ */

/**
 * The glossary terms the queried entry defines inline, as DefinedTerm nodes.
 * Empty where the entry gets no definitions (Emotional and Identity doubts,
 * the switch off, a view that is not an entry).
 *
 * @return array[]
 */
function murtadd_glossary_term_nodes() {
	if ( ! function_exists( 'murtadd_glossary_matcher' ) || ! is_singular() || is_front_page() ) {
		return array();
	}
	$post_id = (int) get_queried_object_id();
	if ( ! murtadd_glossary_post_allowed( $post_id ) ) {
		return array();
	}
	$matcher = murtadd_glossary_matcher();
	if ( ! $matcher ) {
		return array();
	}
	// The same scope as the tooltips: murtadd_glossary_apply() skips headings,
	// quotations, Arabic and the like, so a term found only there is not
	// defined on the page and gets no node.
	$seen = array();
	foreach ( array(
		(string) get_post_meta( $post_id, '_murtadd_short_response', true ),
		(string) get_post_meta( $post_id, '_murtadd_position_summary', true ),
		(string) get_post_field( 'post_content', $post_id ),
	) as $html ) {
		if ( '' !== trim( $html ) ) {
			murtadd_glossary_apply( '<div>' . $html . '</div>', $seen, 'murtadd-schema-term' );
		}
	}
	$terms = murtadd_glossary_terms();
	$nodes = array();
	foreach ( array_keys( $seen ) as $slug ) {
		if ( ! isset( $terms[ $slug ] ) ) {
			continue;
		}
		$t    = $terms[ $slug ];
		$node = array(
			'@type'            => 'DefinedTerm',
			'@id'              => home_url( '/#glossary-' . $slug ),
			'name'             => $t['term'],
			'description'      => $t['definition'],
			'inDefinedTermSet' => array( '@id' => home_url( '/#glossary' ) ),
		);
		if ( $t['aliases'] ) {
			$node['alternateName'] = array_values( $t['aliases'] );
		}
		$nodes[ $slug ] = $node;
	}
	return array_values( $nodes );
}

/**
 * The DefinedTermSet holding the given terms, or null for none.
 *
 * @param array[] $terms DefinedTerm nodes.
 * @return array|null
 */
function murtadd_glossary_set_node( $terms ) {
	if ( ! $terms ) {
		return null;
	}
	return array(
		'@type'          => 'DefinedTermSet',
		'@id'            => home_url( '/#glossary' ),
		'name'           => __( 'Glossary', 'murtadd' ),
		'inLanguage'     => murtadd_schema_language(),
		'hasDefinedTerm' => $terms,
	);
}

/* ---------------------------------------------------------------------
 * Images
 * ------------------------------------------------------------------ */

/**
 * The featured image a doubt or rebuttal shows, as an ImageObject, or null.
 *
 * @return array|null
 */
function murtadd_primary_image_node() {
	if ( ! murtadd_schema_single( array( 'murtadd_doubt', 'murtadd_rebuttal' ) ) || ! function_exists( 'has_post_thumbnail' ) || ! has_post_thumbnail() ) {
		return null;
	}
	$id  = (int) get_post_thumbnail_id();
	$src = function_exists( 'wp_get_attachment_image_src' ) ? wp_get_attachment_image_src( $id, 'large' ) : false;
	if ( ! $src || empty( $src[0] ) ) {
		return null;
	}
	$node = array(
		'@type'               => 'ImageObject',
		'@id'                 => murtadd_current_url() . '#primaryimage',
		'url'                 => $src[0],
		'contentUrl'          => $src[0],
		'representativeOfPage' => true,
	);
	if ( ! empty( $src[1] ) && ! empty( $src[2] ) ) {
		$node['width']  = (int) $src[1];
		$node['height'] = (int) $src[2];
	}
	$ext     = strtolower( (string) pathinfo( (string) wp_parse_url( $src[0], PHP_URL_PATH ), PATHINFO_EXTENSION ) );
	$formats = array( 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif', 'avif' => 'image/avif', 'svg' => 'image/svg+xml' );
	if ( isset( $formats[ $ext ] ) ) {
		$node['encodingFormat'] = $formats[ $ext ];
	}
	$caption = function_exists( 'wp_get_attachment_caption' ) ? murtadd_schema_text( (string) wp_get_attachment_caption( $id ) ) : '';
	if ( '' !== $caption ) {
		// The caption shows under the image, and is where a credit is written.
		$node['caption']    = $caption;
		$node['creditText'] = $caption;
	}
	return $node;
}

/* ---------------------------------------------------------------------
 * Page frame
 * ------------------------------------------------------------------ */

/**
 * The page subtype for the current view, or '' where the view has no page
 * node of its own (a doubt is its QAPage, the FAQ page its FAQPage).
 *
 * @return string
 */
function murtadd_webpage_type() {
	if ( function_exists( 'is_404' ) && is_404() ) {
		return '';
	}
	if ( function_exists( 'is_search' ) && is_search() ) {
		return 'SearchResultsPage';
	}
	if ( is_singular( 'murtadd_doubt' ) || is_page( 'faq' ) ) {
		return '';
	}
	if ( is_page( 'about' ) ) {
		return 'AboutPage';
	}
	if ( is_page( 'contact' ) ) {
		return 'ContactPage';
	}
	if ( is_tax() || is_category() || is_tag() || is_post_type_archive() || ( function_exists( 'is_home' ) && is_home() && ! is_front_page() ) ) {
		return 'CollectionPage';
	}
	return 'WebPage';
}

/**
 * The visible name of the current view.
 *
 * @return string
 */
function murtadd_webpage_name() {
	if ( is_front_page() ) {
		return murtadd_schema_text( get_bloginfo( 'name' ) );
	}
	if ( is_singular() ) {
		return murtadd_schema_text( get_the_title() );
	}
	$o = get_queried_object();
	if ( is_tax() || is_category() || is_tag() ) {
		return isset( $o->name ) ? murtadd_schema_text( $o->name ) : '';
	}
	if ( is_post_type_archive() ) {
		return murtadd_schema_text( post_type_archive_title( '', false ) );
	}
	return '';
}

/**
 * Properties every page node carries, the QAPage and FAQPage included.
 *
 * @param bool $has_breadcrumb Whether the graph has a BreadcrumbList.
 * @param bool $has_image      Whether the graph has the primary image.
 * @return array
 */
function murtadd_webpage_frame( $has_breadcrumb, $has_image ) {
	$here  = murtadd_current_url();
	$frame = array(
		'url'        => $here,
		'inLanguage' => murtadd_schema_language(),
		'isPartOf'   => array( '@id' => home_url( '/#website' ) ),
		'speakable'  => array(
			'@type'       => 'SpeakableSpecification',
			'cssSelector' => murtadd_speakable_selectors(),
		),
	);
	$name = murtadd_webpage_name();
	if ( '' !== $name ) {
		$frame['name'] = $name;
	}
	if ( $has_breadcrumb ) {
		$frame['breadcrumb'] = array( '@id' => $here . '#breadcrumb' );
	}
	if ( $has_image ) {
		$frame['primaryImageOfPage'] = array( '@id' => $here . '#primaryimage' );
	}
	// "Last reviewed" shows on doubts, rebuttals, letters and Journal posts
	// once an entry is edited more than a day after it went up
	// (murtadd_the_reviewed_date()).
	if ( is_singular( array( 'murtadd_doubt', 'murtadd_rebuttal', 'murtadd_letter', 'post' ) ) ) {
		$published = strtotime( (string) get_the_date( 'c' ) );
		$modified  = strtotime( (string) get_the_modified_date( 'c' ) );
		if ( $published && $modified && $modified > $published + DAY_IN_SECONDS ) {
			$frame['lastReviewed'] = get_the_modified_date( 'c' );
		}
	}
	return $frame;
}

/**
 * The title and the summary paragraph, for SpeakableSpecification.
 *
 * @return string[]
 */
function murtadd_speakable_selectors() {
	if ( is_singular( 'murtadd_doubt' ) ) {
		return array( '.murtadd-doubt-statement', '.murtadd-short-response' );
	}
	if ( is_singular( 'murtadd_rebuttal' ) ) {
		return array( '.murtadd-entry-header h1', '.murtadd-claim-box' );
	}
	if ( is_singular( 'murtadd_fatwa' ) ) {
		return array( '.murtadd-entry-header h1', '.murtadd-position-summary' );
	}
	if ( is_singular( 'post' ) && function_exists( 'murtadd_article_speakable' ) ) {
		return murtadd_article_speakable( (int) get_queried_object_id() );
	}
	if ( is_tax() || is_post_type_archive() ) {
		return array( '.murtadd-archive-header h1', '.murtadd-subtitle' );
	}
	return array( 'h1' );
}

/**
 * The page node for views with no main page node, or null.
 *
 * @param bool $has_breadcrumb Whether the graph has a BreadcrumbList.
 * @param bool $has_image      Whether the graph has the primary image.
 * @return array|null
 */
function murtadd_webpage_node( $has_breadcrumb, $has_image ) {
	$type = murtadd_webpage_type();
	if ( '' === $type ) {
		return null;
	}
	$node = array(
		'@type' => $type,
		'@id'   => murtadd_current_url() . '#webpage',
	) + murtadd_webpage_frame( $has_breadcrumb, $has_image );

	// A school page collects one school's positions: it is about the school.
	if ( is_tax( 'murtadd_school' ) ) {
		$term = get_queried_object();
		if ( $term && isset( $term->name ) ) {
			$about = array(
				'@type' => 'Thing',
				'@id'   => murtadd_current_url() . '#school',
				'name'  => murtadd_schema_text( $term->name ),
				'url'   => murtadd_current_url(),
			);
			if ( ! empty( $term->description ) ) {
				$about['description'] = murtadd_schema_text( $term->description );
			}
			$node['about'] = $about;
		}
	}
	if ( is_singular( array( 'murtadd_rebuttal', 'murtadd_fatwa', 'murtadd_letter', 'post' ) ) ) {
		$node['mainEntity'] = array( '@id' => murtadd_current_url() . '#article' );
	}
	return $node;
}

/**
 * The sidebar: the site's primary navigation, shown on every page.
 *
 * Mirrors sidebar.php.
 *
 * @return array
 */
function murtadd_sidebar_node() {
	// Each item: name, url and, for the dropdowns, the taxonomy listed under it.
	$items = array(
		array( __( 'Home', 'murtadd' ), home_url( '/' ), '' ),
		array( __( 'Journal', 'murtadd' ), function_exists( 'murtadd_journal_url' ) ? murtadd_journal_url() : home_url( '/journal/' ), '' ),
		array( __( 'Topics', 'murtadd' ), get_post_type_archive_link( 'murtadd_doubt' ), 'murtadd_topic' ),
	);
	if ( function_exists( 'murtadd_fatwa_index_has_entries' ) && murtadd_fatwa_index_has_entries() ) {
		$items[] = array( __( 'Fatwa', 'murtadd' ), get_post_type_archive_link( 'murtadd_fatwa' ), 'murtadd_school' );
	}
	$items[] = array( __( 'Doubts', 'murtadd' ), get_post_type_archive_link( 'murtadd_doubt' ), 'murtadd_doubt_category' );
	$items[] = array( __( 'Rebuttals', 'murtadd' ), get_post_type_archive_link( 'murtadd_rebuttal' ), '' );
	$items[] = array( __( 'Letters', 'murtadd' ), get_post_type_archive_link( 'murtadd_letter' ), '' );

	$parts = array();
	foreach ( $items as $item ) {
		if ( ! $item[1] ) {
			continue;
		}
		$part = array(
			'@type' => 'SiteNavigationElement',
			'name'  => $item[0],
			'url'   => (string) $item[1],
		);
		if ( '' !== $item[2] ) {
			// The dropdown sidebar.php prints: every term, empty ones included.
			$sub   = array();
			$found = get_terms( array( 'taxonomy' => $item[2], 'hide_empty' => false ) );
			if ( is_array( $found ) ) {
				foreach ( $found as $term ) {
					$link = get_term_link( $term );
					if ( is_string( $link ) && '' !== $link ) {
						$sub[] = array(
							'@type' => 'SiteNavigationElement',
							'name'  => murtadd_schema_text( $term->name ),
							'url'   => $link,
						);
					}
				}
			}
			if ( $sub ) {
				$part['hasPart'] = $sub;
			}
		}
		$parts[] = $part;
	}
	return array(
		'@type'       => 'WPSideBar',
		'@id'         => home_url( '/#sidebar' ),
		'cssSelector' => '.murtadd-sidebar',
		'hasPart'     => array(
			array(
				'@type'       => 'SiteNavigationElement',
				'@id'         => home_url( '/#primary-nav' ),
				'name'        => __( 'Primary', 'murtadd' ),
				'cssSelector' => '#murtadd-nav',
				'hasPart'     => $parts,
			),
		),
	);
}

/**
 * The rail on doubt and rebuttal singles, with the verse it shows.
 *
 * @param array[] $verse_nodes The rail verse's Quotation nodes, if shown.
 * @return array|null
 */
function murtadd_rail_node( $verse_nodes ) {
	if ( ! murtadd_schema_single( array( 'murtadd_doubt', 'murtadd_rebuttal' ) ) ) {
		return null;
	}
	$node = array(
		'@type'       => 'WPSideBar',
		'@id'         => murtadd_current_url() . '#rail',
		'name'        => __( 'Related', 'murtadd' ),
		'cssSelector' => '.murtadd-rail',
	);
	if ( $verse_nodes ) {
		$node['hasPart'] = array( array( '@id' => $verse_nodes[0]['@id'] ) );
	}
	return $node;
}

/**
 * The footer bar, with the secondary links where they show.
 *
 * Mirrors footer.php. front-page.php prints its own footer section first;
 * the bar is on every page.
 *
 * @return array
 */
function murtadd_footer_node() {
	$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	$node = array(
		'@type'           => 'WPFooter',
		'@id'             => home_url( '/#footer' ),
		'cssSelector'     => '.murtadd-footer-bar',
		'copyrightHolder' => array( '@id' => home_url( '/#organization' ) ),
	);
	if ( '' !== $host ) {
		$node['copyrightNotice'] = '© ' . $host;
	}
	if ( ! is_front_page() ) {
		$nav = array(
			'@type'       => 'SiteNavigationElement',
			'@id'         => home_url( '/#secondary-nav' ),
			'name'        => __( 'Secondary', 'murtadd' ),
			'cssSelector' => '.murtadd-footer-links',
		);
		// The Journal link is always shown first (footer.php).
		$parts = array(
			array(
				'@type' => 'SiteNavigationElement',
				'name'  => __( 'Journal', 'murtadd' ),
				'url'   => function_exists( 'murtadd_journal_url' ) ? murtadd_journal_url() : home_url( '/journal/' ),
			),
		);
		$nav['hasPart'] = $parts;
		// A menu, when assigned, is the editor's; the fallback links are known.
		if ( function_exists( 'murtadd_has_secondary_links' ) && murtadd_has_secondary_links() && ! ( function_exists( 'has_nav_menu' ) && has_nav_menu( 'footer_site' ) ) ) {
			foreach ( murtadd_secondary_links() as $url => $label ) {
				$parts[] = array(
					'@type' => 'SiteNavigationElement',
					'name'  => $label,
					'url'   => (string) $url,
				);
			}
			if ( $parts ) {
				$nav['hasPart'] = $parts;
			}
		}
		$node['hasPart'] = array( $nav );
	}
	return $node;
}

/* ---------------------------------------------------------------------
 * Main node details
 * ------------------------------------------------------------------ */

/**
 * The entry's terms in a taxonomy as Thing nodes with the address shown.
 *
 * @param string $taxonomy Taxonomy.
 * @return array[]
 */
function murtadd_schema_term_things( $taxonomy ) {
	$terms = get_the_terms( get_the_ID(), $taxonomy );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return array();
	}
	$out = array();
	// The template shows the first term as a chip; that one is marked.
	$term = reset( $terms );
	$link = get_term_link( $term );
	$out[] = array(
		'@type' => 'Thing',
		'name'  => murtadd_schema_text( $term->name ),
		'url'   => is_wp_error( $link ) ? '' : (string) $link,
	);
	return $out;
}

/**
 * Article details shared by rebuttals, fatwa entries and letters.
 *
 * @param int $post_id Post.
 * @return array
 */
function murtadd_article_details( $post_id ) {
	$details = array(
		'inLanguage'          => murtadd_schema_language(),
		'isAccessibleForFree' => true,
		'copyrightHolder'     => array( '@id' => home_url( '/#organization' ) ),
		'accessMode'          => array( 'textual' ),
		'accessModeSufficient' => array( 'textual' ),
	);
	$year = (int) substr( (string) get_the_date( 'c' ), 0, 4 );
	if ( $year > 0 ) {
		$details['copyrightYear'] = $year;
	}
	if ( function_exists( 'murtadd_word_count' ) ) {
		$words = murtadd_word_count( $post_id );
		if ( $words ) {
			$details['wordCount'] = $words;
		}
	}
	return $details;
}

/**
 * Fill a node's empty fields from another array; never overwrite.
 *
 * @param array $node   Node.
 * @param array $fields Fields.
 * @return array
 */
function murtadd_schema_fill( $node, $fields ) {
	foreach ( $fields as $k => $v ) {
		if ( ! isset( $node[ $k ] ) || ( '' === $node[ $k ] || array() === $node[ $k ] ) ) {
			$node[ $k ] = $v;
		}
	}
	return $node;
}

/**
 * Add mentions to a node, keeping any it has.
 *
 * @param array   $node     Node.
 * @param array[] $mentions References.
 * @return array
 */
function murtadd_schema_add_mentions( $node, $mentions ) {
	if ( ! $mentions ) {
		return $node;
	}
	$node['mentions'] = array_merge( isset( $node['mentions'] ) ? (array) $node['mentions'] : array(), $mentions );
	return $node;
}

/**
 * Complete the main nodes and add the frame, scripture, glossary and image
 * nodes. Called by murtadd_structured_data_node() with the main node(s) and
 * the BreadcrumbList and FAQPage, if present.
 *
 * @param array[] $nodes Graph nodes so far, without @context.
 * @return array[]
 */
function murtadd_schema_complete_graph( $nodes ) {
	$has_breadcrumb = false;
	foreach ( $nodes as $node ) {
		if ( 'BreadcrumbList' === ( $node['@type'] ?? '' ) ) {
			$has_breadcrumb = true;
		}
	}

	$image   = murtadd_primary_image_node();
	$terms   = murtadd_glossary_term_nodes();
	$extras  = array();
	$post_id = is_singular() ? (int) get_queried_object_id() : 0;

	// Scripture: the rail verse on doubt and rebuttal singles, the blog page's
	// verse box (home.php), and verses marked in an entry's content.
	$rail_verses = array();
	if ( murtadd_schema_single( array( 'murtadd_doubt', 'murtadd_rebuttal' ) ) ) {
		$verse = murtadd_rail_verse();
		if ( $verse ) {
			$rail_verses = murtadd_quran_quotation_nodes( $verse['text'], $verse['ref'], 'rail-verse' );
		}
	}
	$verses = $rail_verses;
	if ( function_exists( 'is_home' ) && is_home() && ! is_front_page() ) {
		$verses = array_merge(
			$verses,
			murtadd_quran_quotation_nodes( 'الْحَقُّ مِن رَّبِّكَ فَلَا تَكُونَنَّ مِنَ الْمُمْتَرِينَ', 'Qur’an 2:147', 'verse-2-147', __( '“The truth is from your Lord — so never be among the doubters.” — Qur’an 2:147', 'murtadd' ) )
		);
	}
	$content_verses = $post_id ? murtadd_content_verse_nodes( (string) get_post_field( 'post_content', $post_id ) ) : array();
	$verses         = array_merge( $verses, $content_verses );

	$glossary_refs = array();
	foreach ( $terms as $t ) {
		$glossary_refs[] = array( '@id' => $t['@id'] );
	}
	$verse_refs = array();
	foreach ( $content_verses as $v ) {
		$verse_refs[] = array( '@id' => $v['@id'] );
	}

	foreach ( $nodes as $i => $node ) {
		$type = $node['@type'] ?? '';

		if ( 'QAPage' === $type ) {
			$node = murtadd_schema_fill( $node, murtadd_webpage_frame( $has_breadcrumb, (bool) $image ) );
			$about = murtadd_schema_term_things( 'murtadd_topic' );
			if ( $about ) {
				$node['about'] = $about;
			}
			$node = murtadd_schema_add_mentions( $node, array_merge( $glossary_refs, $verse_refs ) );
			$deep = $post_id ? (string) get_post_meta( $post_id, '_murtadd_external_deep_link', true ) : '';
			if ( '' !== $deep ) {
				$node['citation'] = array(
					array(
						'@type' => 'CreativeWork',
						'url'   => esc_url_raw( $deep ),
					),
				);
			}
			// Only a published rebuttal, as the visible link (murtadd_related_link()).
			$rebuttal = $post_id ? murtadd_related_link( '_murtadd_related_rebuttal', $post_id ) : 0;
			if ( $rebuttal && get_permalink( $rebuttal ) ) {
				$node['significantLink'] = array( (string) get_permalink( $rebuttal ) );
			}
		} elseif ( 'FAQPage' === $type && is_page( 'faq' ) ) {
			$node = murtadd_schema_fill( $node, murtadd_webpage_frame( $has_breadcrumb, false ) );
		} elseif ( in_array( $type, array( 'Article', 'BlogPosting' ), true ) && $post_id ) {
			$node = murtadd_schema_fill( $node, murtadd_article_details( $post_id ) );
			$node = murtadd_schema_add_mentions( $node, array_merge( $glossary_refs, $verse_refs ) );
			$topic = murtadd_schema_term_things( 'murtadd_topic' );
			if ( $topic ) {
				$node['articleSection'] = $topic[0]['name'];
			}
			if ( $image ) {
				$node['image'] = array_merge( array( array( '@id' => $image['@id'] ) ), (array) ( $node['image'] ?? array() ) );
			}
		}
		$nodes[ $i ] = $node;
	}

	$page = murtadd_webpage_node( $has_breadcrumb, (bool) $image );
	if ( $page ) {
		$extras[] = $page;
	}
	if ( $image ) {
		$extras[] = $image;
	}
	$extras[] = murtadd_sidebar_node();
	$rail     = murtadd_rail_node( $rail_verses );
	if ( $rail ) {
		$extras[] = $rail;
	}
	$extras[] = murtadd_footer_node();
	if ( $verses ) {
		$extras[] = murtadd_quran_book_node();
		foreach ( $verses as $v ) {
			$extras[] = $v;
		}
	}
	$set = murtadd_glossary_set_node( $terms );
	if ( $set ) {
		$extras[] = $set;
	}

	return array_merge( $nodes, $extras );
}
