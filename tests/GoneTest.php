<?php
/**
 * 410 Gone.
 *
 * The danger in a 410 list is answering a real page "gone for good", which a
 * search engine acts on. So the cleaner refuses patterns that would cover the
 * whole site, the matcher is exact unless told otherwise, and the handler
 * runs after the legacy redirects and only on a 404.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class GoneTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['murtadd_options'] = array();
	}

	public function test_ships_empty() {
		$this->assertSame( '', murtadd_upkeep( 'gone_paths' ) );
		$this->assertSame( array(), murtadd_gone_patterns() );
	}

	public function test_cleaner_refuses_whole_site_and_junk() {
		$clean = murtadd_gone_clean( "/\n/*\n*\nno-slash\n/ok-path/\n/Shop/*\n/a b/\n/x*y\n/ok-path/\n javascript:alert(1)" );
		$this->assertSame( "/ok-path/\n/shop/*", $clean );
	}

	public function test_cleaner_caps_the_list() {
		$lines = array();
		for ( $i = 0; $i < 250; $i++ ) {
			$lines[] = '/spam-' . $i . '/';
		}
		$this->assertCount( 200, explode( "\n", murtadd_gone_clean( implode( "\n", $lines ) ) ) );
	}

	/** @return array */
	public static function cases() {
		return array(
			'exact, with slash'          => array( '/cheap-pills/', array( '/cheap-pills/' ), true ),
			'exact, without slash'       => array( '/cheap-pills', array( '/cheap-pills/' ), true ),
			'exact does not take child'  => array( '/cheap-pills/more/', array( '/cheap-pills/' ), false ),
			'exact ignores capitals'     => array( '/Cheap-Pills/', array( '/cheap-pills/' ), true ),
			'folder prefix, the folder'  => array( '/shop', array( '/shop/*' ), true ),
			'folder prefix, a child'     => array( '/shop/manufacturer-site/x', array( '/shop/*' ), true ),
			'folder prefix, a neighbour' => array( '/shopping/', array( '/shop/*' ), false ),
			'word prefix'                => array( '/product-similar-image/9', array( '/product-similar*' ), true ),
			'encoded path'               => array( '/caf%C3%A9-spam/', array( '/caf%c3%a9-spam/' ), true ),
			'no pattern'                 => array( '/doubts/x/', array(), false ),
		);
	}

	#[\PHPUnit\Framework\Attributes\DataProvider( 'cases' )]
	public function test_matcher( $path, $patterns, $expected ) {
		$this->assertSame( $expected, murtadd_gone_matches( $path, $patterns ) );
	}

	public function test_runs_after_the_legacy_redirects() {
		$legacy = murtadd_test_hook_priority( 'template_redirect', 'murtadd_legacy_redirects' );
		$gone   = murtadd_test_hook_priority( 'template_redirect', 'murtadd_send_gone' );
		$this->assertNotNull( $legacy );
		$this->assertNotNull( $gone );
		$this->assertGreaterThan( $legacy, $gone, 'A recovered pre-2024 URL must be redirected, never answered 410.' );
	}

	public function test_upkeep_sanitiser() {
		$out = murtadd_sanitize_upkeep( array( 'gone_paths' => "/\n/spam/", 'maintenance' => '1' ) );
		$this->assertSame( '/spam/', $out['gone_paths'] );
		$this->assertSame( 1, $out['maintenance'] );
		$this->assertSame( 0, $out['error_pages'] );
	}
}
