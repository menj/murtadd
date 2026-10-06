<?php
/**
 * Murtadd theme bootstrap.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

define( 'MURTADD_VERSION', '1.39.0' );

/**
 * Asset suffix. Sources are served when SCRIPT_DEBUG is on, minified otherwise.
 * Every file in /css and /js ships as a source and a .min pair.
 */
define( 'MURTADD_ASSET_SUFFIX', ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min' );

require_once get_stylesheet_directory() . '/inc/cpt-doubt.php';
require_once get_stylesheet_directory() . '/inc/cpt-rebuttal.php';
require_once get_stylesheet_directory() . '/inc/cpt-fatwa.php';
require_once get_stylesheet_directory() . '/inc/cpt-letter.php';
require_once get_stylesheet_directory() . '/inc/taxonomy-topic.php';
require_once get_stylesheet_directory() . '/inc/taxonomy-school.php';
require_once get_stylesheet_directory() . '/inc/taxonomy-doubt-category.php';
require_once get_stylesheet_directory() . '/inc/meta-boxes.php';
require_once get_stylesheet_directory() . '/inc/settings-page.php';
require_once get_stylesheet_directory() . '/inc/docs.php';
require_once get_stylesheet_directory() . '/inc/legacy-redirects.php';
require_once get_stylesheet_directory() . '/inc/social.php';
require_once get_stylesheet_directory() . '/inc/site-icon.php';
require_once get_stylesheet_directory() . '/inc/activation.php';
require_once get_stylesheet_directory() . '/inc/schema.php';
require_once get_stylesheet_directory() . '/inc/seed-content.php';
require_once get_stylesheet_directory() . '/inc/search.php';
require_once get_stylesheet_directory() . '/inc/meta-tags.php';
require_once get_stylesheet_directory() . '/inc/feeds.php';
require_once get_stylesheet_directory() . '/inc/analytics.php';
require_once get_stylesheet_directory() . '/inc/share.php';
require_once get_stylesheet_directory() . '/inc/social-card.php';
require_once get_stylesheet_directory() . '/inc/dashboard.php';
require_once get_stylesheet_directory() . '/inc/relations.php';
require_once get_stylesheet_directory() . '/inc/template-tags.php';

/**
 * Theme setup.
 */
function murtadd_setup() {
	load_theme_textdomain( 'murtadd', get_stylesheet_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	/*
	 * Featured images are enabled on the content types for DOCUMENTARY images
	 * only: a folio, a court document, a screenshot of a claim as it actually
	 * circulates. Not mood photography. Where no image is set, a generated
	 * card carries the doubt itself (inc/social-card.php).
	 */
	add_theme_support( 'post-thumbnails', array( 'post', 'page', 'murtadd_doubt', 'murtadd_rebuttal', 'murtadd_fatwa', 'murtadd_letter' ) );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'comment-form', 'comment-list' ) );
	add_theme_support( 'automatic-feed-links' );

	register_nav_menus(
		array(
			'primary'     => __( 'Primary (sidebar)', 'murtadd' ),
			'footer_site' => __( 'Footer — Site column', 'murtadd' ),
		)
	);
}
add_action( 'after_setup_theme', 'murtadd_setup' );

/**
 * Frontend assets.
 */
function murtadd_enqueue_assets() {
	// tokens -> logo -> main. The first two are shared with the admin, so the
	// brand colours and the wordmark cannot disagree between the two contexts.
	wp_enqueue_style( 'murtadd-tokens', get_stylesheet_directory_uri() . '/assets/css/tokens' . MURTADD_ASSET_SUFFIX . '.css', array(), MURTADD_VERSION );
	wp_enqueue_style( 'murtadd-logo', get_stylesheet_directory_uri() . '/assets/css/logo' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-tokens' ), MURTADD_VERSION );
	wp_enqueue_style( 'murtadd-main', get_stylesheet_directory_uri() . '/assets/css/main' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-tokens', 'murtadd-logo' ), MURTADD_VERSION );
	wp_enqueue_style( 'murtadd-social', get_stylesheet_directory_uri() . '/assets/css/social' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );

	/*
	 * RTL. WordPress auto-loads a root rtl.css only for a stylesheet enqueued
	 * from style.css, and this theme never enqueues style.css. The overrides are
	 * therefore a real stylesheet, loaded explicitly.
	 */
	if ( is_rtl() ) {
		wp_enqueue_style( 'murtadd-rtl', get_stylesheet_directory_uri() . '/assets/css/main-rtl' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
	}

	if ( is_singular( 'murtadd_doubt' ) ) {
		wp_enqueue_style( 'murtadd-doubt', get_stylesheet_directory_uri() . '/assets/css/doubt-template' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
	}
	if ( is_singular( 'murtadd_rebuttal' ) ) {
		wp_enqueue_style( 'murtadd-rebuttal', get_stylesheet_directory_uri() . '/assets/css/rebuttal-template' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
	}
	// The counterpart aside renders on both single types, so enqueue for either.
	if ( is_singular( array( 'murtadd_rebuttal', 'murtadd_doubt' ) ) ) {
		wp_enqueue_style( 'murtadd-counterpart', get_stylesheet_directory_uri() . '/assets/css/counterpart' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
	}
	if ( is_post_type_archive( 'murtadd_fatwa' ) || is_singular( 'murtadd_fatwa' ) || is_tax( 'murtadd_school' ) ) {
		wp_enqueue_style( 'murtadd-fatwa', get_stylesheet_directory_uri() . '/assets/css/fatwa-index' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
	}

	wp_enqueue_script( 'murtadd-main', get_stylesheet_directory_uri() . '/assets/js/main' . MURTADD_ASSET_SUFFIX . '.js', array(), MURTADD_VERSION, true );

	// Threaded replies — core script, only where a comment form is actually shown.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	/*
	 * Long-form furniture is for rebuttals only. Doubts are capped at a few
	 * hundred words by design; giving them a table of contents and a progress
	 * bar would advertise a length they do not have and must never have.
	 */
	if ( is_singular( 'murtadd_rebuttal' ) ) {
		wp_enqueue_style( 'murtadd-longform', get_stylesheet_directory_uri() . '/assets/css/longform' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
		wp_enqueue_script( 'murtadd-longform', get_stylesheet_directory_uri() . '/assets/js/longform' . MURTADD_ASSET_SUFFIX . '.js', array(), MURTADD_VERSION, true );
	}

	if ( is_singular( array( 'murtadd_doubt', 'murtadd_rebuttal' ) ) ) {
		wp_enqueue_style( 'murtadd-share', get_stylesheet_directory_uri() . '/assets/css/share' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
		wp_enqueue_script( 'murtadd-share', get_stylesheet_directory_uri() . '/assets/js/share' . MURTADD_ASSET_SUFFIX . '.js', array(), MURTADD_VERSION, true );
	}

	if ( is_singular( array( 'murtadd_doubt', 'murtadd_rebuttal' ) ) ) {
		wp_enqueue_style( 'murtadd-rail', get_stylesheet_directory_uri() . '/assets/css/rail' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
	}

	if ( is_singular( 'murtadd_letter' ) || is_post_type_archive( 'murtadd_letter' ) ) {
		wp_enqueue_style( 'murtadd-letter', get_stylesheet_directory_uri() . '/assets/css/letter' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
	}

	if ( is_page_template( 'templates/page-start-here.php' ) ) {
		wp_enqueue_style( 'murtadd-start-here', get_stylesheet_directory_uri() . '/assets/css/start-here' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
	}

	if ( is_singular() && ( comments_open() || get_comments_number() ) ) {
		wp_enqueue_style( 'murtadd-comments', get_stylesheet_directory_uri() . '/assets/css/comments' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
	}

	if ( is_post_type_archive( 'murtadd_fatwa' ) ) {
		wp_enqueue_script( 'murtadd-fatwa-filters', get_stylesheet_directory_uri() . '/assets/js/fatwa-filters' . MURTADD_ASSET_SUFFIX . '.js', array(), MURTADD_VERSION, true );
	}

	// Colour overrides from the Colours settings tab — never inline <style> blocks in templates.
	$colours = get_option( 'murtadd_colours', array() );
	$css     = '';
	if ( ! empty( $colours['accent'] ) ) {
		$css .= '--murtadd-accent:' . sanitize_hex_color( $colours['accent'] ) . ';';
	}
	if ( ! empty( $colours['accent_tint'] ) ) {
		$css .= '--murtadd-accent-tint:' . sanitize_hex_color( $colours['accent_tint'] ) . ';';
	}
	if ( $css ) {
		wp_add_inline_style( 'murtadd-main', ':root{' . $css . '}' );
	}
}
add_action( 'wp_enqueue_scripts', 'murtadd_enqueue_assets' );

/**
 * Preload the two variable fonts.
 *
 * Without this the browser discovers them only after parsing main.css, so the
 * first paint renders in Georgia and system-ui and then swaps. crossorigin is
 * required on font preloads even same-origin, or the preload is discarded and
 * the file fetched twice.
 */
function murtadd_preload_fonts() {
	$base = get_stylesheet_directory_uri() . '/assets/fonts';
	printf( '<link rel="preload" href="%s/SourceSerif4-Variable.woff2" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( $base ) );
	printf( '<link rel="preload" href="%s/PublicSans-Variable.woff2" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( $base ) );
}
add_action( 'wp_head', 'murtadd_preload_fonts', 2 );

/**
 * Admin assets — only on relevant screens.
 */
function murtadd_admin_assets( $hook ) {
	if ( 'appearance_page_murtadd' === $hook ) {
		wp_enqueue_script( 'murtadd-settings-tabs', get_stylesheet_directory_uri() . '/assets/js/settings-tabs' . MURTADD_ASSET_SUFFIX . '.js', array(), MURTADD_VERSION, true );

		// Admin skin. The tokens and the wordmark are the SAME files the front end
		// loads, so the mark in Theme Options is the mark on the site, not a copy.
		wp_enqueue_style( 'murtadd-tokens', get_stylesheet_directory_uri() . '/assets/css/tokens' . MURTADD_ASSET_SUFFIX . '.css', array(), MURTADD_VERSION );
		wp_enqueue_style( 'murtadd-logo', get_stylesheet_directory_uri() . '/assets/css/logo' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-tokens' ), MURTADD_VERSION );
		wp_enqueue_style( 'murtadd-admin', get_stylesheet_directory_uri() . '/assets/css/admin' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-tokens', 'murtadd-logo' ), MURTADD_VERSION );
		wp_enqueue_style( 'murtadd-docs', get_stylesheet_directory_uri() . '/assets/css/docs' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-admin' ), MURTADD_VERSION );
		wp_enqueue_style( 'murtadd-social', get_stylesheet_directory_uri() . '/assets/css/social' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-admin' ), MURTADD_VERSION );

		// The owner's accent choice applies to the admin screen too.
		$murtadd_admin_colours = get_option( 'murtadd_colours', array() );
		if ( ! empty( $murtadd_admin_colours['accent'] ) ) {
			wp_add_inline_style(
				'murtadd-admin',
				sprintf(
					':root{--murtadd-accent:%s;--murtadd-accent-tint:%s;}',
					sanitize_hex_color( $murtadd_admin_colours['accent'] ),
					sanitize_hex_color( $murtadd_admin_colours['accent_tint'] ?? '#eefaf7' )
				)
			);
		}
	}
	if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		$screen = get_current_screen();
		if ( $screen && in_array( $screen->post_type, array( 'murtadd_doubt', 'murtadd_rebuttal', 'murtadd_fatwa' ), true ) ) {
			wp_enqueue_script( 'murtadd-meta-boxes', get_stylesheet_directory_uri() . '/assets/js/meta-boxes' . MURTADD_ASSET_SUFFIX . '.js', array(), MURTADD_VERSION, true );
			wp_localize_script(
				'murtadd-meta-boxes',
				'murtaddMeta',
				array(
					'wordCap'     => (int) murtadd_option( 'murtadd_content_display', 'short_response_word_cap', 400 ),
					'capWarning'  => __( 'Over the soft cap — consider trimming. Saving is not blocked.', 'murtadd' ),
					'wordsLabel'  => __( 'words', 'murtadd' ),
				)
			);
		}
	}
}
add_action( 'admin_enqueue_scripts', 'murtadd_admin_assets' );

/**
 * Fatwa archive: sorting + GET filters (works with JS disabled).
 */
function murtadd_fatwa_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( ! $query->is_post_type_archive( 'murtadd_fatwa' ) && ! $query->is_tax( 'murtadd_school' ) ) {
		return;
	}

	$sort = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : murtadd_option( 'murtadd_content_display', 'fatwa_index_default_sort', 'scholar_az' );

	switch ( $sort ) {
		case 'era':
			$query->set( 'meta_key', '_murtadd_era' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'ASC' );
			break;
		case 'recently_added':
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
			break;
		default:
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' );
	}

	$tax_query = array();
	if ( ! empty( $_GET['school'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'murtadd_school',
			'field'    => 'slug',
			'terms'    => sanitize_key( wp_unslash( $_GET['school'] ) ),
		);
	}
	if ( ! empty( $_GET['topic'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'murtadd_topic',
			'field'    => 'slug',
			'terms'    => sanitize_key( wp_unslash( $_GET['topic'] ) ),
		);
	}
	if ( $tax_query ) {
		$query->set( 'tax_query', $tax_query );
	}
	if ( ! empty( $_GET['era'] ) ) {
		$query->set(
			'meta_query',
			array(
				array(
					'key'   => '_murtadd_era',
					'value' => sanitize_key( wp_unslash( $_GET['era'] ) ),
				),
			)
		);
	}
}
add_action( 'pre_get_posts', 'murtadd_fatwa_archive_query' );

/* Cross-site content links between the sister sites. */
require_once get_stylesheet_directory() . '/inc/network-links.php';
