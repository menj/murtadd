<?php
/**
 * Contextual links: in the running text of a single entry, the first mention
 * of a known phrase links to the inner page that treats it, on this site or
 * on one of the six sister sites.
 *
 * The phrases live in inc/data/network-links.json, a shared file kept
 * byte-for-byte identical across all seven themes. Entries owned by this site
 * resolve through home_url(), so staging links stay on staging; sister entries
 * resolve against the map's own `sites` table. Extra lines from Appearance →
 * Murtadd → Contextual links are tried before the map, so they win.
 *
 * Links are added as the text renders. Stored content is never touched.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/** This theme's key in the shared map's `sites` table. */
function murtadd_ctx_site_key() {
	return 'murtadd';
}

/** Defaults for the murtadd_contextual_links option. */
function murtadd_ctx_defaults() {
	return array(
		'own_enabled'    => 1,
		'sister_enabled' => 1,
		'own_max'        => 4,
		'sister_max'     => 3,
		'extra'          => '',
		'excluded'       => '',
	);
}

/** Saved settings over the defaults. */
function murtadd_ctx_settings() {
	$saved = get_option( 'murtadd_contextual_links', array() );
	return array_merge( murtadd_ctx_defaults(), is_array( $saved ) ? $saved : array() );
}

/** The shared map, decoded once. */
function murtadd_ctx_network() {
	static $network = null;
	if ( null === $network ) {
		$raw     = file_get_contents( get_stylesheet_directory() . '/inc/data/network-links.json' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
		$network = json_decode( (string) $raw, true );
		if ( ! is_array( $network ) || empty( $network['sites'] ) || empty( $network['links'] ) ) {
			$network = array( 'sites' => array(), 'links' => array() );
		}
	}
	return $network;
}

/** Lower-case a matched phrase into its lookup key: entities decoded, curly apostrophes straightened. */
function murtadd_ctx_key( $text ) {
	$text = html_entity_decode( (string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$text = str_replace( array( '’', '‘', 'ʼ' ), "'", $text );
	// A non-breaking space (decoded from &nbsp; or &#160; above) is a space.
	$text = preg_replace( '/[\s\x{00A0}]+/u', ' ', $text ) ?? $text;
	return function_exists( 'mb_strtolower' ) ? mb_strtolower( $text, 'UTF-8' ) : strtolower( $text );
}

/**
 * The entry a matched phrase leads to. Without mbstring the key lower-cases
 * ASCII only, so a match in non-ASCII capitals ("IBN ISḤĀQ") misses its key;
 * the phrases are then compared one by one, without regard to capitals.
 *
 * @param array  $index Key to entry, from murtadd_ctx_compile().
 * @param string $text  Matched text.
 * @return array|null
 */
function murtadd_ctx_lookup( array $index, $text ) {
	$key = murtadd_ctx_key( $text );
	if ( isset( $index[ $key ] ) ) {
		return $index[ $key ];
	}
	foreach ( $index as $phrase_key => $entry ) {
		if ( 1 === preg_match( '/^' . preg_quote( $phrase_key, '/' ) . '$/iu', $key ) ) {
			return $entry;
		}
	}
	return null;
}

/**
 * Split alternatives into patterns that each compile. One pattern of every
 * phrase is the fast path; past the PCRE size limit (about 58 KB of pattern,
 * reachable through extra phrases on the tab) it would fail to compile, so it
 * is halved until each part compiles. A single phrase that cannot compile is
 * left out.
 *
 * @param string[] $alts Escaped phrase alternatives, longest first.
 * @return string[]
 */
function murtadd_ctx_patterns( array $alts ) {
	if ( ! $alts ) {
		return array();
	}
	$pattern = '/(?<![\p{L}\p{N}])(?:' . implode( '|', $alts ) . ')(?![\p{L}\p{N}])/iu';
	if ( false !== @preg_match( $pattern, '' ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors -- a failed compile is the expected signal.
		return array( $pattern );
	}
	if ( 1 === count( $alts ) ) {
		return array();
	}
	$half = (int) ceil( count( $alts ) / 2 );
	return array_merge( murtadd_ctx_patterns( array_slice( $alts, 0, $half ) ), murtadd_ctx_patterns( array_slice( $alts, $half ) ) );
}

/**
 * Every phrase match in a run of text, left to right, never overlapping; at
 * the same offset the longer match wins, as in one longest-first pattern. A
 * pattern that fails on the text (malformed UTF-8) contributes nothing.
 *
 * @param string[] $patterns From murtadd_ctx_patterns().
 * @param string   $text     Plain text between tags.
 * @return array<int,array{0:string,1:int}> Matched text and byte offset.
 */
function murtadd_ctx_matches( array $patterns, $text ) {
	$out = array();
	$pos = 0;
	while ( null !== ( $best = murtadd_ctx_next( $patterns, $text, $pos ) ) ) {
		$out[] = $best;
		$pos   = $best[1] + strlen( $best[0] );
	}
	return $out;
}

/**
 * The first phrase match at or after a byte offset, the longest where several
 * start there. Split patterns are searched together from the offset, as one
 * pattern would be; a pattern that fails on the text contributes nothing.
 *
 * @param string[] $patterns From murtadd_ctx_patterns().
 * @param string   $text     Plain text between tags.
 * @param int      $from     Byte offset, on a character boundary.
 * @return array{0:string,1:int}|null Matched text and byte offset.
 */
function murtadd_ctx_next( array $patterns, $text, $from ) {
	$best = null;
	foreach ( $patterns as $pattern ) {
		$m = array();
		if ( 1 === preg_match( $pattern, $text, $m, PREG_OFFSET_CAPTURE, $from ) ) {
			$hit = $m[0];
			if ( null === $best || $hit[1] < $best[1] || ( $hit[1] === $best[1] && strlen( $hit[0] ) > strlen( $best[0] ) ) ) {
				$best = $hit;
			}
		}
	}
	return null === $best || '' === $best[0] ? null : $best;
}

/**
 * Parse one admin line, "phrase | /path/" or "phrase | https://sister/path/".
 *
 * A full URL is accepted only on a host in the map's `sites` table; this
 * site's own host is rewritten through home_url(). Anything else is dropped.
 *
 * @return array{phrase:string,url:string,own:bool}|null
 */
function murtadd_ctx_parse_line( $line ) {
	$parts = array_map( 'trim', explode( '|', (string) $line, 2 ) );
	if ( count( $parts ) < 2 || '' === $parts[0] || '' === $parts[1] ) {
		return null;
	}
	list( $phrase, $target ) = $parts;
	if ( '/' === $target[0] && ( ! isset( $target[1] ) || '/' !== $target[1] ) ) {
		return array( 'phrase' => $phrase, 'url' => home_url( $target ), 'own' => true );
	}
	$host = strtolower( (string) wp_parse_url( $target, PHP_URL_HOST ) );
	if ( ! $host || ! preg_match( '#^https?://#i', $target ) ) {
		return null;
	}
	foreach ( murtadd_ctx_network()['sites'] as $key => $base ) {
		if ( strtolower( (string) wp_parse_url( $base, PHP_URL_HOST ) ) !== $host ) {
			continue;
		}
		if ( murtadd_ctx_site_key() === $key ) {
			$path = (string) wp_parse_url( $target, PHP_URL_PATH );
			return array( 'phrase' => $phrase, 'url' => home_url( '' !== $path ? $path : '/' ), 'own' => true );
		}
		return array( 'phrase' => $phrase, 'url' => $target, 'own' => false );
	}
	return null;
}

/**
 * Every phrase with its address, admin lines first, then the shared map.
 *
 * @return array<int,array{phrase:string,url:string,own:bool}>
 */
function murtadd_ctx_entries() {
	$settings = murtadd_ctx_settings();
	$network  = murtadd_ctx_network();
	$own_key  = murtadd_ctx_site_key();
	$entries  = array();

	foreach ( preg_split( '/\r\n|\r|\n/', (string) $settings['extra'] ) as $line ) {
		$entry = murtadd_ctx_parse_line( $line );
		if ( $entry ) {
			$entries[] = $entry;
		}
	}
	foreach ( $network['links'] as $link ) {
		if ( empty( $link['site'] ) || empty( $link['path'] ) || empty( $link['phrases'] ) || ! isset( $network['sites'][ $link['site'] ] ) ) {
			continue;
		}
		$own = $own_key === $link['site'];
		$url = $own ? home_url( $link['path'] ) : rtrim( $network['sites'][ $link['site'] ], '/' ) . $link['path'];
		foreach ( (array) $link['phrases'] as $phrase ) {
			$entries[] = array( 'phrase' => (string) $phrase, 'url' => $url, 'own' => $own );
		}
	}
	return (array) apply_filters( 'murtadd_contextual_links_map', $entries );
}

/**
 * The compiled matcher: one longest-first pattern and a phrase-key index.
 * The first entry for a key wins, so admin lines override the map.
 *
 * @param array $entries From murtadd_ctx_entries().
 * @return array{pattern:string,index:array}|null
 */
function murtadd_ctx_compile( array $entries ) {
	$index = array();
	foreach ( $entries as $entry ) {
		$key = murtadd_ctx_key( $entry['phrase'] );
		if ( '' !== $key && ! isset( $index[ $key ] ) ) {
			$index[ $key ] = $entry;
		}
	}
	if ( ! $index ) {
		return null;
	}
	$phrases = array_column( $index, 'phrase' );
	usort(
		$phrases,
		static function ( $a, $b ) {
			return ( function_exists( 'mb_strlen' ) ? mb_strlen( $b ) - mb_strlen( $a ) : strlen( $b ) - strlen( $a ) );
		}
	);
	// Text reaching the_content has been texturised, so an apostrophe may
	// arrive straight, curly, or as an entity.
	$apostrophe = "(?:'|’|‘|ʼ|&\\#8217;|&\\#8216;|&\\#039;|&\\#39;|&rsquo;|&lsquo;|&apos;)";
	// A space may arrive as a non-breaking space, written or as an entity.
	$space = '(?:\s|&nbsp;|&\#160;|\x{00A0})+';
	$alts  = array_map(
		static function ( $p ) use ( $apostrophe, $space ) {
			$alt = str_replace( array( "'", '’', '‘', 'ʼ' ), $apostrophe, preg_quote( $p, '/' ) );
			return (string) preg_replace( '/ +/', $space, $alt );
		},
		$phrases
	);
	$patterns = murtadd_ctx_patterns( $alts );
	if ( ! $patterns ) {
		return null;
	}
	return array(
		'patterns' => $patterns,
		'index'    => $index,
	);
}

/** Scheme-less, query-less, trailing-slash-less form of a URL, for "is this the current page". */
function murtadd_ctx_normalise_url( $url ) {
	$url = strtolower( preg_replace( '#[?\#].*$#', '', (string) $url ) );
	return rtrim( preg_replace( '#^https?://#', '', $url ), '/' );
}

/**
 * Link the first mention of each phrase in the running text.
 *
 * Pure: everything it needs arrives in $args, so the rules are testable
 * without WordPress.
 *
 * @param string $html  Rendered HTML.
 * @param array  $entries From murtadd_ctx_entries().
 * @param array  $args  own_enabled, sister_enabled, own_max, sister_max, here (current URL).
 * @return string
 */
function murtadd_ctx_apply( $html, array $entries, array $args ) {
	$args = array_merge( murtadd_ctx_defaults(), array( 'here' => '' ), $args );
	$caps = array(
		'own'    => $args['own_enabled'] ? max( 0, min( 15, (int) $args['own_max'] ) ) : 0,
		'sister' => $args['sister_enabled'] ? max( 0, min( 10, (int) $args['sister_max'] ) ) : 0,
	);
	if ( '' === (string) $html || false === strpos( $html, '<' ) || ! ( $caps['own'] + $caps['sister'] ) ) {
		return $html;
	}

	static $compiled = array();
	$sig = md5( (string) wp_json_encode( $entries ) );
	if ( ! array_key_exists( $sig, $compiled ) ) {
		$compiled[ $sig ] = murtadd_ctx_compile( $entries );
	}
	$matcher = $compiled[ $sig ];
	if ( ! $matcher ) {
		return $html;
	}

	$skip_tags  = array( 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'q', 'figure', 'figcaption', 'cite', 'code', 'pre', 'sup', 'sub', 'nav', 'table', 'button', 'label', 'select', 'textarea', 'summary', 'script', 'style' );
	$skip_attr  = '/\blang=(["\']?)(?:ar|he|el|syr)(?:-[a-z0-9]+)?\1(?=[\s>\/])|\bclass=(["\'])[^"\']*\b(?:murtadd-ayah|murtadd-ayah-tr|murtadd-ayah-box|murtadd-verse|murtadd-sources-panel|murtadd-sources-list|murtadd-disclaimer|footnotes?|wp-block-footnotes|toc)\b/i';
	$block_tags = array( 'p', 'li', 'dd' );
	$void_tags  = array( 'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'source', 'track', 'wbr' );

	$here   = murtadd_ctx_normalise_url( $args['here'] );
	$done   = array();
	$made   = array( 'own' => 0, 'sister' => 0 );
	$open   = array(); // Every open element: array( tag, skip ).
	$skips  = 0;       // How many open elements forbid links.
	$blocks = array(); // Per open <p>/<li>/<dd>: has it a link yet.

	// A tag ends at the first ">" outside quotes, so a ">" inside an attribute
	// value never turns the rest of the value into linkable text.
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
					// Close the innermost matching element and anything left open inside it.
					for ( $i = count( $open ) - 1; $i >= 0; $i-- ) {
						if ( $open[ $i ][0] !== $tag ) {
							continue;
						}
						while ( count( $open ) > $i ) {
							list( $closed, $was_skip ) = array_pop( $open );
							$skips -= $was_skip ? 1 : 0;
							if ( in_array( $closed, $block_tags, true ) ) {
								array_pop( $blocks );
							}
						}
						break;
					}
				} elseif ( ! in_array( $tag, $void_tags, true ) && '/>' !== substr( $piece, -2 ) ) {
					$skip    = in_array( $tag, $skip_tags, true ) || preg_match( $skip_attr, $piece );
					$open[]  = array( $tag, (bool) $skip );
					$skips  += $skip ? 1 : 0;
					if ( in_array( $tag, $block_tags, true ) ) {
						$blocks[] = false;
					}
				}
			}
			$out .= $piece;
			continue;
		}
		if ( $skips || ( $made['own'] >= $caps['own'] && $made['sister'] >= $caps['sister'] ) || '' === trim( $piece ) ) {
			$out .= $piece;
			continue;
		}
		// The text is rebuilt from the matches; with no match (or a pattern
		// that fails on malformed UTF-8) the piece is kept as it came.
		// A match passed over resumes the search one character on, so a phrase
		// running past its end ("Paul of Tarsus" after "Peter and Paul") can
		// still link; one wholly inside it ("Barnabas" in "Gospel of Barnabas")
		// stays part of that name.
		$at     = 0;
		$pos    = 0;
		$shadow = 0;
		while ( null !== ( $match = murtadd_ctx_next( $matcher['patterns'], $piece, $pos ) ) ) {
			list( $text, $offset ) = $match;
			$end   = $offset + strlen( $text );
			$entry = murtadd_ctx_lookup( $matcher['index'], $text );
			$kind  = $entry && $entry['own'] ? 'own' : 'sister';
			if ( $end <= $shadow
				|| ! $entry
				|| isset( $done[ $entry['url'] ] )
				|| $made[ $kind ] >= $caps[ $kind ]
				|| ( $blocks && in_array( true, $blocks, true ) )
				|| ( '' !== $here && murtadd_ctx_normalise_url( $entry['url'] ) === $here ) ) {
				$shadow = max( $shadow, $end );
				$byte   = ord( $piece[ $offset ] );
				$pos    = $offset + ( $byte >= 0xF0 ? 4 : ( $byte >= 0xE0 ? 3 : ( $byte >= 0xC0 ? 2 : 1 ) ) );
				continue;
			}
			$out .= substr( $piece, $at, $offset - $at );
			$at   = $end;
			$pos  = $end;
			$done[ $entry['url'] ] = true;
			++$made[ $kind ];
			$blocks = array_fill( 0, count( $blocks ), true );
			if ( $entry['own'] ) {
				$out .= '<a class="murtadd-ctx-link" href="' . esc_url( $entry['url'] ) . '">' . $text . '</a>';
			} else {
				$out .= '<a class="murtadd-ctx-link murtadd-ctx-link--sister" href="' . esc_url( $entry['url'] ) . '" rel="noopener">' . $text . '</a>';
			}
		}
		$out .= substr( $piece, $at );
	}
	return $out;
}

/** True when the slug is on the excluded list (one slug per line). */
function murtadd_ctx_is_excluded( $slug, $excluded ) {
	$list = array_filter( array_map( 'sanitize_title', preg_split( '/\r\n|\r|\n/', (string) $excluded ) ) );
	return '' !== (string) $slug && in_array( (string) $slug, $list, true );
}

/**
 * Whether an entry gets contextual links at all.
 *
 * Emotional and Identity doubts get none. They are written care-first and left
 * deliberately unlinked: a grief is not a claim awaiting refutation, and a link
 * to a rebuttal or another site in the middle of one reads as exactly that.
 */
function murtadd_ctx_post_allowed( $post_id ) {
	$post_id = (int) $post_id;
	$allowed = $post_id > 0;
	if ( $allowed && 'murtadd_doubt' === get_post_type( $post_id ) && murtadd_doubt_needs_notice( $post_id ) ) {
		$allowed = false;
	}
	if ( $allowed && murtadd_ctx_is_excluded( get_post_field( 'post_name', $post_id ), murtadd_ctx_settings()['excluded'] ) ) {
		$allowed = false;
	}
	return (bool) apply_filters( 'murtadd_contextual_links_enabled', $allowed, get_post( $post_id ) );
}

/** The request is a single entry's own view, not the admin, a feed, an excerpt or the front page. */
function murtadd_ctx_request_allowed() {
	return ! is_admin() && ! is_feed() && is_singular() && is_main_query() && ! is_front_page() && ! doing_filter( 'get_the_excerpt' );
}

/**
 * Add contextual links to HTML rendered for the queried entry. Shared by the
 * the_content filter and the meta-field render paths (doubt short response,
 * fatwa position summary) that never pass through the_content.
 */
function murtadd_ctx_render( $html, $post_id = 0 ) {
	$post_id = (int) ( $post_id ?: get_the_ID() );
	if ( ! murtadd_ctx_request_allowed() || get_queried_object_id() !== $post_id || ! murtadd_ctx_post_allowed( $post_id ) ) {
		return $html;
	}
	$settings = murtadd_ctx_settings();
	return murtadd_ctx_apply( $html, murtadd_ctx_entries(), array_merge( $settings, array( 'here' => get_permalink( $post_id ) ) ) );
}

/** the_content filter. */
function murtadd_ctx_filter( $content ) {
	return murtadd_ctx_render( $content );
}
// After wpautop (10) and shortcodes (11), so the paragraphs the per-paragraph
// cap counts exist and shortcode output is checked like any other text. The
// theme runs no other the_content filters.
add_filter( 'the_content', 'murtadd_ctx_filter', 20 );
