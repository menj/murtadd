<?php
/**
 * Readable search addresses: a 301 from /?s=term to /search/term/, page
 * number and other arguments kept, no redirect for an empty search, the
 * plugin's base imported, and the rewrite rules rebuilt only when the base
 * changes or the version does.
 *
 * @package Murtadd\Tests
 */

// Run from the command line only (phpdbg included): on a web server this file does nothing.
if ( ! in_array( PHP_SAPI, array( 'cli', 'phpdbg' ), true ) ) {
	exit;
}

use PHPUnit\Framework\TestCase;

/** The two WP_Rewrite members the module touches. */
final class MurtaddTestRewrite {
	public $search_base = 'search';
	public function using_permalinks() {
		return true;
	}
}

final class SearchPermalinksTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['murtadd_options']        = array();
		$GLOBALS['murtadd_view']           = array( 'search' => 'x' );
		$GLOBALS['murtadd_test_query_vars'] = array();
		$GLOBALS['murtadd_test_is_admin']  = false;
		$GLOBALS['wp_rewrite']             = new MurtaddTestRewrite();
		$_GET                              = array();
	}

	protected function tearDown(): void {
		$GLOBALS['murtadd_view']            = array();
		$GLOBALS['murtadd_test_query_vars'] = array();
		$_GET                               = array();
		unset( $GLOBALS['wp_rewrite'] );
	}

	private function redirect( array $get, array $vars ) {
		$_GET                               = $get;
		$GLOBALS['murtadd_test_query_vars'] = $vars;
		try {
			murtadd_redirect_search();
		} catch ( MurtaddTestRedirect $r ) {
			return array( $r->url, $r->status );
		}
		return null;
	}

	public function test_base_defaults_and_imports_the_plugin_base() {
		$this->assertSame( 'search', murtadd_search_settings()['base'] );
		$GLOBALS['murtadd_options']['wpseosearch_base'] = 'find';
		$this->assertSame( 'find', murtadd_search_settings()['base'] );
		$GLOBALS['murtadd_options']['murtadd_search'] = array( 'pretty' => 1, 'base' => 'Cari' );
		$this->assertSame( 'cari', murtadd_search_settings()['base'], 'The theme\'s own base wins, cleaned.' );
	}

	public function test_base_reaches_the_rewrite_rules() {
		$GLOBALS['murtadd_options']['murtadd_search'] = array( 'pretty' => 1, 'base' => 'cari' );
		murtadd_apply_search_base();
		$this->assertSame( 'cari', $GLOBALS['wp_rewrite']->search_base );
		$this->assertSame( 1, murtadd_test_hook_priority( 'init', 'murtadd_apply_search_base' ), 'Before the rules are built.' );
	}

	public function test_query_string_search_gets_a_301() {
		$this->assertSame( array( 'https://murtadd.org/search/apostasy/', 301 ), $this->redirect( array( 's' => 'apostasy' ), array( 's' => 'apostasy' ) ) );
	}

	public function test_pagination_and_arguments_survive() {
		$r = $this->redirect( array( 's' => 'apostasy', 'paged' => '2' ), array( 's' => 'apostasy', 'paged' => 2 ) );
		$this->assertSame( 'https://murtadd.org/search/apostasy/page/2/', $r[0] );

		$r = $this->redirect( array( 's' => 'two words', 'post_type' => 'murtadd_doubt' ), array( 's' => 'two words' ) );
		$this->assertStringStartsWith( 'https://murtadd.org/search/two%20words/', $r[0] );
		$this->assertStringContainsString( 'murtadd_doubt', $r[0] );
	}

	public function test_empty_search_is_not_redirected() {
		$this->assertNull( $this->redirect( array( 's' => '' ), array( 's' => '' ) ) );
		$this->assertNull( $this->redirect( array( 's' => '  ' ), array( 's' => '  ' ) ) );
	}

	public function test_readable_address_is_not_redirected_again() {
		$this->assertNull( $this->redirect( array(), array( 's' => 'apostasy' ) ) );
	}

	public function test_switched_off_means_no_redirect() {
		$GLOBALS['murtadd_options']['murtadd_search'] = array( 'pretty' => 0, 'base' => 'search' );
		$this->assertNull( $this->redirect( array( 's' => 'apostasy' ), array( 's' => 'apostasy' ) ) );
	}

	public function test_flush_only_when_the_base_changes() {
		$GLOBALS['murtadd_options']['murtadd_search']          = array( 'pretty' => 1, 'base' => 'search' );
		$GLOBALS['murtadd_options']['murtadd_rewrite_version'] = MURTADD_VERSION;
		murtadd_sanitize_search( array( 'pretty' => '1', 'base' => 'search' ) );
		$this->assertSame( MURTADD_VERSION, $GLOBALS['murtadd_options']['murtadd_rewrite_version'], 'Unchanged: no rebuild.' );
		$out = murtadd_sanitize_search( array( 'base' => 'cari', 'from_permalinks' => 1 ) );
		$this->assertArrayNotHasKey( 'murtadd_rewrite_version', $GLOBALS['murtadd_options'], 'Changed: rebuilt once.' );
		$this->assertSame( array( 'pretty' => 1, 'base' => 'cari' ), $out, 'Settings, Permalinks leaves the switch alone.' );
		$this->assertSame( array( 'pretty' => 1, 'base' => 'search' ), murtadd_sanitize_search( null ) );
	}

	public function test_array_arguments_stay_arrays() {
		$r = $this->redirect( array( 's' => 'apostasy', 'post_type' => array( 'murtadd_doubt', 'murtadd_rebuttal' ), 'junk' => array( array() ) ), array( 's' => 'apostasy' ) );
		$this->assertSame( 'https://murtadd.org/search/apostasy/?post_type%5B0%5D=murtadd_doubt&post_type%5B1%5D=murtadd_rebuttal', $r[0] );
	}

	public function test_reserved_bases_are_refused() {
		$GLOBALS['murtadd_options']['murtadd_search'] = array( 'pretty' => 1, 'base' => 'cari' );
		foreach ( array( 'doubts', 'topic', 'journal', 'guides' ) as $base ) {
			$this->assertNotSame( '', murtadd_search_base_problem( $base ), $base );
			$this->assertSame( 'cari', murtadd_sanitize_search( array( 'pretty' => '1', 'base' => $base ) )['base'], $base . ' keeps the old base.' );
		}
		$this->assertSame( 'zoek_hier', murtadd_sanitize_search( array( 'pretty' => '1', 'base' => 'Zoek_Hier!' ) )['base'], 'Cleaned with sanitize_key.' );
	}

	public function test_permalinks_save_keeps_the_switch() {
		$GLOBALS['murtadd_options']['murtadd_search'] = array( 'pretty' => 0, 'base' => 'search' );
		$out = murtadd_sanitize_search( array( 'pretty' => murtadd_search_settings()['pretty'], 'base' => 'cari', 'from_permalinks' => 1 ) );
		$this->assertSame( array( 'pretty' => 0, 'base' => 'cari' ), $out );
		$src = (string) file_get_contents( dirname( __DIR__ ) . '/inc/search-permalinks.php' );
		$this->assertStringContainsString( "'pretty'          => murtadd_search_settings()['pretty'],", $src, 'The Permalinks save merges the switch in.' );
	}

	public function test_stamp_waits_for_the_search_rule() {
		$GLOBALS['murtadd_options']['murtadd_search'] = array( 'pretty' => 1, 'base' => 'cari' );
		$GLOBALS['murtadd_options']['rewrite_rules']  = array( 'topic/(.+)/?$' => 'x' );
		$this->assertFalse( murtadd_search_rules_ready() );
		$GLOBALS['murtadd_options']['rewrite_rules']['cari/(.+)/?$'] = 'index.php?s=$matches[1]';
		$this->assertTrue( murtadd_search_rules_ready() );
		$src = (string) file_get_contents( dirname( __DIR__ ) . '/inc/taxonomy-doubt-category.php' );
		$this->assertLessThan( strpos( $src, "update_option( 'murtadd_rewrite_version'" ), strpos( $src, 'flush_rewrite_rules();' ) );
		$this->assertStringContainsString( 'murtadd_search_rules_ready()', $src );
	}

	/** Runs last: a function cannot be undeclared. */
	public function test_zz_stands_aside_for_the_plugin() {
		eval( 'function wpseosearch_base() {}' ); // phpcs:ignore Squiz.PHP.Eval.Discouraged
		$this->assertFalse( murtadd_pretty_search_enabled() );
	}
}
