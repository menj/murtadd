<?php
/**
 * Unlisted entries: out of every listing, previous and next links, page
 * lists and the sitemaps; untouched at their own address and in the admin;
 * noindex, through Rank Math too. The plugin's list is imported, and the
 * theme stands aside while the plugin runs.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

/** The few WP_Query methods the filter uses. */
final class MurtaddTestQuery {
	public $vars;
	public $singular;
	public function __construct( $vars = array(), $singular = false ) {
		$this->vars     = $vars;
		$this->singular = $singular;
	}
	public function get( $key ) {
		return $this->vars[ $key ] ?? '';
	}
	public function set( $key, $value ) {
		$this->vars[ $key ] = $value;
	}
	public function is_singular() {
		return $this->singular;
	}
	public function is_main_query() {
		return true;
	}
}

final class UnlistTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['wpdb']                  = new MurtaddTestWpdb();
		$GLOBALS['murtadd_written_meta']  = array( 7 => array( '_murtadd_unlisted' => 1 ), 9 => array( '_murtadd_unlisted' => 1 ) );
		$GLOBALS['murtadd_options']       = array( 'murtadd_unlisted' => array( 7, 9 ), 'unlist_posts' => array( 7, 9 ), 'murtadd_unlisted_synced' => md5( '7,9' ) );
		$GLOBALS['murtadd_test_is_admin'] = false;
		$GLOBALS['murtadd_test_caps']     = array();
		$GLOBALS['murtadd_view']          = array();
		unset( $GLOBALS['murtadd_test_request'] );
	}

	protected function tearDown(): void {
		unset( $GLOBALS['wpdb'] );
	}

	public function test_imports_the_plugin_list_on_any_request() {
		$GLOBALS['murtadd_written_meta'] = array();
		$GLOBALS['murtadd_options']      = array( 'unlist_posts' => array( '9', 7, 0, '7' ) );
		murtadd_unlist_sync_from_plugin( true );
		$this->assertSame( array( 7, 9 ), murtadd_unlisted_ids() );
		$this->assertSame( 1, $GLOBALS['murtadd_written_meta'][7]['_murtadd_unlisted'], 'One flag per entry.' );
		$this->assertSame( array( 7, 9 ), $GLOBALS['murtadd_options']['unlist_posts'], 'The plugin list is kept in step.' );
	}

	public function test_a_changed_plugin_list_is_synced_again() {
		$GLOBALS['murtadd_options']['unlist_posts'] = array( 9, 20 );
		murtadd_unlist_sync_from_plugin( true );
		$this->assertSame( array( 9, 20 ), murtadd_unlisted_ids() );
		$this->assertArrayNotHasKey( '_murtadd_unlisted', $GLOBALS['murtadd_written_meta'][7] );
	}

	public function test_concurrent_saves_keep_each_other() {
		murtadd_set_unlisted( 11, true );
		// A second request that read the list before the first save landed.
		$GLOBALS['murtadd_options']['murtadd_unlisted'] = array( 7, 9 );
		murtadd_set_unlisted( 12, true );
		$this->assertSame( array( 7, 9, 11, 12 ), $GLOBALS['murtadd_options']['murtadd_unlisted'] );
		murtadd_set_unlisted( 7, false );
		$this->assertFalse( murtadd_is_unlisted( 7 ) );
		$this->assertSame( array( 9, 11, 12 ), $GLOBALS['murtadd_options']['unlist_posts'] );
	}

	public function test_listing_queries_leave_them_out() {
		$q = new MurtaddTestQuery( array( 'post__not_in' => array( 3 ) ) );
		murtadd_unlist_filter_query( $q );
		$this->assertSame( array( 3, 7, 9 ), $q->get( 'post__not_in' ) );

		$q = new MurtaddTestQuery( array( 'post__in' => array( 7, 8 ) ) );
		murtadd_unlist_filter_query( $q );
		$this->assertSame( array( 8 ), $q->get( 'post__in' ) );

		$q = new MurtaddTestQuery( array( 'post__in' => array( 9 ) ) );
		murtadd_unlist_filter_query( $q );
		$this->assertSame( array( 0 ), $q->get( 'post__in' ), 'Only unlisted IDs asked for: nothing comes back.' );
	}

	public function test_own_address_and_admin_are_untouched() {
		$q = new MurtaddTestQuery( array(), true );
		murtadd_unlist_filter_query( $q );
		$this->assertSame( '', $q->get( 'post__not_in' ) );

		$GLOBALS['murtadd_test_is_admin'] = true;
		$q = new MurtaddTestQuery();
		murtadd_unlist_filter_query( $q );
		$this->assertSame( '', $q->get( 'post__not_in' ) );
	}

	/** A visitor's listing, REST included, is filtered; REST_REQUEST is not defined here so other tests are unaffected. */
	public function test_visitor_listing_is_filtered() {
		$q = new MurtaddTestQuery();
		murtadd_unlist_filter_query( $q );
		$this->assertSame( array( 7, 9 ), $q->get( 'post__not_in' ) );
		$this->assertFalse( murtadd_unlist_bypass() );
	}

	public function test_adjacent_page_lists_and_sitemaps() {
		$this->assertSame( "x AND p.ID NOT IN (7,9)", murtadd_unlist_adjacent( 'x' ) );
		$this->assertSame( array( 1, 7, 9 ), murtadd_unlist_page_lists( array( 1 ) ) );
		$this->assertSame( array( 2, 7, 9 ), murtadd_unlist_core_sitemap( array( 'post__not_in' => array( 2 ) ) )['post__not_in'] );
		$post = (object) array( 'ID' => 7 );
		$this->assertFalse( murtadd_unlist_rank_math_sitemap( array( 'loc' => 'x' ), 'post', $post ) );
		$this->assertSame( array( 'loc' => 'x' ), murtadd_unlist_rank_math_sitemap( array( 'loc' => 'x' ), 'post', (object) array( 'ID' => 8 ) ) );
	}

	public function test_noindex_on_the_entry_itself() {
		$GLOBALS['murtadd_test_request'] = array( 'singular' => true, 'queried' => 7 );
		$robots = murtadd_unlist_robots( array( 'max-image-preview' => 'large' ) );
		$this->assertTrue( $robots['noindex'] );
		$this->assertArrayNotHasKey( 'max-image-preview', $robots );
		$this->assertSame( 'noindex', murtadd_unlist_rank_math_robots( array( 'index' => 'index' ) )['index'] );
		$this->assertNotNull( murtadd_test_hook_priority( 'wpseo_robots_array', '__return_empty_array' ), 'Yoast\'s robots tag is emptied.' );

		$GLOBALS['murtadd_test_request'] = array( 'singular' => true, 'queried' => 8 );
		$this->assertArrayNotHasKey( 'noindex', murtadd_unlist_robots( array() ) );
		$this->assertSame( 'index', murtadd_unlist_rank_math_robots( array( 'index' => 'index' ) )['index'] );
	}

	public function test_hooks_are_in_place() {
		foreach ( array(
			array( 'pre_get_posts', 'murtadd_unlist_filter_query' ),
			array( 'get_next_post_where', 'murtadd_unlist_adjacent' ),
			array( 'get_previous_post_where', 'murtadd_unlist_adjacent' ),
			array( 'wp_robots', 'murtadd_unlist_robots' ),
			array( 'rank_math/frontend/robots', 'murtadd_unlist_rank_math_robots' ),
			array( 'rank_math/sitemap/entry', 'murtadd_unlist_rank_math_sitemap' ),
			array( 'wp_sitemaps_posts_query_args', 'murtadd_unlist_core_sitemap' ),
			array( 'quick_edit_custom_box', 'murtadd_unlist_quick_edit' ),
			array( 'bulk_edit_custom_box', 'murtadd_unlist_bulk_edit' ),
		) as $hook ) {
			$this->assertNotNull( murtadd_test_hook_priority( $hook[0], $hook[1] ), $hook[0] );
		}
	}

	public function test_quick_edit_script_ships_minified() {
		foreach ( array( 'unlist-admin.js', 'unlist-admin.min.js' ) as $file ) {
			$js = (string) file_get_contents( dirname( __DIR__ ) . '/assets/js/' . $file );
			$this->assertStringContainsString( 'murtadd-unlist-status', $js, $file );
		}
	}

	/** Runs last: a class cannot be undeclared. */
	public function test_zz_stands_aside_for_the_plugin() {
		eval( 'class Unlist_Posts {}' ); // phpcs:ignore Squiz.PHP.Eval.Discouraged
		$this->assertFalse( murtadd_unlist_enabled() );
		$this->assertFalse( murtadd_is_unlisted( 7 ) );
		$this->assertSame( 'x', murtadd_unlist_adjacent( 'x' ) );
		$this->assertTrue( murtadd_is_unlisted_anywhere( 7 ), 'Sitemap exclusions read the plugin list.' );
		$this->assertFalse( murtadd_unlist_rank_math_sitemap( array( 'loc' => 'x' ), 'post', (object) array( 'ID' => 9 ) ) );
		$this->assertSame( array( 7, 9 ), murtadd_unlist_core_sitemap( array() )['post__not_in'] );
	}
}
