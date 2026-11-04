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
		$GLOBALS['murtadd_options']       = array( 'murtadd_unlisted' => array( 7, 9 ) );
		$GLOBALS['murtadd_test_is_admin'] = false;
		$GLOBALS['murtadd_test_caps']     = array();
		$GLOBALS['murtadd_view']          = array();
		unset( $GLOBALS['murtadd_test_request'] );
	}

	public function test_imports_the_plugin_list_once() {
		$GLOBALS['murtadd_options'] = array( 'unlist_posts' => array( '7', 9, 0, '7' ) );
		$this->assertSame( array( 7, 9 ), murtadd_unlisted_ids() );
		$this->assertSame( array( 7, 9 ), $GLOBALS['murtadd_options']['murtadd_unlisted'] );
	}

	public function test_set_and_check() {
		murtadd_set_unlisted( 8, true );
		$this->assertTrue( murtadd_is_unlisted( 8 ) );
		murtadd_set_unlisted( 7, false );
		$this->assertFalse( murtadd_is_unlisted( 7 ) );
		$this->assertSame( array( 9, 8 ), murtadd_unlisted_ids() );
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
	}
}
