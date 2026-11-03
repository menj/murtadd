<?php
/**
 * Structured data keeps to the types Google Search supports, each matching
 * what the page shows: no FAQPage, no QAPage for answers the site wrote
 * itself, no sitelinks SearchAction, and Articles carry headline, image,
 * both dates and an author with a url.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class StructuredDataTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['murtadd_options'] = array();
		murtadd_test_view( array() );
	}

	protected function tearDown(): void {
		murtadd_test_view( array() );
	}

	private function types( $node ) {
		$nodes = isset( $node['@graph'] ) ? $node['@graph'] : array( $node );
		return array_column( $nodes, '@type' );
	}

	public function test_faq_page_and_category_archives_carry_no_faq_page() {
		murtadd_test_view( array( 'post' => (object) array( 'ID' => 8, 'post_type' => 'page', 'post_name' => 'faq', 'post_title' => 'FAQ', 'post_content' => murtadd_seed_page_bodies()['faq'] ) ) );
		$node = murtadd_structured_data_node();
		$this->assertNotContains( 'FAQPage', $node ? $this->types( $node ) : array() );

		murtadd_test_view( array( 'term' => (object) array( 'taxonomy' => 'murtadd_doubt_category', 'slug' => 'intellectual', 'name' => 'Intellectual', 'term_id' => 3 ) ) );
		$node = murtadd_structured_data_node();
		$this->assertNotContains( 'FAQPage', $node ? $this->types( $node ) : array() );
		$this->assertFalse( function_exists( 'murtadd_faq_node' ) );
	}

	public function test_doubt_is_an_article_headed_by_its_statement() {
		murtadd_test_view(
			array(
				'post' => (object) array( 'ID' => 5, 'post_type' => 'murtadd_doubt', 'post_name' => 'd', 'post_title' => 'D' ),
				'meta' => array( 5 => array( '_murtadd_doubt_statement' => 'Is <em>this</em> true?', '_murtadd_short_response' => 'An answer.' ) ),
			)
		);
		$node = murtadd_structured_data_primary_node();
		$this->assertSame( 'Article', $node['@type'] );
		$this->assertSame( 'Is this true?', $node['headline'] );
		foreach ( array( 'image', 'datePublished', 'dateModified', 'author', 'publisher' ) as $key ) {
			$this->assertNotEmpty( $node[ $key ], "Article $key" );
		}
		$this->assertNotEmpty( $node['author']['url'], 'The author carries a url.' );
		$this->assertNotFalse( json_decode( wp_json_encode( $node ) ) );
	}

	public function test_rebuttal_and_fatwa_articles_carry_an_image() {
		foreach ( array( 'murtadd_rebuttal', 'murtadd_fatwa' ) as $type ) {
			murtadd_test_view( array( 'post' => (object) array( 'ID' => 6, 'post_type' => $type, 'post_name' => 'x', 'post_title' => 'X' ) ) );
			$node = murtadd_structured_data_primary_node();
			$this->assertSame( 'Article', $node['@type'] );
			$this->assertNotEmpty( $node['image'], "$type image" );
			$this->assertNotEmpty( $node['author']['url'], "$type author url" );
		}
	}

	public function test_front_page_website_has_no_search_action() {
		murtadd_test_view( array( 'front' => true ) );
		$node = murtadd_structured_data_primary_node();
		$this->assertSame( array( 'WebSite', 'Organization' ), $this->types( $node ) );
		$this->assertArrayNotHasKey( 'potentialAction', $node['@graph'][0] );
	}

	public function test_rank_math_graph_loses_unsupported_types() {
		$rm = array(
			'WebSite' => array(
				'@type'           => 'WebSite',
				'potentialAction' => array( '@type' => 'SearchAction', 'target' => 'x' ),
			),
			'faq'     => array( '@type' => 'FAQPage', 'mainEntity' => array() ),
			'qa'      => array( '@type' => 'QAPage' ),
		);
		$out = murtadd_rank_math_merge( $rm, null );
		$this->assertSame( array( 'WebSite' ), array_keys( $out ) );
		$this->assertArrayNotHasKey( 'potentialAction', $out['WebSite'] );
	}
}
