<?php
/**
 * Unlisted entries.
 *
 * An unlisted entry stays published and opens from its own address, but
 * appears in no listing: the front page, archives, the doors, feeds, search,
 * the core and Rank Math sitemaps, the image sitemap, previous and next
 * links, related doubts, the rail, page lists and REST listings. It is marked
 * noindex, through Rank Math's robots filter when Rank Math is active.
 *
 * Adapted from the Unlist Posts & Pages plugin by Nikhil Chavan (GPLv2 or
 * later). The plugin filters posts_where, which get_posts() skips because it
 * suppresses filters; the theme builds its related doubts and its rail with
 * get_posts(), so this version filters at pre_get_posts, which every query
 * passes through. The plugin's editor box, Quick Edit, Bulk Edit, Unlisted
 * column and Unlisted view are all here.
 *
 * Editors keep seeing unlisted entries everywhere in the admin and in the
 * block editor's own requests.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

const MURTADD_UNLISTED_OPTION = 'murtadd_unlisted';

/**
 * Whether the theme's unlisting runs. It stands aside while the plugin is
 * active, so nothing is filtered twice. The plugin's own switch,
 * unlist_posts_enabled, is honoured too.
 *
 * @return bool
 */
function murtadd_unlist_enabled() {
	if ( class_exists( 'Unlist_Posts' ) ) {
		return false;
	}

	return (bool) apply_filters( 'murtadd_unlist_enabled', (bool) apply_filters( 'unlist_posts_enabled', true ) );
}

/**
 * Post types that can be unlisted: every public type, as in the plugin.
 *
 * @return string[]
 */
function murtadd_unlist_post_types() {
	return array_values( get_post_types( array( 'public' => true ), 'names' ) );
}

const MURTADD_UNLISTED_META = '_murtadd_unlisted';

/**
 * The unlisted IDs.
 *
 * Each entry carries its own flag in post meta (_murtadd_unlisted), so two
 * saves on different entries cannot erase each other. murtadd_unlisted is a
 * cache of those flags, rebuilt from them after every change and kept equal
 * to the plugin's unlist_posts, so the plugin finds the same entries if it
 * is switched back on.
 *
 * The plugin's list is read on the first call of any request, front end
 * included, and again whenever it differs from the copy last synced.
 *
 * @return int[]
 */
function murtadd_unlisted_ids() {
	murtadd_unlist_sync_from_plugin();

	return array_values( array_filter( array_map( 'intval', (array) get_option( MURTADD_UNLISTED_OPTION, array() ) ), static fn( $id ) => $id > 0 ) );
}

/**
 * The plugin's saved list, cleaned and sorted.
 *
 * @return int[]
 */
function murtadd_unlist_plugin_ids() {
	$ids = get_option( 'unlist_posts', array() );
	$ids = array_values( array_unique( array_filter( array_map( 'intval', is_array( $ids ) ? $ids : array() ), static fn( $id ) => $id > 0 ) ) );
	sort( $ids );

	return $ids;
}

/**
 * Imports the plugin's list when it is new or has changed since the last
 * sync. On the first sync, entries in the list option without a flag get
 * one. Runs once a request.
 *
 * @param bool $again Run even if this request already ran it (tests).
 * @return void
 */
function murtadd_unlist_sync_from_plugin( $again = false ) {
	static $done = false;

	if ( $done && ! $again ) {
		return;
	}

	$done = true;

	// No database (a very early call, or the test harness without one).
	if ( empty( $GLOBALS['wpdb'] ) || ! is_object( $GLOBALS['wpdb'] ) ) {
		return;
	}

	$plugin = murtadd_unlist_plugin_ids();
	$synced = get_option( 'murtadd_unlisted_synced', null );

	if ( md5( implode( ',', $plugin ) ) === $synced ) {
		return;
	}

	$ids = $plugin;

	if ( null === $synced ) {
		$ids = array_merge( $ids, array_map( 'intval', (array) get_option( MURTADD_UNLISTED_OPTION, array() ) ) );
	} else {
		// The plugin's list changed while it was in charge: it wins.
		foreach ( murtadd_unlist_flagged_ids() as $id ) {
			if ( ! in_array( $id, $plugin, true ) ) {
				delete_post_meta( $id, MURTADD_UNLISTED_META );
			}
		}
	}

	foreach ( array_unique( array_filter( $ids ) ) as $id ) {
		update_post_meta( (int) $id, MURTADD_UNLISTED_META, 1 );
	}

	murtadd_unlist_rebuild();
}

/**
 * The IDs whose flag is set, read straight from the table so no query
 * filter, this module's included, can hide one.
 *
 * @return int[]
 */
function murtadd_unlist_flagged_ids() {
	global $wpdb;

	if ( ! is_object( $wpdb ) ) {
		return array();
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$ids = array_values( array_unique( array_filter( array_map( 'intval', (array) $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s", MURTADD_UNLISTED_META ) ) ) ) ) );
	sort( $ids );

	return $ids;
}

/**
 * Rebuilds the list option and the plugin's list from the flags, and
 * records the plugin list as synced.
 *
 * @return void
 */
function murtadd_unlist_rebuild() {
	$ids = murtadd_unlist_flagged_ids();

	update_option( MURTADD_UNLISTED_OPTION, $ids );
	update_option( 'unlist_posts', $ids );
	update_option( 'murtadd_unlisted_synced', md5( implode( ',', $ids ) ) );
}

/**
 * Whether an entry is unlisted by the theme or, while it is active, by the
 * plugin. For the exclusions the theme keeps either way (the sitemaps),
 * which the plugin does not cover.
 *
 * @param int|object $post Post or ID.
 * @return bool
 */
function murtadd_is_unlisted_anywhere( $post ) {
	$id = is_object( $post ) ? (int) $post->ID : (int) $post;

	if ( murtadd_unlist_enabled() ) {
		return murtadd_is_unlisted( $id );
	}

	return $id > 0 && in_array( $id, murtadd_unlist_plugin_ids(), true );
}

/**
 * The IDs to leave out of a sitemap: the theme's, or the plugin's while the
 * plugin is active.
 *
 * @return int[]
 */
function murtadd_unlist_sitemap_ids() {
	return murtadd_unlist_enabled() ? murtadd_unlisted_ids() : murtadd_unlist_plugin_ids();
}

/**
 * Whether an entry is unlisted.
 *
 * @param int|WP_Post $post Post or ID.
 * @return bool
 */
function murtadd_is_unlisted( $post ) {
	if ( ! murtadd_unlist_enabled() ) {
		return false;
	}

	$id = is_object( $post ) ? (int) $post->ID : (int) $post;

	return $id > 0 && in_array( $id, murtadd_unlisted_ids(), true );
}

/**
 * Unlists or lists one entry.
 *
 * @param int  $post_id  Post ID.
 * @param bool $unlisted True to unlist.
 * @return void
 */
function murtadd_set_unlisted( $post_id, $unlisted ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 ) {
		return;
	}

	// One flag per entry: a save on one entry never rewrites another's.
	if ( $unlisted ) {
		update_post_meta( $post_id, MURTADD_UNLISTED_META, 1 );
	} else {
		delete_post_meta( $post_id, MURTADD_UNLISTED_META );
	}

	// Rebuilt from every flag, so the last rebuild sees both of two saves.
	murtadd_unlist_rebuild();
}

/**
 * Whether this request should see unlisted entries: the admin, and the
 * block editor's REST and admin AJAX requests from someone who can edit.
 *
 * @return bool
 */
function murtadd_unlist_bypass() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return true;
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		return false;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return true;
	}

	if ( wp_doing_ajax() ) {
		// Same host, and a path under wp-admin: the plugin's referer test.
		$referer = wp_parse_url( (string) wp_get_referer() );
		$admin   = wp_parse_url( admin_url() );

		return is_array( $referer ) && is_array( $admin ) && ! empty( $referer['host'] ) && ! empty( $admin['host'] )
			&& strtolower( $referer['host'] ) === strtolower( $admin['host'] )
			&& 0 === strpos( trailingslashit( $referer['path'] ?? '' ), trailingslashit( $admin['path'] ?? '' ) );
	}

	return false;
}

/**
 * Keeps unlisted entries out of every listing query. The query for the
 * entry at its own address is left alone.
 *
 * @param WP_Query $query Query.
 * @return void
 */
function murtadd_unlist_filter_query( $query ) {
	if ( ! murtadd_unlist_enabled() || murtadd_unlist_bypass() || $query->is_singular() ) {
		return;
	}

	$ids = murtadd_unlisted_ids();

	if ( ! $ids ) {
		return;
	}

	$in = $query->get( 'post__in' );

	if ( $in ) {
		$kept = array_values( array_diff( array_map( 'intval', (array) $in ), $ids ) );
		$query->set( 'post__in', $kept ? $kept : array( 0 ) );
		return;
	}

	$current = array_filter( array_map( 'intval', (array) $query->get( 'post__not_in' ) ) );
	$query->set( 'post__not_in', array_values( array_unique( array_merge( $current, $ids ) ) ) );
}
add_action( 'pre_get_posts', 'murtadd_unlist_filter_query', 20 );

/**
 * Keeps unlisted entries out of previous and next links.
 *
 * @param string $where WHERE clause of the adjacent-post query.
 * @return string
 */
function murtadd_unlist_adjacent( $where ) {
	$ids = murtadd_unlist_enabled() && ! murtadd_unlist_bypass() ? murtadd_unlisted_ids() : array();

	return $ids ? $where . ' AND p.ID NOT IN (' . implode( ',', array_map( 'intval', $ids ) ) . ')' : $where;
}
add_filter( 'get_next_post_where', 'murtadd_unlist_adjacent', 20 );
add_filter( 'get_previous_post_where', 'murtadd_unlist_adjacent', 20 );

/**
 * Keeps unlisted pages out of wp_list_pages().
 *
 * @param int[] $exclude IDs to exclude.
 * @return int[]
 */
function murtadd_unlist_page_lists( $exclude ) {
	return murtadd_unlist_enabled() ? array_merge( (array) $exclude, murtadd_unlisted_ids() ) : $exclude;
}
add_filter( 'wp_list_pages_excludes', 'murtadd_unlist_page_lists' );

/**
 * Whether the current view is an unlisted entry at its own address.
 *
 * @return bool
 */
function murtadd_unlist_is_current() {
	return is_singular() && murtadd_is_unlisted( get_queried_object_id() );
}

/**
 * Marks an unlisted entry noindex.
 *
 * @param array $robots Directives.
 * @return array
 */
function murtadd_unlist_robots( $robots ) {
	if ( murtadd_unlist_is_current() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'], $robots['max-image-preview'] );

		// Yoast SEO prints a robots tag of its own; the plugin empties it.
		add_filter( 'wpseo_robots_array', '__return_empty_array' );
	}

	return $robots;
}
add_filter( 'wp_robots', 'murtadd_unlist_robots', 20 );

/**
 * Rank Math prints the robots tag when it is active: noindex there.
 *
 * @param array $robots Rank Math's directives.
 * @return array
 */
function murtadd_unlist_rank_math_robots( $robots ) {
	if ( murtadd_unlist_is_current() ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'follow';
	}

	return $robots;
}
add_filter( 'rank_math/frontend/robots', 'murtadd_unlist_rank_math_robots', 20 );

/**
 * Leaves unlisted entries out of Rank Math's sitemap.
 *
 * @param array|false $url    Sitemap entry.
 * @param string      $type   Entry type.
 * @param mixed       $object The post, term or user behind it.
 * @return array|false
 */
function murtadd_unlist_rank_math_sitemap( $url, $type = '', $object = null ) {
	if ( 'post' === $type && is_object( $object ) && isset( $object->ID ) && murtadd_is_unlisted_anywhere( $object ) ) {
		return false;
	}

	return $url;
}
add_filter( 'rank_math/sitemap/entry', 'murtadd_unlist_rank_math_sitemap', 10, 3 );

/**
 * Leaves unlisted entries out of the core sitemaps. The query is a
 * WP_Query and pre_get_posts already covers it; this names the rule where
 * the sitemap builds its arguments, so it holds if that ever changes.
 *
 * @param array $args Query arguments.
 * @return array
 */
function murtadd_unlist_core_sitemap( $args ) {
	$ids = murtadd_unlist_sitemap_ids();

	if ( $ids ) {
		$args['post__not_in'] = array_values( array_unique( array_merge( array_filter( array_map( 'intval', (array) ( $args['post__not_in'] ?? array() ) ) ), $ids ) ) );
	}

	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'murtadd_unlist_core_sitemap' );

/* -------------------------------------------------------------------------
 * Admin: the editor box, Quick Edit, Bulk Edit, the column and the view.
 * ---------------------------------------------------------------------- */

/**
 * The Unlist box in the editor sidebar, on every public post type. It is a
 * classic meta box, which the block editor shows in its sidebar.
 *
 * @return void
 */
function murtadd_unlist_meta_box() {
	if ( ! murtadd_unlist_enabled() ) {
		return;
	}

	add_meta_box( 'murtadd-unlist', __( 'Unlist', 'murtadd' ), 'murtadd_unlist_render_meta_box', murtadd_unlist_post_types(), 'side', 'high' );
}
add_action( 'add_meta_boxes', 'murtadd_unlist_meta_box' );

/**
 * The box itself.
 *
 * @param WP_Post $post Entry being edited.
 * @return void
 */
function murtadd_unlist_render_meta_box( $post ) {
	wp_nonce_field( 'murtadd_unlist_' . $post->ID, 'murtadd_unlist_nonce' );
	printf(
		'<p><label><input type="checkbox" name="murtadd_unlisted" value="1"%1$s> %2$s</label></p><p class="description">%3$s</p>',
		checked( murtadd_is_unlisted( $post ), true, false ),
		esc_html__( 'Unlist this entry', 'murtadd' ),
		esc_html__( 'It opens from its own link, but no listing, search, feed or sitemap shows it, and search engines are asked not to index it.', 'murtadd' )
	);
}

/**
 * Saves the box, Quick Edit and Bulk Edit.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function murtadd_unlist_save( $post_id ) {
	if ( ! murtadd_unlist_enabled() || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Bulk Edit. Core checks the bulk-posts nonce first; it is checked again
	// so a stray request key in another save_post context changes nothing.
	if ( isset( $_REQUEST['bulk_edit'], $_REQUEST['murtadd_unlist_bulk'] ) ) {
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-posts' ) ) {
			return;
		}

		$choice = sanitize_key( wp_unslash( $_REQUEST['murtadd_unlist_bulk'] ) );

		if ( 'unlist' === $choice || 'list' === $choice ) {
			murtadd_set_unlisted( $post_id, 'unlist' === $choice );
		}

		return;
	}

	// Quick Edit.
	if ( isset( $_POST['murtadd_unlist_qe_nonce'] ) ) {
		if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['murtadd_unlist_qe_nonce'] ) ), 'murtadd_unlist_quick_edit' ) ) {
			murtadd_set_unlisted( $post_id, ! empty( $_POST['murtadd_unlisted'] ) );
		}

		return;
	}

	// The editor box.
	if ( isset( $_POST['murtadd_unlist_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['murtadd_unlist_nonce'] ) ), 'murtadd_unlist_' . $post_id ) ) {
		murtadd_set_unlisted( $post_id, ! empty( $_POST['murtadd_unlisted'] ) );
	}
}
add_action( 'save_post', 'murtadd_unlist_save' );

/**
 * Marks unlisted entries in the admin lists.
 *
 * @param string[] $states Post states.
 * @param WP_Post  $post   Post.
 * @return string[]
 */
function murtadd_unlist_post_state( $states, $post ) {
	if ( ! murtadd_unlist_view_active() && murtadd_is_unlisted( $post ) ) {
		$states['murtadd-unlisted'] = __( 'Unlisted', 'murtadd' );
	}

	return $states;
}
add_filter( 'display_post_states', 'murtadd_unlist_post_state', 10, 2 );

/**
 * The Unlisted column, Quick Edit and Bulk Edit fields, and the view, on
 * every public post type's list. WordPress prints the Quick Edit and Bulk
 * Edit fields only for a column that exists, hence the column.
 *
 * @return void
 */
function murtadd_unlist_list_table_hooks() {
	if ( ! murtadd_unlist_enabled() ) {
		return;
	}

	foreach ( murtadd_unlist_post_types() as $type ) {
		add_filter( "manage_{$type}_posts_columns", 'murtadd_unlist_column' );
		add_action( "manage_{$type}_posts_custom_column", 'murtadd_unlist_column_cell', 10, 2 );
		add_filter( "views_edit-{$type}", 'murtadd_unlist_view' );
	}
}
add_action( 'admin_init', 'murtadd_unlist_list_table_hooks' );

/**
 * The column header.
 *
 * @param array $columns Columns.
 * @return array
 */
function murtadd_unlist_column( $columns ) {
	$columns['murtadd_unlisted'] = __( 'Unlisted', 'murtadd' );
	return $columns;
}

/**
 * The column cell. Its data attribute tells Quick Edit the state.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 * @return void
 */
function murtadd_unlist_column_cell( $column, $post_id ) {
	if ( 'murtadd_unlisted' !== $column ) {
		return;
	}

	$unlisted = murtadd_is_unlisted( $post_id );

	printf(
		'<span class="murtadd-unlist-status" data-unlisted="%1$s">%2$s</span>',
		$unlisted ? '1' : '0',
		$unlisted ? esc_html__( 'Yes', 'murtadd' ) : '&mdash;'
	);
}

/**
 * The Quick Edit checkbox.
 *
 * @param string $column    Column key.
 * @param string $post_type Post type.
 * @return void
 */
function murtadd_unlist_quick_edit( $column, $post_type ) {
	if ( 'murtadd_unlisted' !== $column || ! in_array( $post_type, murtadd_unlist_post_types(), true ) ) {
		return;
	}

	echo '<fieldset class="inline-edit-col-right"><div class="inline-edit-col"><label class="alignleft">';
	wp_nonce_field( 'murtadd_unlist_quick_edit', 'murtadd_unlist_qe_nonce', false );
	printf( '<input type="checkbox" name="murtadd_unlisted" value="1"> <span class="checkbox-title">%s</span>', esc_html__( 'Unlist this entry', 'murtadd' ) );
	echo '</label></div></fieldset>';
}
add_action( 'quick_edit_custom_box', 'murtadd_unlist_quick_edit', 10, 2 );

/**
 * The Bulk Edit choice: no change, unlist or list.
 *
 * @param string $column    Column key.
 * @param string $post_type Post type.
 * @return void
 */
function murtadd_unlist_bulk_edit( $column, $post_type ) {
	if ( 'murtadd_unlisted' !== $column || ! in_array( $post_type, murtadd_unlist_post_types(), true ) ) {
		return;
	}

	printf(
		'<fieldset class="inline-edit-col-right"><div class="inline-edit-col"><label class="inline-edit-group"><span class="title">%1$s</span><select name="murtadd_unlist_bulk"><option value="">%2$s</option><option value="unlist">%3$s</option><option value="list">%4$s</option></select></label></div></fieldset>',
		esc_html__( 'Unlisted', 'murtadd' ),
		esc_html__( 'No change', 'murtadd' ),
		esc_html__( 'Unlist', 'murtadd' ),
		esc_html__( 'List', 'murtadd' )
	);
}
add_action( 'bulk_edit_custom_box', 'murtadd_unlist_bulk_edit', 10, 2 );

/**
 * Loads the Quick Edit script on post lists only.
 *
 * @param string $hook Admin page.
 * @return void
 */
function murtadd_unlist_admin_assets( $hook ) {
	if ( 'edit.php' !== $hook || ! murtadd_unlist_enabled() ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || ! in_array( $screen->post_type, murtadd_unlist_post_types(), true ) ) {
		return;
	}

	wp_enqueue_script( 'murtadd-unlist-admin', get_stylesheet_directory_uri() . '/assets/js/unlist-admin' . MURTADD_ASSET_SUFFIX . '.js', array( 'jquery', 'inline-edit-post' ), MURTADD_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'murtadd_unlist_admin_assets' );

/**
 * Whether a post list is showing the Unlisted view.
 *
 * @return bool
 */
function murtadd_unlist_view_active() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a list filter, read only.
	return is_admin() && isset( $_GET['post_status'] ) && 'unlisted' === $_GET['post_status'];
}

/**
 * Adds "Unlisted (n)" to the views above a post list.
 *
 * @param array $views Views.
 * @return array
 */
function murtadd_unlist_view( $views ) {
	$ids    = murtadd_unlisted_ids();
	$screen = get_current_screen();

	if ( ! $ids || ! $screen ) {
		return $views;
	}

	$count = count(
		get_posts(
			array(
				'post_type'      => $screen->post_type,
				'post__in'       => $ids,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		)
	);

	if ( $count > 0 ) {
		$views['murtadd-unlisted'] = sprintf(
			'<a href="%1$s"%2$s>%3$s <span class="count">(%4$d)</span></a>',
			esc_url( add_query_arg( array( 'post_type' => $screen->post_type, 'post_status' => 'unlisted' ), admin_url( 'edit.php' ) ) ),
			murtadd_unlist_view_active() ? ' class="current" aria-current="page"' : '',
			esc_html__( 'Unlisted', 'murtadd' ),
			$count
		);
	}

	return $views;
}

/**
 * Limits a post list to unlisted entries in the Unlisted view.
 *
 * @param WP_Query $query Query.
 * @return void
 */
function murtadd_unlist_view_query( $query ) {
	global $pagenow;

	if ( 'edit.php' !== $pagenow || ! $query->is_main_query() || ! murtadd_unlist_view_active() || ! murtadd_unlist_enabled() ) {
		return;
	}

	$ids = murtadd_unlisted_ids();
	$query->set( 'post__in', $ids ? $ids : array( 0 ) );
	$query->set( 'post_status', 'any' );
}
add_action( 'pre_get_posts', 'murtadd_unlist_view_query' );
