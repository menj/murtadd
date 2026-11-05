<?php
/**
 * Murtadd theme bootstrap.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

define( 'MURTADD_VERSION', '1.55.8' );

/**
 * Asset suffix. Sources are served when SCRIPT_DEBUG is on, minified otherwise.
 * Every file in /css and /js ships as a source and a .min pair.
 */
define( 'MURTADD_ASSET_SUFFIX', ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min' );

/**
 * The theme's files in inc/, in the order they load, without ".php".
 *
 * @return string[]
 */
function murtadd_theme_files() {
	return array(
		'cpt-doubt', 'cpt-rebuttal', 'cpt-fatwa', 'cpt-letter', 'cpt-guide', 'taxonomy-topic', 'taxonomy-school',
		'taxonomy-doubt-category', 'meta-boxes', 'settings-page', 'hardening', 'security-detector', 'security',
		'dev-files', 'hide-login', 'login', 'docs', 'legacy-redirects', 'gone', 'maintenance', 'social',
		'site-icon', 'activation', 'schema', 'schema-graph', 'rank-math', 'seo-title', 'seed-content',
		'seed-guides', 'seed-journal', 'slug-renames', 'search', 'search-permalinks', 'unlist', 'reading-progress',
		'meta-tags', 'feeds', 'analytics', 'share', 'social-card', 'dashboard', 'relations', 'contextual-links',
		'glossary', 'lanah', 'contact', 'quran-text', 'template-tags',
	);
}

/**
 * The theme caught half uploaded. An FTP upload that sends functions.php
 * before inc/ left murtadd.org answering a fatal error for every visitor
 * (237 in the October and November 2026 logs). Every file is now checked
 * before any loads, and each loads inside a try, so a half-written one (a
 * ParseError) is caught too. The request gets the "Back shortly" page with
 * a 503 and Retry-After; the error log gets one line per problem every ten
 * minutes, not one per request. A missing file leaves the admin alone, so
 * the upload can finish; a file that breaks part-way through loading sends
 * the page to the admin too, since the theme is then half set up. The login
 * screen, AJAX, cron and the command line go on without it.
 *
 * @param string $what What is wrong, for the log.
 * @param bool   $all  Send the page to the admin screens too.
 */
function murtadd_incomplete_theme( $what, $all = false ) {
	$key = 'murtadd_incomplete_' . md5( $what );
	if ( ! get_transient( $key ) ) {
		set_transient( $key, 1, 10 * MINUTE_IN_SECONDS );
		error_log( 'Murtadd: the theme is incomplete or being uploaded (' . $what . '); visitors see the maintenance page until it is whole.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	}
	if ( ! murtadd_incomplete_serves( $all ) ) {
		return;
	}
	if ( ! headers_sent() ) {
		header( 'HTTP/1.1 503 Service Unavailable', true, 503 );
		header( 'Content-Type: text/html; charset=utf-8' );
		header( 'Retry-After: 300' );
		header( 'X-Robots-Tag: noindex' );
		header( 'Cache-Control: no-store, max-age=0' );
	}
	// The site's own copy of the static page, then the theme's, then a plain one.
	foreach ( array( WP_CONTENT_DIR . '/murtadd-errors/503.html', get_stylesheet_directory() . '/errors/503.html' ) as $page ) {
		if ( is_readable( $page ) ) {
			readfile( $page ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			exit;
		}
	}
	echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="robots" content="noindex"><title>Back shortly</title></head><body><h1>Back shortly</h1><p>The site is being updated. Please try again in a few minutes.</p></body></html>';
	exit;
}

/**
 * Whether this request is for the login screen: wp-login.php, or a private
 * login address (the WPS Hide Login plugin's, or the theme's own), read from
 * the stored options, since the theme's own code may be the part missing.
 * The owner must be able to log in while an upload finishes.
 *
 * @return bool
 */
function murtadd_incomplete_is_login() {
	$uri = (string) ( $_SERVER['REQUEST_URI'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( 'wp-login.php' === ( $GLOBALS['pagenow'] ?? '' ) || false !== strpos( $uri, 'wp-login' ) ) {
		return true;
	}
	$slugs = array( (string) get_option( 'whl_page', '' ) );
	$stored = (array) get_option( 'murtadd_security', array() );
	if ( ! empty( $stored['login_slug'] ) ) {
		$slugs[] = (string) $stored['login_slug'];
	}
	$path = trim( (string) parse_url( $uri, PHP_URL_PATH ), '/' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$home = function_exists( 'home_url' ) ? trim( (string) parse_url( home_url( '/' ), PHP_URL_PATH ), '/' ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( '' !== $home && 0 === strpos( $path . '/', $home . '/' ) ) {
		$path = trim( substr( $path, strlen( $home ) ), '/' );
	}
	foreach ( array_filter( $slugs ) as $slug ) {
		if ( $slug === $path || isset( $_GET[ $slug ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return true;
		}
	}
	return false;
}

/**
 * Whether murtadd_incomplete_theme() sends the page to this request. The
 * login screen, AJAX, cron and the command line never get it; the admin gets
 * it only when $all is set.
 *
 * @param bool   $all  Send the page to the admin screens too.
 * @param string $sapi The PHP interface, PHP_SAPI by default.
 * @return bool
 */
function murtadd_incomplete_serves( $all = false, $sapi = PHP_SAPI ) {
	if ( wp_doing_ajax() || wp_doing_cron() || 'cli' === $sapi || murtadd_incomplete_is_login() ) {
		return false;
	}
	return $all || ! is_admin();
}

// inc/seed-data.php is loaded by the seeders, not here; it is checked with the rest.
foreach ( array_merge( murtadd_theme_files(), array( 'seed-data' ) ) as $murtadd_file ) {
	if ( ! is_readable( get_stylesheet_directory() . '/inc/' . $murtadd_file . '.php' ) ) {
		murtadd_incomplete_theme( 'inc/' . $murtadd_file . '.php is missing' );
		return;
	}
}
try {
	foreach ( murtadd_theme_files() as $murtadd_file ) {
		require_once get_stylesheet_directory() . '/inc/' . $murtadd_file . '.php';
	}
} catch ( Throwable $murtadd_error ) {
	murtadd_incomplete_theme( $murtadd_error->getMessage() . ' in ' . basename( $murtadd_error->getFile() ), true );
	return;
}
unset( $murtadd_file );

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

	// The editor canvas shows the front end's colours, faces and body type.
	add_theme_support( 'editor-styles' );
	add_editor_style( array( 'assets/css/tokens' . MURTADD_ASSET_SUFFIX . '.css', 'assets/css/editor' . MURTADD_ASSET_SUFFIX . '.css' ) );

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

	if ( ! is_front_page() && function_exists( 'murtadd_breadcrumbs_enabled' ) && murtadd_breadcrumbs_enabled() ) {
		wp_enqueue_style( 'murtadd-breadcrumbs', get_stylesheet_directory_uri() . '/assets/css/breadcrumbs' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
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
	 * bar would advertise a length they do not have and must never have. The
	 * progress bar itself is inc/reading-progress.php, which never runs on
	 * doubts either.
	 */
	if ( is_singular( 'murtadd_rebuttal' ) ) {
		wp_enqueue_style( 'murtadd-longform', get_stylesheet_directory_uri() . '/assets/css/longform' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
		wp_enqueue_script( 'murtadd-longform', get_stylesheet_directory_uri() . '/assets/js/longform' . MURTADD_ASSET_SUFFIX . '.js', array(), MURTADD_VERSION, true );
		wp_localize_script(
			'murtadd-longform',
			'murtaddLongform',
			array(
				'progress' => __( 'Reading progress', 'murtadd' ),
				'toc'      => __( 'On this page', 'murtadd' ),
				'top'      => __( 'Back to top', 'murtadd' ),
			)
		);
	}

	if ( is_singular( array( 'murtadd_doubt', 'murtadd_rebuttal' ) ) ) {
		wp_enqueue_style( 'murtadd-share', get_stylesheet_directory_uri() . '/assets/css/share' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
		wp_enqueue_script( 'murtadd-share', get_stylesheet_directory_uri() . '/assets/js/share' . MURTADD_ASSET_SUFFIX . '.js', array(), MURTADD_VERSION, true );
	}

	if ( is_singular( array( 'murtadd_doubt', 'murtadd_rebuttal' ) ) ) {
		wp_enqueue_style( 'murtadd-rail', get_stylesheet_directory_uri() . '/assets/css/rail' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
	}

	if ( ( is_home() && ! is_front_page() ) || is_singular( 'post' ) ) {
		wp_enqueue_style( 'murtadd-journal', get_stylesheet_directory_uri() . '/assets/css/journal' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
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
	if ( murtadd_settings_hook() === $hook ) {
		// Core's colour picker; the tabs, sliders and contrast check are ours.
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'murtadd-settings-tabs', get_stylesheet_directory_uri() . '/assets/js/settings-tabs' . MURTADD_ASSET_SUFFIX . '.js', array( 'jquery', 'wp-color-picker' ), MURTADD_VERSION, true );
		wp_localize_script(
			'murtadd-settings-tabs',
			'murtaddSettings',
			array(
				/* translators: %s: contrast ratio, e.g. 5.2 */
				'contrastPass' => __( '%s:1. Passes WCAG AA against white.', 'murtadd' ),
				/* translators: %s: contrast ratio, e.g. 3.1 */
				'contrastFail' => __( '%s:1. Below WCAG AA (4.5:1) against white. Saving is not blocked.', 'murtadd' ),
				'ajax'         => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'murtadd_settings_panel' ),
			)
		);

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
	// The content-gaps dashboard widget's few rules live in admin.css.
	if ( 'index.php' === $hook && current_user_can( 'edit_posts' ) ) {
		wp_enqueue_style( 'murtadd-admin', get_stylesheet_directory_uri() . '/assets/css/admin' . MURTADD_ASSET_SUFFIX . '.css', array(), MURTADD_VERSION );
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

/**
 * Respect Settings → Reading. WordPress uses front-page.php for the front page
 * whatever Reading says, so a site set to show its latest posts on the front
 * page would get the hero instead. With "Your latest posts" chosen, the front
 * page falls through to home.php, the posts index. A static front page (the
 * theme's own setup) keeps front-page.php as before.
 *
 * @param string $template The front-page template path.
 * @return string
 */
function murtadd_front_page_template( $template ) {
	return 'posts' === get_option( 'show_on_front' ) ? '' : $template;
}
add_filter( 'frontpage_template', 'murtadd_front_page_template' );
