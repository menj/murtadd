<?php
/**
 * The Journal post page: the pieces around a post's text, in reading order.
 *
 * single.php places them: the meta row and the dek in the header, then the
 * answer box ("In short"), the contents, the text with its headings given
 * ids, its tables made scrollable and an optional one-line next step, then
 * the sources, the end next step and related posts. The FAQ is the text's
 * own Details blocks in the FAQ style; only those feed the FAQPage, so the
 * data says nothing the page does not show.
 *
 * Journal posts only. Doubts are short by design and keep their own page;
 * rebuttals and guides keep theirs. No byline: the author is named in the
 * structured data only, as on every other page of the site. No piece here
 * sells anything: a next step is the next post, the sources or a question.
 *
 * The Content tab, card "Journal posts", switches the answer box, the
 * contents, the mid-article next step and related posts.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/** Post meta for the key points, one a line. */
const MURTADD_ARTICLE_POINTS_KEY = '_murtadd_takeaways';

/* ---------------------------------------------------------------------
 * Settings
 * ------------------------------------------------------------------ */

/**
 * One of the Journal post switches on the Content tab.
 *
 * @param string $key answer_box, contents, mid_next or related.
 * @return bool
 */
function murtadd_article_on( $key ) {
	$defaults = array(
		'answer_box' => 1,
		'contents'   => 1,
		'mid_next'   => 0,
		'related'    => 1,
	);
	if ( ! isset( $defaults[ $key ] ) ) {
		return false;
	}
	return (bool) (int) murtadd_option( 'murtadd_content_display', 'journal_' . $key, $defaults[ $key ] );
}

/**
 * Whether the request is a single Journal post.
 *
 * @return bool
 */
function murtadd_article_applies() {
	return is_singular( 'post' );
}

/* ---------------------------------------------------------------------
 * Headings and the contents
 * ------------------------------------------------------------------ */

/**
 * Plain text of a piece of markup, on one line.
 *
 * @param string $html Markup.
 * @return string
 */
function murtadd_article_text( $html ) {
	$text = html_entity_decode( wp_strip_all_tags( (string) $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	return trim( (string) preg_replace( '/\s+/u', ' ', $text ) );
}

/**
 * An id for a heading, made from its text and numbered when that id is taken.
 *
 * @param string $text Heading text.
 * @param array  $seen Ids taken so far, as keys; the new one is added.
 * @return string
 */
function murtadd_article_slug( $text, array &$seen ) {
	// sanitize_title() percent-encodes letters outside ASCII; the id keeps them.
	$base = rawurldecode( sanitize_title( $text ) );
	$base = '' !== $base ? $base : 'section';
	$slug = $base;
	for ( $n = 2; isset( $seen[ $slug ] ); $n++ ) {
		$slug = $base . '-' . $n;
	}
	$seen[ $slug ] = true;
	return $slug;
}

/**
 * The ids the Journal post page gives its own pieces.
 *
 * @return string[]
 */
function murtadd_article_reserved_ids() {
	return array( 'murtadd-sources', 'murtadd-sources-title', 'murtadd-toc-title', 'murtadd-answer-title', 'murtadd-next-title', 'murtadd-related-title' );
}

/**
 * The level-two headings of some markup, in order: the author's own id where
 * a heading has one, else one made from its text. The author's ids are
 * reserved first, so a made id never repeats one.
 *
 * @param string $html Stored or rendered text.
 * @return array<int,array{0:string,1:string,2:bool}> Id, text, whether the author set the id.
 */
function murtadd_article_heading_list( $html ) {
	if ( ! preg_match_all( '#<h2(\s[^>]*)?>(.*?)</h2>#is', (string) $html, $found, PREG_SET_ORDER ) ) {
		return array();
	}
	// The ids of the page's own pieces are taken, so no heading can claim one.
	$seen = array_fill_keys( murtadd_article_reserved_ids(), true );
	$own  = array();
	foreach ( $found as $i => $m ) {
		if ( preg_match( '/\sid\s*=\s*["\']([^"\']+)["\']/i', (string) ( $m[1] ?? '' ), $idm ) ) {
			$own[ $i ]       = $idm[1];
			$seen[ $idm[1] ] = true;
		}
	}
	$out = array();
	foreach ( $found as $i => $m ) {
		$text  = murtadd_article_text( $m[2] );
		$out[] = isset( $own[ $i ] ) ? array( $own[ $i ], $text, true ) : array( '' === $text ? '' : murtadd_article_slug( $text, $seen ), $text, false );
	}
	return $out;
}

/**
 * The headings the contents lists: id and text, for headings with text.
 *
 * @param string $html Stored text.
 * @return array<int,array{0:string,1:string}>
 */
function murtadd_article_headings( $html ) {
	$out = array();
	foreach ( murtadd_article_heading_list( $html ) as $h ) {
		if ( '' !== $h[1] ) {
			$out[] = array( $h[0], $h[1] );
		}
	}
	return $out;
}

/**
 * Give each level-two heading of the rendered text the id the contents
 * links to. A heading with its own id keeps it.
 *
 * @param string $html Rendered text.
 * @return string
 */
function murtadd_article_heading_ids( $html ) {
	$list = murtadd_article_heading_list( $html );
	$i    = 0;
	return (string) preg_replace_callback(
		'#<h2(\s[^>]*)?>(.*?)</h2>#is',
		static function ( $m ) use ( $list, &$i ) {
			$h = $list[ $i++ ] ?? array( '', '', true );
			if ( $h[2] || '' === $h[0] ) {
				return $m[0];
			}
			return '<h2 id="' . esc_attr( $h[0] ) . '"' . (string) ( $m[1] ?? '' ) . '>' . $m[2] . '</h2>';
		},
		(string) $html
	);
}

/**
 * The contents: a list of the post's sections, from three level-two
 * headings up. Built on the server from the stored text, so it shows
 * without JavaScript; longform.js marks the section being read.
 *
 * @param WP_Post|null $post Post; the current one when null.
 * @return string
 */
function murtadd_article_contents( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || ! murtadd_article_on( 'contents' ) ) {
		return '';
	}
	$headings = murtadd_article_headings( (string) $post->post_content );
	if ( count( $headings ) < 3 ) {
		return '';
	}
	$items = '';
	foreach ( $headings as $h ) {
		$items .= '<li><a href="#' . esc_attr( $h[0] ) . '">' . esc_html( $h[1] ) . '</a></li>';
	}
	return '<nav class="murtadd-toc murtadd-toc--inline" aria-labelledby="murtadd-toc-title"><h2 id="murtadd-toc-title" class="murtadd-toc-label">' . esc_html__( 'On this page', 'murtadd' ) . '</h2><ol>' . $items . '</ol></nav>';
}

/* ---------------------------------------------------------------------
 * The text: heading ids, tables, the mid-article next step
 * ------------------------------------------------------------------ */

/**
 * Make each table in the text scroll on its own on a narrow screen. A table
 * in a figure gets the figure as a labelled, focusable region, so a
 * keyboard can scroll it too; a bare table is wrapped in one. The label is
 * the caption, where there is one.
 *
 * @param string $html Rendered text.
 * @return string
 */
function murtadd_article_tables( $html ) {
	if ( false === stripos( (string) $html, '<table' ) ) {
		return (string) $html;
	}
	return (string) preg_replace_callback(
		'#<figure\b[^>]*>(?:(?!</figure>).)*?<table\b.*?</figure>|<table\b.*?</table>#is',
		static function ( $m ) {
			$html = $m[0];
			if ( false !== strpos( $html, 'role="region"' ) ) {
				return $html;
			}
			$label = '';
			if ( preg_match( '#<(?:figcaption|caption)[^>]*>(.*?)</(?:figcaption|caption)>#is', $html, $c ) ) {
				$label = murtadd_article_text( $c[1] );
			}
			$label = '' !== $label ? wp_html_excerpt( $label, 120, '…' ) : __( 'Table', 'murtadd' );
			$attrs = ' role="region" tabindex="0" aria-label="' . esc_attr( $label ) . '"';
			if ( 0 === stripos( $html, '<figure' ) ) {
				$html = (string) preg_replace( '#^<figure\b([^>]*)>#i', '<figure$1' . $attrs . '>', $html, 1 );
				if ( ! preg_match( '#^<figure\b[^>]*\sclass\s*=#i', $html ) ) {
					// A figure with no class of its own gets one.
					return (string) preg_replace( '#^<figure\b#i', '<figure class="murtadd-table"', $html, 1 );
				}
				return (string) preg_replace( '#^<figure\b(?![^>]*\bmurtadd-table\b)([^>]*?)class="([^"]*)"#i', '<figure$1class="$2 murtadd-table"', $html, 1 );
			}
			return '<div class="murtadd-table"' . $attrs . '>' . $html . '</div>';
		},
		(string) $html
	);
}

/**
 * Put the mid-article next step before the third level-two heading, when the
 * text has one.
 *
 * @param string $html  Rendered text.
 * @param string $aside The next step.
 * @return string
 */
function murtadd_article_insert_mid( $html, $aside ) {
	if ( '' === $aside || ! preg_match_all( '#<h2[\s>]#i', (string) $html, $found, PREG_OFFSET_CAPTURE ) || count( $found[0] ) < 3 ) {
		return (string) $html;
	}
	$at = (int) $found[0][2][1];
	return substr( $html, 0, $at ) . $aside . substr( $html, $at );
}

/**
 * The main text of a Journal post: heading ids, scrollable tables and, when
 * switched on, the one-line next step. Runs after the contextual links (10).
 *
 * @param string $content Rendered text.
 * @return string
 */
function murtadd_article_filter_content( $content ) {
	if ( ! murtadd_article_applies() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$content = murtadd_article_tables( murtadd_article_heading_ids( (string) $content ) );
	if ( murtadd_article_on( 'mid_next' ) ) {
		$content = murtadd_article_insert_mid( $content, murtadd_article_next_step( 'mid' ) );
	}
	return $content;
}
add_filter( 'the_content', 'murtadd_article_filter_content', 12 );

/* ---------------------------------------------------------------------
 * The header: meta row and dek
 * ------------------------------------------------------------------ */

/**
 * Whether the post was edited more than a day after it went up: the rule
 * murtadd_the_reviewed_date() uses for "Last reviewed".
 *
 * @param WP_Post|null $post Post; the current one when null.
 * @return bool
 */
function murtadd_article_was_reviewed( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}
	return (int) get_the_modified_date( 'U', $post ) > (int) get_the_date( 'U', $post ) + DAY_IN_SECONDS;
}

/**
 * The meta row: the date the post went up, "Last reviewed" once it has been
 * edited more than a day later (murtadd_the_reviewed_date()), and the reading
 * time. The date leads, as on every Journal view.
 *
 * @return void
 */
function murtadd_article_meta_row() {
	echo '<div class="murtadd-meta-row murtadd-journal-dateline">';
	printf( '<span><time datetime="%1$s">%2$s</time></span>', esc_attr( (string) get_the_date( 'c' ) ), esc_html( (string) get_the_date() ) );
	if ( murtadd_article_was_reviewed() ) {
		echo '<span aria-hidden="true">·</span><span>';
		murtadd_the_reviewed_date();
		echo '</span>';
	}
	echo '<span aria-hidden="true">·</span><span>' . esc_html( murtadd_read_time( get_the_content() ) ) . '</span>';
	echo '</div>';
}

/**
 * The dek: the post's excerpt, under the title, when the author wrote one.
 *
 * @return void
 */
function murtadd_article_dek() {
	$text = trim( (string) get_post_field( 'post_excerpt', get_the_ID() ) );
	if ( '' !== $text ) {
		echo '<p class="murtadd-dek">' . esc_html( $text ) . '</p>';
	}
}

/* ---------------------------------------------------------------------
 * The answer box
 * ------------------------------------------------------------------ */

/**
 * Lines of text, trimmed, blanks dropped.
 *
 * @param string $text One point a line.
 * @return string[]
 */
function murtadd_article_points( $text ) {
	return array_values( array_filter( array_map( 'trim', (array) preg_split( '/\r\n|\r|\n/', (string) $text ) ), 'strlen' ) );
}

/**
 * The key points of a post, from the Journal fields box.
 *
 * @param int $post_id Post.
 * @return string[]
 */
function murtadd_article_post_points( $post_id ) {
	return murtadd_article_points( (string) get_post_meta( $post_id, MURTADD_ARTICLE_POINTS_KEY, true ) );
}

/**
 * The answer box: "In short", the key points near the top. The dek above it
 * is the summary, so the box holds the points only, and shows only when the
 * post has some.
 *
 * @return string
 */
function murtadd_article_answer_box() {
	if ( ! murtadd_article_on( 'answer_box' ) ) {
		return '';
	}
	$points = murtadd_article_post_points( (int) get_the_ID() );
	if ( ! $points ) {
		return '';
	}
	$out = '<section class="murtadd-answer" aria-labelledby="murtadd-answer-title"><h2 id="murtadd-answer-title" class="murtadd-kicker murtadd-kicker--accent">' . esc_html__( 'In short', 'murtadd' ) . '</h2><ul class="murtadd-answer-points">';
	foreach ( $points as $point ) {
		$out .= '<li>' . esc_html( $point ) . '</li>';
	}
	return $out . '</ul></section>';
}

/* ---------------------------------------------------------------------
 * Sources
 * ------------------------------------------------------------------ */

/**
 * A post's sources, as the rebuttal sources panel stores them.
 *
 * @param int $post_id Post.
 * @return array<int,array{citation_text:string,url:string}>
 */
function murtadd_article_sources( $post_id ) {
	$out = array();
	foreach ( (array) get_post_meta( $post_id, '_murtadd_sources', true ) as $row ) {
		if ( is_array( $row ) && '' !== trim( (string) ( $row['citation_text'] ?? '' ) ) ) {
			$out[] = array(
				'citation_text' => (string) $row['citation_text'],
				'url'           => (string) ( $row['url'] ?? '' ),
			);
		}
	}
	return $out;
}

/**
 * The sources panel, as on a rebuttal. The same list is the BlogPosting's
 * citation.
 *
 * @return void
 */
function murtadd_article_sources_panel() {
	$sources = murtadd_article_sources( (int) get_the_ID() );
	if ( ! $sources ) {
		return;
	}
	echo '<section class="murtadd-sources-panel" id="murtadd-sources" aria-labelledby="murtadd-sources-title"><h2 id="murtadd-sources-title" class="murtadd-kicker">' . esc_html__( 'Sources', 'murtadd' ) . '</h2><ol class="murtadd-sources-list">';
	foreach ( $sources as $src ) {
		echo '<li>' . esc_html( $src['citation_text'] );
		murtadd_external_link( $src['url'], __( 'source ↗', 'murtadd' ), 'murtadd-source-link' );
		echo '</li>';
	}
	echo '</ol></section>';
}

/* ---------------------------------------------------------------------
 * Next step and related posts
 * ------------------------------------------------------------------ */

/**
 * Whether post a comes after post b in the Journal's reading order: by date,
 * then by ID, so two posts with the same date still have an order.
 *
 * @param WP_Post $a Post.
 * @param WP_Post $b Post.
 * @return int 1, 0 or -1.
 */
function murtadd_article_order( $a, $b ) {
	return array( (string) $a->post_date, (int) $a->ID ) <=> array( (string) $b->post_date, (int) $b->ID );
}

/**
 * The next post in the Journal's reading order: the next one published, or,
 * on the newest post, the one before it. Posts on the same date as this one
 * are compared by ID, so none is skipped.
 *
 * @param WP_Post|null $post Post; the current one when null.
 * @return array{0:WP_Post,1:bool}|null The post, and whether it comes after this one.
 */
function murtadd_article_next_post( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}
	$query = static function ( $date_query, $order, $count ) use ( $post ) {
		return get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => $count,
				'no_found_rows'  => true,
				'orderby'        => array(
					'date' => $order,
					'ID'   => $order,
				),
				'post__not_in'   => array( $post->ID ),
				'date_query'     => array( $date_query ),
			)
		);
	};
	// Posts on this very date, which only the ID can put before or after it.
	$same = $query(
		array(
			'after'     => $post->post_date,
			'before'    => $post->post_date,
			'inclusive' => true,
		),
		'ASC',
		-1
	);
	foreach ( array( 'after' => 'ASC', 'before' => 'DESC' ) as $way => $order ) {
		$sign = 'after' === $way ? 1 : -1;
		$ties = array_values(
			array_filter(
				$same,
				static function ( $p ) use ( $post, $sign ) {
					return murtadd_article_order( $p, $post ) === $sign;
				}
			)
		);
		if ( $ties ) {
			return array( 'after' === $way ? $ties[0] : end( $ties ), 1 === $sign );
		}
		$found = $query(
			array(
				$way        => $post->post_date,
				'inclusive' => false,
			),
			$order,
			1
		);
		if ( $found ) {
			return array( $found[0], 1 === $sign );
		}
	}
	return null;
}

/**
 * The address of the Contact page, where a reader can ask a question, or ''.
 *
 * @return string
 */
function murtadd_article_ask_url() {
	$page = get_page_by_path( 'contact' );
	return ( $page && 'publish' === get_post_status( $page ) ) ? (string) get_permalink( $page ) : '';
}

/**
 * The next step. The end variant offers the next post in the Journal, the
 * sources when the post lists some, and a question through the Contact
 * page. The mid variant is one line: a question, or the sources.
 *
 * @param string $variant end or mid.
 * @return string
 */
function murtadd_article_next_step( $variant = 'end' ) {
	$ask     = murtadd_article_ask_url();
	$sources = (bool) murtadd_article_sources( (int) get_the_ID() );
	if ( 'mid' === $variant ) {
		if ( '' !== $ask ) {
			$line = sprintf(
				/* translators: %s: link to the Contact page. */
				esc_html__( 'A question this post leaves open? %s, and it may become the next answer.', 'murtadd' ),
				'<a href="' . esc_url( $ask ) . '">' . esc_html__( 'Ask it', 'murtadd' ) . '</a>'
			);
		} elseif ( $sources ) {
			$line = sprintf(
				/* translators: %s: link to the sources panel. */
				esc_html__( 'Every claim here can be checked: %s.', 'murtadd' ),
				'<a href="#murtadd-sources">' . esc_html__( 'see the sources', 'murtadd' ) . '</a>'
			);
		} else {
			return '';
		}
		// One line in the text: a plain block, so the page keeps one "Next step" landmark.
		return '<div class="murtadd-next murtadd-next--mid"><p>' . $line . '</p></div>';
	}

	$next = murtadd_article_next_post();
	if ( ! $next && '' === $ask && ! $sources ) {
		return '';
	}
	$out = '<aside class="murtadd-next" aria-labelledby="murtadd-next-title"><h2 id="murtadd-next-title" class="murtadd-kicker">' . esc_html__( 'Next step', 'murtadd' ) . '</h2>';
	if ( $next ) {
		$out .= '<a class="murtadd-crosslink-card murtadd-next-post" href="' . esc_url( get_permalink( $next[0] ) ) . '"><span class="murtadd-kicker-small">' . esc_html( $next[1] ? __( 'Next in the Journal', 'murtadd' ) : __( 'Earlier in the Journal', 'murtadd' ) ) . '</span><span>' . esc_html( get_the_title( $next[0] ) ) . ' →</span></a>';
	}
	if ( '' !== $ask || $sources ) {
		$out .= '<p class="murtadd-next-actions">';
		if ( $sources ) {
			$out .= '<a class="murtadd-btn murtadd-btn--outline" href="#murtadd-sources">' . esc_html__( 'Check the sources', 'murtadd' ) . '</a>';
		}
		if ( '' !== $ask ) {
			$out .= '<a class="murtadd-btn murtadd-btn--outline" href="' . esc_url( $ask ) . '">' . esc_html__( 'Ask a question', 'murtadd' ) . '</a>';
		}
		$out .= '</p>';
	}
	return $out . '</aside>';
}

/**
 * Term ids of a post in one taxonomy, the default category left out: every
 * post has it, so sharing it says nothing.
 *
 * @param int    $post_id  Post.
 * @param string $taxonomy Taxonomy.
 * @return int[]
 */
function murtadd_article_term_ids( $post_id, $taxonomy ) {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( ! is_array( $terms ) ) {
		return array();
	}
	$skip = 'category' === $taxonomy ? (int) get_option( 'default_category' ) : 0;
	$ids  = array();
	foreach ( $terms as $term ) {
		if ( (int) $term->term_id !== $skip ) {
			$ids[] = (int) $term->term_id;
		}
	}
	return $ids;
}

/**
 * Score related candidates: 3 for each shared topic, 2 for each shared tag,
 * 1 for each shared category; ties go to the newer post. A post that shares
 * nothing scores 0 and still counts, after the rest: it is in the Journal.
 *
 * @param array<string,int[]>             $mine       This post's term ids by taxonomy.
 * @param array<int,array<string,int[]>>  $candidates Term ids by taxonomy, keyed by post id.
 * @param array<int,int>                  $dates      Unix time published, keyed by post id.
 * @param int                             $limit      How many.
 * @return int[] Post ids, best first.
 */
function murtadd_article_rank( array $mine, array $candidates, array $dates, $limit ) {
	$weights = array(
		'murtadd_topic' => 3,
		'post_tag'      => 2,
		'category'      => 1,
	);
	$scored  = array();
	foreach ( $candidates as $id => $terms ) {
		$score = 0;
		foreach ( $weights as $tax => $weight ) {
			$score += $weight * count( array_intersect( $mine[ $tax ] ?? array(), $terms[ $tax ] ?? array() ) );
		}
		$scored[] = array( $score, (int) ( $dates[ $id ] ?? 0 ), (int) $id );
	}
	usort(
		$scored,
		static function ( $a, $b ) {
			return array( $b[0], $b[1], $b[2] ) <=> array( $a[0], $a[1], $a[2] );
		}
	);
	return array_map( 'intval', array_column( array_slice( $scored, 0, max( 0, (int) $limit ) ), 2 ) );
}

/**
 * Related posts in the Journal, as cards: three, scored by
 * murtadd_article_rank() from the 30 newest posts. The next post in the
 * next step is left out, so a card never repeats it.
 *
 * @return string
 */
function murtadd_article_related() {
	if ( ! murtadd_article_on( 'related' ) ) {
		return '';
	}
	$id   = (int) get_the_ID();
	$skip = array( $id );
	$next = murtadd_article_next_post();
	if ( $next ) {
		$skip[] = (int) $next[0]->ID;
	}
	$pool = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 30,
			'no_found_rows'  => true,
			'post__not_in'   => $skip,
		)
	);
	if ( ! $pool ) {
		return '';
	}
	$taxes = array( 'murtadd_topic', 'post_tag', 'category' );
	$mine  = array();
	foreach ( $taxes as $tax ) {
		$mine[ $tax ] = murtadd_article_term_ids( $id, $tax );
	}
	$candidates = array();
	$dates      = array();
	$by_id      = array();
	foreach ( $pool as $p ) {
		foreach ( $taxes as $tax ) {
			$candidates[ $p->ID ][ $tax ] = murtadd_article_term_ids( $p->ID, $tax );
		}
		$dates[ $p->ID ] = (int) get_post_time( 'U', true, $p );
		$by_id[ $p->ID ] = $p;
	}
	$ids = murtadd_article_rank( $mine, $candidates, $dates, 3 );
	if ( ! $ids ) {
		return '';
	}
	$out = '<section class="murtadd-related murtadd-related--cards" aria-labelledby="murtadd-related-title"><h2 id="murtadd-related-title" class="murtadd-kicker">' . esc_html__( 'More from the Journal', 'murtadd' ) . '</h2><ul class="murtadd-related-cards">';
	foreach ( $ids as $rid ) {
		$p       = $by_id[ $rid ];
		$excerpt = trim( (string) $p->post_excerpt );
		$out    .= '<li class="murtadd-related-card"><time class="murtadd-kicker-small" datetime="' . esc_attr( (string) get_the_date( 'c', $p ) ) . '">' . esc_html( (string) get_the_date( '', $p ) ) . '</time>';
		$out    .= '<h3 class="murtadd-related-card__title"><a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( get_the_title( $p ) ) . '</a></h3>';
		if ( '' !== $excerpt ) {
			$out .= '<p class="murtadd-related-card__excerpt">' . esc_html( wp_trim_words( $excerpt, 22 ) ) . '</p>';
		}
		$out .= '</li>';
	}
	return $out . '</ul></section>';
}

/* ---------------------------------------------------------------------
 * The FAQ
 * ------------------------------------------------------------------ */

/**
 * Questions and answers from the text: each Details block in the FAQ style
 * (`is-style-faq`), its summary the question and the rest the answer. They
 * show on the page, so they may feed the FAQPage.
 *
 * @param string $html Stored text.
 * @return array<int,array> Question nodes.
 */
function murtadd_article_faq_pairs( $html ) {
	if ( ! preg_match_all( '#<details\b[^>]*\bclass\s*=\s*["\'][^"\']*\bis-style-faq\b[^"\']*["\'][^>]*>\s*<summary\b[^>]*>(.*?)</summary>(.*?)</details>#is', (string) $html, $found, PREG_SET_ORDER ) ) {
		return array();
	}
	$pairs = array();
	foreach ( $found as $m ) {
		$question = murtadd_article_text( $m[1] );
		$answer   = murtadd_article_text( preg_replace( '#<!--.*?-->#s', '', $m[2] ) );
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
 * The FAQ block style and the pattern that uses it.
 *
 * @return void
 */
function murtadd_article_register_faq() {
	if ( function_exists( 'register_block_style' ) ) {
		register_block_style(
			'core/details',
			array(
				'name'  => 'faq',
				'label' => __( 'FAQ', 'murtadd' ),
			)
		);
	}
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}
	$item = static function ( $question, $answer ) {
		return '<!-- wp:details {"className":"is-style-faq"} --><details class="wp-block-details is-style-faq"><summary>' . esc_html( $question ) . '</summary><!-- wp:paragraph --><p>' . esc_html( $answer ) . '</p><!-- /wp:paragraph --></details><!-- /wp:details -->';
	};
	register_block_pattern(
		'murtadd/faq',
		array(
			'title'       => __( 'Questions and answers', 'murtadd' ),
			'description' => __( 'A heading and two questions, each a Details block in the FAQ style. The questions show on the page and feed the FAQPage data.', 'murtadd' ),
			'categories'  => array( 'text' ),
			'postTypes'   => array( 'post' ),
			'content'     => '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'Questions readers ask', 'murtadd' ) . '</h2><!-- /wp:heading -->'
				. $item( __( 'The question, as a reader would ask it', 'murtadd' ), __( 'The answer, in one or two sentences.', 'murtadd' ) )
				. $item( __( 'A second question', 'murtadd' ), __( 'Its answer.', 'murtadd' ) ),
		)
	);
}
add_action( 'init', 'murtadd_article_register_faq' );

/* ---------------------------------------------------------------------
 * The post screen: key points and sources
 * ------------------------------------------------------------------ */

/**
 * The Journal fields box on the post screen.
 *
 * @return void
 */
function murtadd_article_meta_box() {
	add_meta_box( 'murtadd_journal_fields', __( 'Journal fields', 'murtadd' ), 'murtadd_article_render_meta_box', 'post', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'murtadd_article_meta_box' );

/**
 * Key points, one a line, and the sources repeater the rebuttal screen uses.
 * The sources are saved by murtadd_save_meta(), under the same nonce.
 *
 * @param WP_Post $post Post.
 * @return void
 */
function murtadd_article_render_meta_box( $post ) {
	wp_nonce_field( 'murtadd_save_meta', 'murtadd_meta_nonce' );
	$sources = murtadd_article_sources( (int) $post->ID );
	if ( ! $sources ) {
		$sources = array(
			array(
				'citation_text' => '',
				'url'           => '',
			),
		);
	}
	?>
	<p><label for="murtadd_takeaways"><strong><?php esc_html_e( 'Key points, one a line', 'murtadd' ); ?></strong></label><br>
	<textarea name="murtadd_takeaways" id="murtadd_takeaways" rows="4" class="large-text" aria-describedby="murtadd_takeaways-desc"><?php echo esc_textarea( (string) get_post_meta( $post->ID, MURTADD_ARTICLE_POINTS_KEY, true ) ); ?></textarea>
	<span class="description" id="murtadd_takeaways-desc"><?php esc_html_e( 'Shown in the "In short" box under the title. The excerpt shows above it. Leave empty for no box.', 'murtadd' ); ?></span></p>

	<p><strong><?php esc_html_e( 'Sources', 'murtadd' ); ?></strong> (<?php esc_html_e( 'citation text required per row; URL optional', 'murtadd' ); ?>)</p>
	<div id="murtadd-sources-repeater">
		<?php foreach ( $sources as $row ) : ?>
			<p class="murtadd-source-row">
				<input type="text" name="murtadd_sources_citation[]" value="<?php echo esc_attr( $row['citation_text'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Citation text', 'murtadd' ); ?>" aria-label="<?php esc_attr_e( 'Citation text', 'murtadd' ); ?>">
				<input type="url" name="murtadd_sources_url[]" value="<?php echo esc_url( $row['url'] ); ?>" class="regular-text" placeholder="https:// (optional)" aria-label="<?php esc_attr_e( 'Source address', 'murtadd' ); ?>">
				<button type="button" class="button murtadd-remove-source" aria-label="<?php esc_attr_e( 'Remove source', 'murtadd' ); ?>">&times;</button>
			</p>
		<?php endforeach; ?>
	</div>
	<p><button type="button" class="button" id="murtadd-add-source"><?php esc_html_e( 'Add source', 'murtadd' ); ?></button></p>
	<?php
}

/**
 * Save the key points. The sources go through murtadd_save_meta().
 *
 * @param int $post_id Post.
 * @return void
 */
function murtadd_article_save_points( $post_id ) {
	if ( ! isset( $_POST['murtadd_meta_nonce'], $_POST['murtadd_takeaways'] ) || ! wp_verify_nonce( sanitize_key( $_POST['murtadd_meta_nonce'] ), 'murtadd_save_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$points = implode( "\n", murtadd_article_points( sanitize_textarea_field( wp_unslash( $_POST['murtadd_takeaways'] ) ) ) );
	if ( '' === $points ) {
		delete_post_meta( $post_id, MURTADD_ARTICLE_POINTS_KEY );
		return;
	}
	update_post_meta( $post_id, MURTADD_ARTICLE_POINTS_KEY, $points );
}
add_action( 'save_post_post', 'murtadd_article_save_points' );

/* ---------------------------------------------------------------------
 * Structured data from the same pieces
 * ------------------------------------------------------------------ */

/**
 * The sources as citation works, for a BlogPosting or Article.
 *
 * @param int $post_id Post.
 * @return array[]
 */
function murtadd_article_citations( $post_id ) {
	$works = array();
	foreach ( murtadd_article_sources( $post_id ) as $source ) {
		$work = array(
			'@type' => 'CreativeWork',
			'name'  => murtadd_schema_text( $source['citation_text'] ),
		);
		if ( '' !== $source['url'] ) {
			$work['url'] = esc_url_raw( $source['url'] );
		}
		$works[] = $work;
	}
	return $works;
}

/**
 * The speakable parts of a Journal post: the title, the dek when there is
 * one, and the answer box when it shows.
 *
 * @param int $post_id Post.
 * @return string[]
 */
function murtadd_article_speakable( $post_id ) {
	$out = array( '.murtadd-entry-header h1' );
	if ( '' !== trim( (string) get_post_field( 'post_excerpt', $post_id ) ) ) {
		$out[] = '.murtadd-dek';
	}
	if ( murtadd_article_on( 'answer_box' ) && murtadd_article_post_points( $post_id ) ) {
		$out[] = '.murtadd-answer';
	}
	return $out;
}
