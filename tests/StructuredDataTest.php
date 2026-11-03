<?php
/**
 * Structured data: every schema.org type stays (owner, 2026-11-03), doubts
 * stay QAPages, the WebSite keeps its SearchAction, Articles carry an image
 * and an author with a url, and Rank Math's graph loses nothing.
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

	public function test_rebuttal_and_fatwa_articles_carry_an_image() {
		foreach ( array( 'murtadd_rebuttal', 'murtadd_fatwa' ) as $type ) {
			murtadd_test_view( array( 'post' => (object) array( 'ID' => 6, 'post_type' => $type, 'post_name' => 'x', 'post_title' => 'X' ) ) );
			$node = murtadd_structured_data_primary_node();
			$this->assertSame( 'Article', $node['@type'] );
			$this->assertNotEmpty( $node['image'], "$type image" );
			$this->assertNotEmpty( $node['author']['url'], "$type author url" );
		}
	}

	public function test_doubt_stays_a_qa_page() {
		murtadd_test_view(
			array(
				'post' => (object) array( 'ID' => 5, 'post_type' => 'murtadd_doubt', 'post_name' => 'd', 'post_title' => 'D' ),
				'meta' => array( 5 => array( '_murtadd_doubt_statement' => 'Is this true?', '_murtadd_short_response' => 'An answer.' ) ),
			)
		);
		$node = murtadd_structured_data_primary_node();
		$this->assertSame( 'QAPage', $node['@type'] );
		$this->assertSame( 'Is this true?', $node['mainEntity']['name'] );
		$this->assertNotFalse( json_decode( wp_json_encode( $node ) ) );
	}

	public function test_front_page_website_keeps_its_search_action() {
		murtadd_test_view( array( 'front' => true ) );
		$node = murtadd_structured_data_primary_node();
		$this->assertSame( array( 'WebSite', 'Organization' ), $this->types( $node ) );
		$this->assertSame( 'SearchAction', $node['@graph'][0]['potentialAction']['@type'] );
	}

	public function test_rank_math_graph_keeps_every_type() {
		$rm = array(
			'WebSite' => array(
				'@type'           => 'WebSite',
				'potentialAction' => array( '@type' => 'SearchAction', 'target' => 'x' ),
			),
			'faq'     => array( '@type' => 'FAQPage', 'mainEntity' => array() ),
			'howto'   => array( '@type' => 'HowTo' ),
		);
		$this->assertSame( $rm, murtadd_rank_math_merge( $rm, null ) );
		$this->assertSame( $rm, murtadd_rank_math_merge( $rm, array( '@type' => 'FAQPage', 'mainEntity' => array() ) ), 'An FAQPage already there is not added twice.' );
	}

	public function test_rank_math_article_author_gains_a_url() {
		$ours = array( '@type' => 'Article', 'author' => array( '@type' => 'Person', 'url' => 'https://example.org/about/' ) );
		$rm   = array(
			'person'  => array( '@type' => 'Person', '@id' => 'https://example.org/#p' ),
			'article' => array( '@type' => 'Article', 'author' => array( '@id' => 'https://example.org/#p' ) ),
		);
		$out = murtadd_rank_math_merge( $rm, $ours );
		$this->assertSame( 'https://example.org/about/', $out['person']['url'] );

		$rm  = array( 'article' => array( '@type' => 'Article', 'author' => array( '@type' => 'Person', 'name' => 'A', 'url' => 'https://x/' ) ) );
		$out = murtadd_rank_math_merge( $rm, $ours );
		$this->assertSame( 'https://x/', $out['article']['author']['url'], 'An existing url is kept.' );
	}
}
