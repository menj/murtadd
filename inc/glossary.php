<?php
/**
 * Glossary of terms.
 *
 * The entries use Arabic and Malay terms (irtidad, nafaqah, 'ayb, tudung) that
 * a reader may not know. The first mention of each on a page gets a short
 * definition and its source, shown on hover, focus or tap. The definitions
 * live in inc/data/glossary.json; each says what the word means, never what a
 * ruling is.
 *
 * Rules, pinned in tests/GlossaryTest.php:
 * - one definition per term per page, across every render path of the entry;
 * - never inside a link, heading, quotation, verse, the sources panel, the
 *   disclaimer, a button or any element the contextual links skip;
 * - a mention the text glosses itself ("hudud (fixed prescribed penalties)")
 *   is left as written, and a later mention may carry the definition;
 * - never on Emotional or Identity doubts, which are written care-first.
 *
 * @package Murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The terms, keyed by slug.
 *
 * @return array<string,array{slug:string,term:string,aliases:string[],definition:string,source:string}>
 */
function murtadd_glossary_terms() {
	static $terms = null;
	if ( null === $terms ) {
		$file  = get_stylesheet_directory() . '/inc/data/glossary.json';
		$data  = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
		$terms = array();
		foreach ( (array) ( $data['terms'] ?? array() ) as $t ) {
			if ( empty( $t['slug'] ) || empty( $t['term'] ) || empty( $t['definition'] ) ) {
				continue;
			}
			$terms[ $t['slug'] ] = array(
				'slug'       => (string) $t['slug'],
				'term'       => (string) $t['term'],
				'aliases'    => array_map( 'strval', (array) ( $t['aliases'] ?? array() ) ),
				'definition' => (string) $t['definition'],
				'source'     => (string) ( $t['source'] ?? '' ),
			);
		}
	}
	return (array) apply_filters( 'murtadd_glossary_terms', $terms );
}

/**
 * A spelling reduced for lookup: every apostrophe or ayn mark becomes "'",
 * any run of spaces (non-breaking ones and line breaks included, as the
 * matcher allows) becomes one space, and capitals go.
 *
 * @param string $text Term or matched text.
 * @return string
 */
function murtadd_glossary_key( $text ) {
	$text = str_replace( array( '&#8217;', '&#8216;', '&#039;', '&#39;', '&rsquo;', '&lsquo;', '&apos;', "\u{2019}", "\u{2018}", "\u{02BC}", "\u{02BF}" ), "'", (string) $text );
	$text = (string) preg_replace( '/(?:\s|&nbsp;|&#160;|\xC2\xA0)+/', ' ', $text );
	return function_exists( 'mb_strtolower' ) ? mb_strtolower( $text, 'UTF-8' ) : strtolower( $text );
}

/**
 * The compiled matcher: one pattern of every spelling, longest first, and
 * the spelling (as a lookup key) to its term slug.
 *
 * @return array{pattern:string,index:array<string,string>}|null
 */
function murtadd_glossary_matcher() {
	static $cache = array();
	$terms = murtadd_glossary_terms();
	$sig   = md5( (string) wp_json_encode( array_keys( $terms ) ) );
	if ( array_key_exists( $sig, $cache ) ) {
		return $cache[ $sig ];
	}
	$index    = array();
	$spelling = array();
	foreach ( $terms as $slug => $t ) {
		foreach ( array_merge( array( $t['term'] ), $t['aliases'] ) as $s ) {
			$key = murtadd_glossary_key( $s );
			if ( '' !== $key && ! isset( $index[ $key ] ) ) {
				$index[ $key ] = $slug;
				$spelling[]    = $key;
			}
		}
	}
	if ( ! $spelling ) {
		return $cache[ $sig ] = null;
	}
	usort(
		$spelling,
		static function ( $a, $b ) {
			return strlen( $b ) <=> strlen( $a );
		}
	);
	$apostrophe = "(?:'|\u{2019}|\u{2018}|\u{02BC}|\u{02BF}|&\#8217;|&\#8216;|&\#0?39;|&rsquo;|&lsquo;|&apos;)";
	$alts       = array_map(
		static function ( $s ) use ( $apostrophe ) {
			return str_replace( array( "'", ' ' ), array( $apostrophe, '(?:\s|&nbsp;|&\#160;|\x{00A0})+' ), preg_quote( $s, '/' ) );
		},
		$spelling
	);
	// The apostrophe of 'ayb is part of the term, so the left edge allows one
	// before a letter; a letter or digit may never touch the match.
	$pattern = '/(?<![\p{L}\p{N}])(?:' . implode( '|', $alts ) . ')(?![\p{L}\p{N}])/iu';
	if ( false === @preg_match( $pattern, '' ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- probing whether the pattern compiles.
		return $cache[ $sig ] = null;
	}
	return $cache[ $sig ] = array(
		'pattern' => $pattern,
		'index'   => $index,
	);
}

/** Whether the switch on the Content tab is on (default on). */
function murtadd_glossary_setting_on() {
	$o = get_option( 'murtadd_content_display', array() );
	return ! is_array( $o ) || ! isset( $o['glossary_terms'] ) || ! empty( $o['glossary_terms'] );
}

/**
 * Whether an entry gets definitions. Emotional and Identity doubts get none,
 * for the reason they get no contextual links.
 *
 * @param int $post_id Post.
 * @return bool
 */
function murtadd_glossary_post_allowed( $post_id ) {
	$post_id = (int) $post_id;
	$allowed = $post_id > 0 && murtadd_glossary_setting_on();
	if ( $allowed && 'murtadd_doubt' === get_post_type( $post_id ) && murtadd_doubt_needs_notice( $post_id ) ) {
		$allowed = false;
	}
	return (bool) apply_filters( 'murtadd_glossary_enabled', $allowed, get_post( $post_id ) );
}

/**
 * Add definitions to HTML. $seen holds the terms already defined on the page
 * and is updated, so a term is defined once whichever path renders it first.
 *
 * @param string               $html   Markup.
 * @param array<string,bool>   $seen   Term slugs already defined on the page.
 * @param string               $prefix Prefix for element ids.
 * @return string
 */
function murtadd_glossary_apply( $html, array &$seen, $prefix = 'murtadd-term' ) {
	if ( '' === (string) $html || false === strpos( $html, '<' ) ) {
		return $html;
	}
	$matcher = murtadd_glossary_matcher();
	if ( ! $matcher ) {
		return $html;
	}
	$terms = murtadd_glossary_terms();

	$skip_tags = array( 'a', 'abbr', 'dfn', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'q', 'figure', 'figcaption', 'cite', 'code', 'pre', 'sup', 'sub', 'nav', 'table', 'button', 'label', 'select', 'textarea', 'summary', 'script', 'style' );
	$skip_attr = '/\blang=(["\']?)(?:ar|he|el|syr)(?:-[a-z0-9]+)?\1(?=[\s>\/])|\bclass=(["\'])[^"\']*\b(?:murtadd-ayah|murtadd-ayah-tr|murtadd-ayah-box|murtadd-verse|murtadd-sources-panel|murtadd-sources-list|murtadd-disclaimer|murtadd-term|footnotes?|wp-block-footnotes|toc)\b/i';
	$void_tags = array( 'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'source', 'track', 'wbr' );

	static $counter = 0;
	$open   = array();
	$skips  = 0;
	$pieces = preg_split( '/(<!--.*?-->|<(?:[^>"\']|"[^"]*"|\'[^\']*\')+>)/s', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	if ( false === $pieces ) {
		return $html;
	}
	$out = '';
	foreach ( $pieces as $piece ) {
		if ( '' === $piece ) {
			continue;
		}
		if ( '<' === $piece[0] ) {
			if ( preg_match( '#^<(/?)([a-zA-Z][a-zA-Z0-9-]*)#', $piece, $m ) ) {
				$tag = strtolower( $m[2] );
				if ( '/' === $m[1] ) {
					for ( $i = count( $open ) - 1; $i >= 0; $i-- ) {
						if ( $open[ $i ][0] !== $tag ) {
							continue;
						}
						while ( count( $open ) > $i ) {
							$skips -= array_pop( $open )[1] ? 1 : 0;
						}
						break;
					}
				} elseif ( ! in_array( $tag, $void_tags, true ) && '/>' !== substr( $piece, -2 ) ) {
					$skip    = in_array( $tag, $skip_tags, true ) || preg_match( $skip_attr, $piece );
					$open[]  = array( $tag, (bool) $skip );
					$skips  += $skip ? 1 : 0;
				}
			}
			$out .= $piece;
			continue;
		}
		if ( $skips || '' === trim( $piece ) ) {
			$out .= $piece;
			continue;
		}
		$rebuilt = preg_replace_callback(
			$matcher['pattern'],
			static function ( $m ) use ( &$seen, &$counter, $matcher, $terms, $prefix, $piece ) {
				$text = $m[0][0];
				$slug = $matcher['index'][ murtadd_glossary_key( $text ) ] ?? '';
				if ( '' === $slug || isset( $seen[ $slug ] ) || ! isset( $terms[ $slug ] ) ) {
					return $text;
				}
				// The text glosses this mention itself: "hudud (fixed penalties)".
				if ( preg_match( '/\G\s*\(/', $piece, $unused, 0, $m[0][1] + strlen( $text ) ) ) {
					return $text;
				}
				$seen[ $slug ] = true;
				++$counter;
				$t  = $terms[ $slug ];
				$id = $prefix . '-' . $counter;
				$src = '' !== $t['source'] ? '<span class="murtadd-term__src">' . esc_html( $t['source'] ) . '</span>' : '';
				return '<span class="murtadd-term">'
					. '<button type="button" class="murtadd-term__word" aria-describedby="' . esc_attr( $id ) . '" aria-expanded="false">' . $text . '</button>'
					. '<span class="murtadd-term__tip" id="' . esc_attr( $id ) . '" role="tooltip" hidden>'
					. '<span class="murtadd-term__def">' . esc_html( $t['definition'] ) . '</span>' . $src
					. '</span></span>';
			},
			$piece,
			-1,
			$unused_count,
			PREG_OFFSET_CAPTURE
		);
		$out .= null === $rebuilt ? $piece : $rebuilt;
	}
	return $out;
}

/**
 * Definitions for HTML rendered for the queried entry: the_content and the
 * meta-field paths (doubt short response, fatwa summary). One set of terms
 * per entry, so a term defined in the short response is not defined again
 * further down.
 *
 * @param string $html    Markup.
 * @param int    $post_id Post.
 * @return string
 */
function murtadd_glossary_render( $html, $post_id = 0 ) {
	static $seen = array();
	$post_id = (int) ( $post_id ?: get_the_ID() );
	if ( ! murtadd_ctx_request_allowed() || get_queried_object_id() !== $post_id || ! murtadd_glossary_post_allowed( $post_id ) ) {
		return $html;
	}
	if ( ! isset( $seen[ $post_id ] ) ) {
		$seen[ $post_id ] = array();
	}
	$out = murtadd_glossary_apply( $html, $seen[ $post_id ], 'murtadd-term-' . $post_id );
	// A definition the head check could not foresee (a synced pattern, a
	// shortcode) still gets its stylesheet and script, printed in the footer.
	if ( $out !== $html ) {
		murtadd_glossary_enqueue_assets();
	}
	return $out;
}

/** the_content filter. */
function murtadd_glossary_filter( $content ) {
	return murtadd_glossary_render( $content );
}
// After the contextual links (20), so a phrase already linked keeps its link
// and never gets a definition inside it.
add_filter( 'the_content', 'murtadd_glossary_filter', 21 );

/**
 * Whether the queried entry mentions any term, so the stylesheet and script
 * load only where a definition can appear.
 *
 * @return bool
 */
function murtadd_glossary_page_has_terms() {
	$post_id = (int) get_queried_object_id();
	if ( ! murtadd_ctx_request_allowed() || ! murtadd_glossary_post_allowed( $post_id ) ) {
		return false;
	}
	$matcher = murtadd_glossary_matcher();
	if ( ! $matcher ) {
		return false;
	}
	$text = (string) get_post_field( 'post_content', $post_id )
		. ' ' . (string) get_post_meta( $post_id, '_murtadd_short_response', true )
		. ' ' . (string) get_post_meta( $post_id, '_murtadd_position_summary', true );
	return 1 === preg_match( $matcher['pattern'], $text );
}

/** The stylesheet and script a definition needs. */
function murtadd_glossary_enqueue_assets() {
	wp_enqueue_style( 'murtadd-glossary', get_stylesheet_directory_uri() . '/assets/css/glossary' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
	wp_enqueue_script( 'murtadd-glossary', get_stylesheet_directory_uri() . '/assets/js/glossary' . MURTADD_ASSET_SUFFIX . '.js', array(), MURTADD_VERSION, true );
}

/** Stylesheet and script in the head, on entries whose stored text mentions a term. */
function murtadd_glossary_enqueue() {
	if ( murtadd_glossary_page_has_terms() ) {
		murtadd_glossary_enqueue_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'murtadd_glossary_enqueue', 20 );
