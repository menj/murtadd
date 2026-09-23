<?php
/**
 * Template tags — escaped output helpers. All frontend output flows through these.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/** Read one key from a settings group with a default. */
function murtadd_option( $group, $key, $default = '' ) {
	$o = get_option( $group, array() );
	return isset( $o[ $key ] ) && '' !== $o[ $key ] ? $o[ $key ] : $default;
}

/** Doubt statement (falls back to post title). */
function murtadd_the_doubt_statement( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	$val     = get_post_meta( $post_id, '_murtadd_doubt_statement', true );
	echo esc_html( $val ?: get_the_title( $post_id ) );
}

/** Short response, paragraphs preserved. */
function murtadd_the_short_response( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	echo wp_kses_post( wpautop( get_post_meta( $post_id, '_murtadd_short_response', true ) ) );
}

/** Outbound link — new tab, rel=noopener. */
function murtadd_external_link( $url, $label, $class = '' ) {
	if ( ! $url ) {
		return;
	}
	printf(
		'<a class="%s" href="%s" target="_blank" rel="noopener">%s</a>',
		esc_attr( $class ),
		esc_url( $url ),
		esc_html( $label )
	);
}

/** Related post link if set and published. */
function murtadd_related_link( $meta_key, $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	$related = (int) get_post_meta( $post_id, $meta_key, true );
	if ( $related && 'publish' === get_post_status( $related ) ) {
		return $related;
	}
	return 0;
}

/** Estimated read time from a string. */
function murtadd_read_time( $text ) {
	$words = str_word_count( wp_strip_all_tags( $text ) );
	$mins  = max( 1, (int) round( $words / 220 ) );
	/* translators: %d: minutes */
	return sprintf( _n( '%d min read', '%d min read', $mins, 'murtadd' ), $mins );
}

/** First term for a taxonomy, as a linked tag chip. */
function murtadd_term_chip( $taxonomy, $class = 'murtadd-chip' ) {
	$terms = get_the_terms( get_the_ID(), $taxonomy );
	if ( $terms && ! is_wp_error( $terms ) ) {
		$term = $terms[0];
		printf( '<a class="%s" href="%s">%s</a>', esc_attr( $class ), esc_url( get_term_link( $term ) ), esc_html( $term->name ) );
	}
}

/** "If you are struggling" support notice — Emotional + Identity doubt pages. */
function murtadd_struggling_notice() {
	if ( ! murtadd_option( 'murtadd_content_display', 'show_struggling_notice', 1 ) ) {
		return;
	}
	get_template_part( 'template-parts/notices/notice', 'struggling' );
}

/** Whether the current doubt is in a category that shows the support notice. */
function murtadd_doubt_needs_notice( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	return has_term( array( 'emotional', 'identity' ), 'murtadd_doubt_category', $post_id );
}

/** Footer heritage note. */
function murtadd_the_heritage_note() {
	echo esc_html( murtadd_option( 'murtadd_content_display', 'footer_heritage_note', __( 'online since 2004', 'murtadd' ) ) );
}

/** Fallback secondary links (sidebar + front-page footer) until a menu is assigned. */
function murtadd_secondary_fallback() {
	$links = murtadd_secondary_links();
	if ( ! $links ) {
		return;
	}
	echo '<ul class="murtadd-secondary-list">';
	foreach ( $links as $url => $label ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

/**
 * The four doubt categories in triage order: Intellectual, Scriptural,
 * Emotional, Identity. get_terms() returns alphabetically, which breaks
 * the 01-04 numbering on the homepage grid and category tabs.
 *
 * @return WP_Term[]
 */
function murtadd_doubt_categories_ordered() {
	$order = array( 'intellectual', 'scriptural', 'emotional', 'identity' );
	$terms = get_terms( array( 'taxonomy' => 'murtadd_doubt_category', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	usort(
		$terms,
		function ( $a, $b ) use ( $order ) {
			$pa = array_search( $a->slug, $order, true );
			$pb = array_search( $b->slug, $order, true );
			return ( false === $pa ? 99 : $pa ) <=> ( false === $pb ? 99 : $pb );
		}
	);
	return $terms;
}

/**
 * Human label for a post type, used by every mixed-type list in the theme.
 *
 * @param string $post_type Post type slug.
 * @return string
 */
function murtadd_type_label( $post_type ) {
	$labels = array(
		'murtadd_doubt'    => __( 'Doubt', 'murtadd' ),
		'murtadd_rebuttal' => __( 'Rebuttal', 'murtadd' ),
		'murtadd_fatwa'    => __( 'Fatwa', 'murtadd' ),
		'post'             => __( 'Blog', 'murtadd' ),
		'page'             => __( 'Page', 'murtadd' ),
	);
	return $labels[ $post_type ] ?? '';
}

/**
 * Single comment renderer — used by wp_list_comments() in comments.php.
 *
 * @param WP_Comment $comment Comment object.
 * @param array      $args    Formatting args.
 * @param int        $depth   Nesting depth.
 */
function murtadd_render_comment( $comment, $args, $depth ) {
	$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
	?>
	<<?php echo esc_attr( $tag ); ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( 'murtadd-comment', $comment ); ?>>
		<article class="murtadd-comment-body">
			<header class="murtadd-comment-header">
				<?php if ( 0 !== (int) $args['avatar_size'] ) : ?>
					<span class="murtadd-comment-avatar"><?php echo get_avatar( $comment, (int) $args['avatar_size'] ); ?></span>
				<?php endif; ?>
				<span class="murtadd-comment-author"><?php echo esc_html( get_comment_author( $comment ) ); ?></span>
				<span class="murtadd-comment-date">
					<a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>"><?php echo esc_html( get_comment_date( '', $comment ) ); ?></a>
				</span>
			</header>

			<?php if ( '0' === $comment->comment_approved ) : ?>
				<p class="murtadd-comment-pending"><?php esc_html_e( 'Awaiting moderation.', 'murtadd' ); ?></p>
			<?php endif; ?>

			<div class="murtadd-comment-content"><?php comment_text(); ?></div>

			<footer class="murtadd-comment-footer">
				<?php
				comment_reply_link(
					array_merge(
						$args,
						array(
							'add_below' => 'comment',
							'depth'     => $depth,
							'max_depth' => $args['max_depth'],
							'before'    => '<span class="murtadd-comment-reply">',
							'after'     => '</span>',
						)
					),
					$comment
				);
				?>
			</footer>
		</article>
	<?php
	// wp_list_comments() closes the tag.
}

/**
 * The wordmark. One component, so the sidebar and the footer cannot drift apart.
 *
 * The strike and the full stop are the identity; the only thing that changes
 * between contexts is the ground it sits on.
 *
 * @param string $variant 'on-dark' (green panel) or 'on-light' (ivory ground).
 */
function murtadd_the_logo( $variant = 'on-dark', $linked = true ) {
	$variant = in_array( $variant, array( 'on-dark', 'on-light' ), true ) ? $variant : 'on-dark';

	$inner = sprintf(
		'<span class="murtadd-logo-word">%s</span><span class="murtadd-logo-stop">.</span>',
		esc_html__( 'murtadd', 'murtadd' )
	);

	// The footer mark closes the page; it is a statement, not a way out of it.
	// A second link home, directly above a footer already full of links, is
	// noise, and anything that is not clickable should not behave as though it is.
	if ( ! $linked ) {
		printf( '<span class="murtadd-logo murtadd-logo--%s">%s</span>', esc_attr( $variant ), $inner ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $inner is built from escaped parts above.
		return;
	}

	printf(
		'<a class="murtadd-logo murtadd-logo--%1$s" href="%2$s" rel="home">%3$s</a>',
		esc_attr( $variant ),
		esc_url( home_url( '/' ) ),
		$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts above.
	);
}

/**
 * Secondary links (About / FAQ / Contact / Privacy) that actually exist as pages.
 *
 * @return array<string,string> Permalink => label.
 */
function murtadd_secondary_links() {
	$links = array();
	foreach ( array(
		'about'   => __( 'About', 'murtadd' ),
		'faq'     => __( 'FAQ', 'murtadd' ),
		'contact' => __( 'Contact', 'murtadd' ),
		'privacy' => __( 'Privacy', 'murtadd' ),
	) as $slug => $label ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$links[ get_permalink( $page ) ] = $label;
		}
	}
	return $links;
}

/** Whether there is anything to put in a secondary-links column at all. */
function murtadd_has_secondary_links() {
	return has_nav_menu( 'footer_site' ) || (bool) murtadd_secondary_links();
}

/**
 * Mark the current top-level nav item (aria-current + class).
 *
 * The sidebar previously rendered identically on every page: no wayfinding
 * for sighted users, nothing announced to screen readers.
 *
 * @param string $section One of home|blog|topics|fatwa|doubts|rebuttals.
 */
function murtadd_nav_state( $section ) {
	$current = false;

	switch ( $section ) {
		case 'home':
			$current = is_front_page();
			break;
		case 'blog':
			$current = is_home() || ( is_singular( 'post' ) );
			break;
		case 'topics':
			$current = is_tax( 'murtadd_topic' );
			break;
		case 'fatwa':
			$current = is_post_type_archive( 'murtadd_fatwa' ) || is_singular( 'murtadd_fatwa' ) || is_tax( 'murtadd_school' );
			break;
		case 'doubts':
			$current = is_post_type_archive( 'murtadd_doubt' ) || is_singular( 'murtadd_doubt' ) || is_tax( 'murtadd_doubt_category' );
			break;
		case 'rebuttals':
			$current = is_post_type_archive( 'murtadd_rebuttal' ) || is_singular( 'murtadd_rebuttal' );
			break;
		case 'letters':
			$current = is_post_type_archive( 'murtadd_letter' ) || is_singular( 'murtadd_letter' );
			break;
	}

	if ( $current ) {
		echo ' class="is-current" aria-current="page"';
	}
}

/**
 * Visible review date (item 7). dateModified was in the schema and never
 * shown to a human; on a site making scholarly claims, the review date is a
 * trust signal for readers, not only for crawlers.
 */
function murtadd_the_reviewed_date() {
	$published = get_the_date( 'U' );
	$modified  = get_the_modified_date( 'U' );

	if ( $modified > $published + DAY_IN_SECONDS ) {
		printf(
			'<span class="murtadd-reviewed">%s <time datetime="%s">%s</time></span>',
			esc_html__( 'Last reviewed', 'murtadd' ),
			esc_attr( get_the_modified_date( 'c' ) ),
			esc_html( get_the_modified_date() )
		);
		return;
	}

	printf(
		'<span class="murtadd-reviewed">%s <time datetime="%s">%s</time></span>',
		esc_html__( 'Published', 'murtadd' ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);
}

/**
 * Related doubts from the same door (item 6). The single doubt page stopped
 * dead after the rebuttal link; three siblings keep the reader inside the
 * triage structure instead of back at a search box.
 */
function murtadd_related_doubts() {
	$terms = get_the_terms( get_the_ID(), 'murtadd_doubt_category' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return;
	}

	$related = get_posts(
		array(
			'post_type'      => 'murtadd_doubt',
			'posts_per_page' => 3,
			'post__not_in'   => array( get_the_ID() ),
			'no_found_rows'  => true,
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- three posts, one term, front of a single view.
				array(
					'taxonomy' => 'murtadd_doubt_category',
					'field'    => 'term_id',
					'terms'    => $terms[0]->term_id,
				),
			),
		)
	);

	if ( ! $related ) {
		return;
	}
	?>
	<section class="murtadd-related">
		<h2 class="murtadd-kicker">
			<?php
			/* translators: %s: doubt category name */
			printf( esc_html__( 'More %s doubts', 'murtadd' ), esc_html( strtolower( $terms[0]->name ) ) );
			?>
		</h2>
		<ul class="murtadd-related-list">
			<?php foreach ( $related as $murtadd_rel ) : ?>
				<li>
					<a href="<?php echo esc_url( get_permalink( $murtadd_rel ) ); ?>">
						&ldquo;<?php echo esc_html( get_post_meta( $murtadd_rel->ID, '_murtadd_doubt_statement', true ) ); ?>&rdquo;
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php
}

/**
 * Letter dateline: outlet and original publication date.
 *
 * Mandatory on every letter surface. An archived position must announce its
 * date before it is read, so that a reader is never left to assume a piece
 * written years ago represents where the site stands today.
 */
function murtadd_letter_dateline() {
	$outlet = get_post_meta( get_the_ID(), '_murtadd_letter_outlet', true );
	$date   = get_post_meta( get_the_ID(), '_murtadd_letter_date', true );

	if ( ! $outlet && ! $date ) {
		return;
	}

	$parts = array();
	if ( $outlet ) {
		$parts[] = esc_html( $outlet );
	}
	if ( $date ) {
		$ts = strtotime( $date );
		if ( $ts ) {
			$parts[] = esc_html( date_i18n( 'j F Y', $ts ) );
		}
	}

	printf(
		'<p class="murtadd-letter-dateline">%s</p>',
		esc_html( implode( ' · ', array_map( 'wp_strip_all_tags', $parts ) ) )
	);
}

/**
 * The four category tabs.
 *
 * Was inline in taxonomy-murtadd_doubt_category.php; the doubts index needs it
 * too, and two copies of the same markup is how they drift apart.
 */
function murtadd_doubt_category_tabs() {
	$cats = murtadd_doubt_categories_ordered();
	if ( ! $cats ) {
		return;
	}

	$current = get_queried_object();
	$i       = 0;

	echo '<div class="murtadd-category-tabs">';
	foreach ( $cats as $cat ) {
		++$i;
		$active = ( $current instanceof WP_Term && $cat->term_id === $current->term_id );
		printf(
			'<a class="murtadd-category-tab %1$s" href="%2$s"><span class="murtadd-tab-num">%3$s</span>%4$s</a>',
			$active ? 'is-active' : '',
			esc_url( get_term_link( $cat ) ),
			esc_html( str_pad( (string) $i, 2, '0', STR_PAD_LEFT ) ),
			esc_html( $cat->name )
		);
	}
	echo '</div>';
}
