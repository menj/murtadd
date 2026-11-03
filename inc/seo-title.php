<?php
/**
 * The title tag, canonical links on archives, and the image sitemap.
 *
 * Title rule: "<page title> | <site name>", under 60 characters, on every
 * view, with or without Rank Math (inc/rank-math.php runs the same builder
 * on rank_math/frontend/title). A page listed in inc/data/seo-titles.json
 * (by post type and slug, read at render time, so sites seeded earlier get
 * it too) uses that page part; any other title too long for the budget is
 * cut at a word boundary.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The longest title tag allowed, in characters.
 */
const MURTADD_TITLE_MAX = 59;

/**
 * The site name as plain text.
 *
 * @return string
 */
function murtadd_title_site_name() {
	return trim( html_entity_decode( wp_strip_all_tags( (string) get_bloginfo( 'name' ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}

/**
 * Character length, multibyte-safe.
 *
 * @param string $text Text.
 * @return int
 */
function murtadd_title_len( $text ) {
	return function_exists( 'mb_strlen' ) ? mb_strlen( $text, 'UTF-8' ) : strlen( $text );
}

/**
 * The title-tag page parts set apart from the post title, keyed
 * "post_type/slug" (inc/data/seo-titles.json): for a title too long for the
 * tag, or one another page shares.
 *
 * @return array<string,string>
 */
function murtadd_short_titles() {
	static $map = null;
	if ( null === $map ) {
		$map  = array();
		$file = __DIR__ . '/data/seo-titles.json';
		$data = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
		foreach ( is_array( $data ) ? $data : array() as $key => $title ) {
			if ( is_string( $title ) && false !== strpos( (string) $key, '/' ) ) {
				$map[ $key ] = $title;
			}
		}
	}
	/**
	 * Title-tag page parts for pages whose title does not fit or is shared.
	 *
	 * @param array<string,string> $map "post_type/slug" => page part.
	 */
	return (array) apply_filters( 'murtadd_short_titles', $map );
}

/**
 * Fit a page part into a budget: as it is, else cut at a word boundary.
 *
 * @param string $part   Page part.
 * @param int    $budget Characters allowed.
 * @return string
 */
function murtadd_title_fit( $part, $budget ) {
	$part = trim( preg_replace( '/\s+/u', ' ', $part ) ?? $part );
	if ( $budget < 1 || murtadd_title_len( $part ) <= $budget ) {
		return $part;
	}
	// The first $budget characters; /u never splits a UTF-8 sequence.
	$cut = preg_match( '/^.{0,' . (int) $budget . '}/us', $part, $m ) ? $m[0] : '';
	$sp  = strrpos( $cut, ' ' );
	if ( false !== $sp && $sp > 0 ) {
		$cut = substr( $cut, 0, $sp );
	}
	return preg_replace( '/[\s,;:.\x{2013}\x{2014}-]+$/u', '', $cut ) ?? $cut;
}

/**
 * The page part of the title for the current view, before fitting.
 *
 * @return array{0:string,1:string} The post title part and its listed replacement ('' if none).
 */
function murtadd_title_view_part() {
	if ( ! empty( $GLOBALS['murtadd_gone'] ) ) {
		return array( __( 'Page removed', 'murtadd' ), '' );
	}
	if ( is_404() ) {
		return array( __( 'Page not found', 'murtadd' ), '' );
	}
	if ( is_search() ) {
		/* translators: %s: search terms */
		return array( sprintf( __( 'Search results for "%s"', 'murtadd' ), get_search_query( false ) ), '' );
	}
	if ( is_front_page() ) {
		return array( (string) get_bloginfo( 'description' ), '' );
	}
	if ( is_home() ) {
		$page = (int) get_option( 'page_for_posts' );
		return array( $page ? get_the_title( $page ) : __( 'Blog', 'murtadd' ), '' );
	}
	if ( is_singular() ) {
		$post  = get_queried_object();
		$short = '';
		if ( $post && isset( $post->post_type, $post->post_name ) ) {
			$map   = murtadd_short_titles();
			$short = $map[ $post->post_type . '/' . $post->post_name ] ?? '';
		}
		return array( (string) single_post_title( '', false ), (string) $short );
	}
	if ( is_tax() || is_category() || is_tag() ) {
		return array( (string) single_term_title( '', false ), '' );
	}
	if ( is_post_type_archive() ) {
		return array( (string) post_type_archive_title( '', false ), '' );
	}
	if ( is_author() ) {
		$author = get_queried_object();
		return array( $author && isset( $author->display_name ) ? $author->display_name : '', '' );
	}
	if ( is_date() ) {
		return array( (string) get_the_archive_title(), '' );
	}
	return array( '', '' );
}

/**
 * Build a title tag from a page part.
 *
 * @param string $part  Page part.
 * @param string $short Page part from inc/data/seo-titles.json, which wins.
 * @param int    $paged Page number of a paged view (1 for the first).
 * @return string
 */
function murtadd_build_title( $part, $short = '', $paged = 1 ) {
	$site = murtadd_title_site_name();
	$part = trim( html_entity_decode( wp_strip_all_tags( (string) $part ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	if ( '' === $part ) {
		return murtadd_title_fit( $site, MURTADD_TITLE_MAX );
	}
	$suffix = '' !== $site ? ' | ' . $site : '';
	/* translators: %d: page number */
	$page   = $paged > 1 ? ', ' . sprintf( __( 'page %d', 'murtadd' ), $paged ) : '';
	$budget = MURTADD_TITLE_MAX - murtadd_title_len( $suffix ) - murtadd_title_len( $page );
	if ( '' !== $short ) {
		$part = $short;
	}
	return murtadd_title_fit( $part, $budget ) . $page . $suffix;
}

/**
 * The title tag for the current view.
 *
 * @return string
 */
function murtadd_document_title() {
	list( $part, $short ) = murtadd_title_view_part();
	$paged                = max( (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ), 1 );
	return murtadd_build_title( $part, $short, $paged );
}

/**
 * Core's title, replaced by the theme's builder.
 *
 * @param string $title Title from an earlier filter, usually empty.
 * @return string
 */
function murtadd_pre_get_document_title( $title ) {
	if ( function_exists( 'murtadd_rank_math_active' ) && murtadd_rank_math_active() ) {
		return $title; // Rank Math filters its own title; see inc/rank-math.php.
	}
	return murtadd_document_title();
}
add_filter( 'pre_get_document_title', 'murtadd_pre_get_document_title', 20 );

/**
 * The canonical address of an archive view, or '' where core prints one
 * (singular views) or none is wanted (search, 404).
 *
 * @return string
 */
function murtadd_archive_canonical_url() {
	if ( is_singular() || is_search() || is_404() ) {
		return '';
	}
	$url = '';
	if ( is_front_page() ) {
		$url = home_url( '/' );
	} elseif ( is_home() ) {
		$page = (int) get_option( 'page_for_posts' );
		$url  = $page ? (string) get_permalink( $page ) : home_url( '/' );
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$link = get_term_link( get_queried_object() );
		$url  = is_wp_error( $link ) ? '' : (string) $link;
	} elseif ( is_post_type_archive() ) {
		$type = get_query_var( 'post_type' );
		$type = is_array( $type ) ? (string) reset( $type ) : (string) $type;
		$url  = (string) get_post_type_archive_link( $type );
	}
	if ( '' === $url ) {
		return '';
	}
	$paged = (int) get_query_var( 'paged' );
	if ( $paged > 1 ) {
		$url = trailingslashit( $url ) . user_trailingslashit( 'page/' . $paged, 'paged' );
	}
	return $url;
}

/**
 * Print the canonical link on archive views. Rank Math prints its own.
 */
function murtadd_archive_canonical() {
	if ( function_exists( 'murtadd_rank_math_active' ) && murtadd_rank_math_active() ) {
		return;
	}
	$url = murtadd_archive_canonical_url();
	if ( '' !== $url ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
	}
}
add_action( 'wp_head', 'murtadd_archive_canonical', 6 );

/* ---------- Image sitemap ---------- */

/**
 * Whether Rank Math's sitemap, which lists images itself, is serving.
 *
 * @return bool
 */
function murtadd_rank_math_sitemap_active() {
	if ( ! function_exists( 'murtadd_rank_math_active' ) || ! murtadd_rank_math_active() ) {
		return false;
	}
	if ( class_exists( '\RankMath\Helper' ) && method_exists( '\RankMath\Helper', 'is_module_active' ) ) {
		return (bool) \RankMath\Helper::is_module_active( 'sitemap' );
	}
	return true;
}

/**
 * The image sitemap's entries: each published entry with a featured image
 * or images attached to it.
 *
 * @return array<int,array{loc:string,images:string[]}>
 */
function murtadd_image_sitemap_entries() {
	$types = array( 'murtadd_doubt', 'murtadd_rebuttal', 'murtadd_fatwa', 'murtadd_guide', 'murtadd_letter', 'post', 'page' );
	$out   = array();
	$paged = 1;
	do {
		// Batches of 200, so no published entry is left out.
		$ids = (array) get_posts(
			array(
				'post_type'      => $types,
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'paged'          => $paged,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'has_password'   => false,
			)
		);
		if ( ! $ids ) {
			break;
		}
		// One query for the batch's post meta and featured images, not one per post.
		if ( function_exists( 'update_meta_cache' ) ) {
			update_meta_cache( 'post', $ids );
		}
		$thumbs = array();
		foreach ( $ids as $id ) {
			$t = (int) get_post_thumbnail_id( $id );
			if ( $t ) {
				$thumbs[ $id ] = $t;
			}
		}
		if ( $thumbs && function_exists( '_prime_post_caches' ) ) {
			_prime_post_caches( array_values( array_unique( $thumbs ) ), false, true );
		}
		foreach ( $ids as $id ) {
			$images = array();
			if ( isset( $thumbs[ $id ] ) ) {
				$images[] = (string) wp_get_attachment_url( $thumbs[ $id ] );
			}
			foreach ( (array) get_attached_media( 'image', $id ) as $att ) {
				$images[] = (string) wp_get_attachment_url( $att->ID );
			}
			$images = array_values( array_unique( array_filter( $images ) ) );
			if ( $images ) {
				$out[] = array(
					'loc'    => (string) get_permalink( $id ),
					'images' => array_slice( $images, 0, 1000 ),
				);
			}
		}
		++$paged;
	} while ( 200 === count( $ids ) );
	return $out;
}

/**
 * The image sitemap as XML.
 *
 * @param array $entries From murtadd_image_sitemap_entries().
 * @return string
 */
function murtadd_image_sitemap_xml( $entries ) {
	$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
		. '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
	foreach ( $entries as $e ) {
		$xml .= "\t<url>\n\t\t<loc>" . esc_xml( $e['loc'] ) . "</loc>\n";
		foreach ( $e['images'] as $img ) {
			$xml .= "\t\t<image:image><image:loc>" . esc_xml( $img ) . "</image:loc></image:image>\n";
		}
		$xml .= "\t</url>\n";
	}
	return $xml . "</urlset>\n";
}

/**
 * The address of the image sitemap.
 *
 * @return string
 */
function murtadd_image_sitemap_url() {
	return home_url( '/murtadd-image-sitemap.xml' );
}

/**
 * Serve /murtadd-image-sitemap.xml, unless Rank Math's sitemap is on.
 */
function murtadd_image_sitemap_render() {
	if ( murtadd_rank_math_sitemap_active() ) {
		return;
	}
	$request = isset( $GLOBALS['wp']->request ) ? trim( (string) $GLOBALS['wp']->request, '/' ) : '';
	if ( 'murtadd-image-sitemap.xml' !== $request ) {
		return;
	}
	status_header( 200 );
	header( 'Content-Type: application/xml; charset=UTF-8' );
	header( 'X-Robots-Tag: noindex, follow' );
	echo murtadd_image_sitemap_xml( murtadd_image_sitemap_entries() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the builder.
	exit;
}
add_action( 'template_redirect', 'murtadd_image_sitemap_render', 0 );

/**
 * List the image sitemap in robots.txt.
 *
 * @param string $output Robots.txt body.
 * @param bool   $public Whether the site is public.
 * @return string
 */
function murtadd_image_sitemap_robots( $output, $public ) {
	if ( ! $public || murtadd_rank_math_sitemap_active() ) {
		return $output;
	}
	return rtrim( (string) $output ) . "\nSitemap: " . esc_url_raw( murtadd_image_sitemap_url() ) . "\n";
}
add_filter( 'robots_txt', 'murtadd_image_sitemap_robots', 20, 2 );
