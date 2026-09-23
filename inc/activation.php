<?php
/**
 * Theme activation.
 *
 * Scaffolds the pages, menu, and Reading options the theme's templates assume,
 * so the site is coherent the moment it is switched on.
 *
 * Two rules govern everything here:
 *
 *   1. Never overwrite. A page, menu, or option that already exists is left
 *      exactly as it is. This runs on every activation, so it must be safe to
 *      run repeatedly; it only ever fills gaps.
 *   2. Never destroy. Nothing in this file deletes or unpublishes anything.
 *
 * Pages are created with placeholder text and are immediately public. Replace
 * the placeholder copy before announcing the site.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The pages the theme expects, in menu order.
 *
 * 'template' assigns a page template. 'body' is placeholder copy, shown only on
 * pages whose template actually renders the_content(); the home and start-here
 * templates do not, so their copy is a note to whoever opens them in the editor.
 *
 * @return array<string,array>
 */
function murtadd_required_pages() {
	return array(
		'home'       => array(
			'title'    => __( 'Home', 'murtadd' ),
			'body'     => __( 'This page exists so WordPress has a static front page to point at. The homepage is rendered by the theme (front-page.php); nothing written here is displayed.', 'murtadd' ),
			'template' => '',
		),
		'blog'       => array(
			'title'    => __( 'Blog', 'murtadd' ),
			'body'     => __( 'This page exists so WordPress has a posts page to point at. The blog index is rendered by the theme (home.php); nothing written here is displayed.', 'murtadd' ),
			'template' => '',
		),
		'start-here' => array(
			'title'    => __( 'Start Here', 'murtadd' ),
			'body'     => __( 'The three routes on this page are rendered by the theme (page-start-here.php); nothing written here is displayed.', 'murtadd' ),
			'template' => 'templates/page-start-here.php',
		),
		'irtidad'    => array(
			'title'    => __( 'Irtidad', 'murtadd' ),
			'body'     => __( 'Placeholder. This page carries the second route from Start Here: what irtidad actually is, and what does not take a person out of Islam. Replace this text.', 'murtadd' ),
			'template' => '',
		),
		'about'      => array(
			'title'    => __( 'About', 'murtadd' ),
			'body'     => __( 'Placeholder. Replace this text.', 'murtadd' ),
			'template' => '',
		),
		'faq'        => array(
			'title'    => __( 'FAQ', 'murtadd' ),
			'body'     => __( 'Placeholder. Replace this text.', 'murtadd' ),
			'template' => '',
		),
		'contact'    => array(
			'title'    => __( 'Contact', 'murtadd' ),
			'body'     => __( 'Placeholder. Replace this text.', 'murtadd' ),
			'template' => '',
		),
		'privacy'    => array(
			'title'    => __( 'Privacy', 'murtadd' ),
			'body'     => __( 'Placeholder. Replace this text.', 'murtadd' ),
			'template' => '',
		),
	);
}

/**
 * Create any missing page. Existing pages are never touched.
 *
 * @return string[] Slugs created on this run.
 */
function murtadd_create_missing_pages() {
	$created = array();
	$order   = 0;

	foreach ( murtadd_required_pages() as $slug => $page ) {
		++$order;

		if ( get_page_by_path( $slug ) ) {
			continue; // Already there. Leave it alone.
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_name'    => $slug,
				'post_title'   => $page['title'],
				'post_content' => $page['body'],
				'post_status'  => 'publish',
				'menu_order'   => $order,
			)
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		if ( $page['template'] ) {
			update_post_meta( $post_id, '_wp_page_template', $page['template'] );
		}

		$created[] = $slug;
	}

	return $created;
}

/**
 * Build the "Footer — Site column" menu and assign it, unless a menu is already
 * assigned to that location.
 *
 * @return bool Whether a menu was created on this run.
 */
function murtadd_create_footer_menu() {
	if ( has_nav_menu( 'footer_site' ) ) {
		return false; // The site owner has already assigned one.
	}

	$name = __( 'Site', 'murtadd' );
	$menu = wp_get_nav_menu_object( $name );

	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $name );
		if ( is_wp_error( $menu_id ) ) {
			return false;
		}

		foreach ( array( 'about', 'faq', 'contact', 'privacy' ) as $slug ) {
			$page = get_page_by_path( $slug );
			if ( ! $page ) {
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-object-id' => $page->ID,
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				)
			);
		}
	} else {
		$menu_id = $menu->term_id;
	}

	$locations                 = get_theme_mod( 'nav_menu_locations', array() );
	$locations['footer_site'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	return true;
}

/**
 * Point Reading at the scaffolded pages, without overriding a choice already made.
 *
 * @return string[] Human-readable notes on what changed.
 */
function murtadd_set_reading_options() {
	$notes = array();
	$home  = get_page_by_path( 'home' );
	$blog  = get_page_by_path( 'blog' );

	// Only claim the front page if the site is still on the default (latest posts).
	if ( 'page' !== get_option( 'show_on_front' ) && $home ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home->ID );
		$notes[] = __( 'front page set to “Home”', 'murtadd' );
	}

	if ( ! (int) get_option( 'page_for_posts' ) && $blog ) {
		update_option( 'page_for_posts', $blog->ID );
		$notes[] = __( 'posts page set to “Blog”', 'murtadd' );
	}

	return $notes;
}

/**
 * Flag the switch. Do not do the work here.
 *
 * 'after_switch_theme' fires from check_theme_switched() on 'after_setup_theme',
 * which is BEFORE 'init' — so the CPTs and taxonomies do not exist yet and a
 * rewrite flush at this point would write an incomplete rule set. All the flag
 * does is ask the real routine to run once, later in the same request.
 */
function murtadd_flag_activation() {
	update_option( 'murtadd_needs_setup', 1 );
}
add_action( 'after_switch_theme', 'murtadd_flag_activation' );

/**
 * The setup routine. Idempotent: safe to run any number of times.
 *
 * @return array Report of what was created.
 */
function murtadd_run_setup() {
	$created = murtadd_create_missing_pages();
	$menu    = murtadd_create_footer_menu();
	$reading = murtadd_set_reading_options();
	$content = murtadd_seed_content();

	// Clearing the stamp makes murtadd_maybe_flush_rewrites() (init, 99) flush
	// after every rule is registered, rather than flushing a half-built set here.
	delete_option( 'murtadd_rewrite_version' );

	$report = array(
		'pages'   => $created,
		'menu'    => $menu,
		'reading' => $reading,
		'content' => $content,
	);

	set_transient( 'murtadd_activation_report', $report, MINUTE_IN_SECONDS );

	return $report;
}

/**
 * Run on activation, on init and after the post types are registered.
 */
function murtadd_activate() {
	if ( ! get_option( 'murtadd_needs_setup' ) ) {
		return;
	}
	delete_option( 'murtadd_needs_setup' );
	murtadd_run_setup();
}
add_action( 'init', 'murtadd_activate', 90 );

/**
 * Which required pages are absent.
 *
 * @return string[] Missing slugs.
 */
function murtadd_missing_pages() {
	$missing = array();
	foreach ( array_keys( murtadd_required_pages() ) as $slug ) {
		if ( ! get_page_by_path( $slug ) ) {
			$missing[] = $slug;
		}
	}
	return $missing;
}

/**
 * Handle the "Run setup" button on the Setup tab.
 *
 * Upgrading the theme in place does not fire 'after_switch_theme', so a site
 * that was already running Murtadd never gets scaffolded. This is how the
 * routine is run without switching themes away and back.
 */
function murtadd_handle_manual_setup() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to do that.', 'murtadd' ) );
	}
	check_admin_referer( 'murtadd_run_setup' );

	murtadd_run_setup();
	flush_rewrite_rules();

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'         => 'murtadd',
				'tab'          => 'setup',
				'murtadd_done' => 1,
			),
			admin_url( 'themes.php' )
		)
	);
	exit;
}
add_action( 'admin_post_murtadd_run_setup', 'murtadd_handle_manual_setup' );

/**
 * Tell the site owner when the theme is missing pages it depends on.
 *
 * Without this the failure is silent: the "Start here" call to action in the
 * sidebar simply does not render, with nothing to explain why.
 */
function murtadd_setup_needed_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && 'appearance_page_murtadd' === $screen->id ) {
		return; // The Setup tab says it better.
	}
	$missing = murtadd_missing_pages();
	if ( ! $missing ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p><strong>%1$s</strong> %2$s <a href="%3$s">%4$s</a></p></div>',
		esc_html__( 'Murtadd is missing pages it needs.', 'murtadd' ),
		esc_html(
			sprintf(
				/* translators: %s: comma-separated list of page slugs */
				__( 'These are absent: %s. Until they exist, the "Start here" button and the footer links do not appear.', 'murtadd' ),
				implode( ', ', $missing )
			)
		),
		esc_url( add_query_arg( array( 'page' => 'murtadd', 'tab' => 'setup' ), admin_url( 'themes.php' ) ) ),
		esc_html__( 'Run setup →', 'murtadd' )
	);
}
add_action( 'admin_notices', 'murtadd_setup_needed_notice' );

/**
 * Report what activation did, once.
 */
function murtadd_activation_notice() {
	$report = get_transient( 'murtadd_activation_report' );
	if ( false === $report || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	delete_transient( 'murtadd_activation_report' );

	$lines = array();

	if ( ! empty( $report['pages'] ) ) {
		$lines[] = sprintf(
			/* translators: %s: comma-separated list of page slugs */
			__( 'Pages created: %s.', 'murtadd' ),
			implode( ', ', $report['pages'] )
		);
	}
	if ( ! empty( $report['menu'] ) ) {
		$lines[] = __( 'Footer menu created and assigned.', 'murtadd' );
	}
	if ( ! empty( $report['reading'] ) ) {
		$lines[] = sprintf(
			/* translators: %s: comma-separated list of changes */
			__( 'Reading settings: %s.', 'murtadd' ),
			implode( ', ', $report['reading'] )
		);
	}
	if ( ! empty( $report['content']['doubts'] ) || ! empty( $report['content']['rebuttals'] ) || ! empty( $report['content']['letters'] ) ) {
		$lines[] = sprintf(
			/* translators: 1: doubts, 2: rebuttals, 3: placeholder letters */
			__( 'Starter content added: %1$d doubts, %2$d rebuttals, and %3$d placeholder letters (lorem ipsum — replace or delete them).', 'murtadd' ),
			(int) $report['content']['doubts'],
			(int) $report['content']['rebuttals'],
			(int) $report['content']['letters']
		);
	}

	if ( ! $lines ) {
		$lines[] = __( 'Everything the theme needs was already in place. Nothing was changed.', 'murtadd' );
	} else {
		$lines[] = __( 'New pages carry placeholder text. Replace it before announcing the site.', 'murtadd' );
	}

	printf(
		'<div class="notice notice-success is-dismissible"><p><strong>%s</strong> %s</p></div>',
		esc_html__( 'Murtadd is set up.', 'murtadd' ),
		esc_html( implode( ' ', $lines ) )
	);
}
add_action( 'admin_notices', 'murtadd_activation_notice' );

/**
 * Migrate the Start Here page template path.
 *
 * The template moved from the theme root to /templates/ in 1.13.0. WordPress
 * stores the path in _wp_page_template, so a page assigned the old path would
 * silently fall back to the default page template and lose the three routes.
 * Runs once, then the option stops it.
 */
function murtadd_migrate_template_paths() {
	if ( get_option( 'murtadd_template_paths_migrated' ) ) {
		return;
	}
	update_option( 'murtadd_template_paths_migrated', 1 );

	$pages = get_posts(
		array(
			'post_type'   => 'page',
			'post_status' => 'any',
			'numberposts' => 50,
			'meta_key'    => '_wp_page_template',
			'meta_value'  => 'page-start-here.php',
			'fields'      => 'ids',
		)
	);

	foreach ( $pages as $page_id ) {
		update_post_meta( $page_id, '_wp_page_template', 'templates/page-start-here.php' );
	}
}
add_action( 'admin_init', 'murtadd_migrate_template_paths' );
