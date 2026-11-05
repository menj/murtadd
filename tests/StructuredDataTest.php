<?php
/**
 * Structured data: every schema.org type stays (owner, 2026-11-03), doubts
 * stay QAPages, the WebSite keeps its SearchAction, Articles carry an image
 * and an author with a url, and Rank Math's graph loses nothing.
 *
 * @package Murtadd\Tests
 */

// Run from the command line only: on a web server this file does nothing.
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

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
			$this->assertSame( array( '@id' => 'https://murtadd.org/#author' ), $node['author'], "$type author" );
		}
	}

	/** One author Person on every graph, and every author and page points to it. */
	public function test_every_graph_names_one_author() {
		$views = array(
			array( 'post' => (object) array( 'ID' => 6, 'post_type' => 'murtadd_rebuttal', 'post_name' => 'x', 'post_title' => 'X' ) ),
			array( 'post' => (object) array( 'ID' => 6, 'post_type' => 'murtadd_fatwa', 'post_name' => 'x', 'post_title' => 'X' ) ),
			array( 'post' => (object) array( 'ID' => 6, 'post_type' => 'post', 'post_name' => 'x', 'post_title' => 'X' ) ),
		);
		foreach ( $views as $view ) {
			murtadd_test_view( $view );
			$graph   = murtadd_structured_data_node();
			$nodes   = isset( $graph['@graph'] ) ? $graph['@graph'] : array( $graph );
			$persons = array_values( array_filter( $nodes, static function ( $n ) {
				return 'Person' === ( $n['@type'] ?? '' );
			} ) );
			$this->assertCount( 1, $persons, $view['post']->post_type );
			$this->assertSame( 'https://murtadd.org/#author', $persons[0]['@id'] );
			$this->assertSame( 'Mohd Elfie Nieshaem Juferi', $persons[0]['name'] );
			$this->assertSame( 'https://menj.bio', $persons[0]['url'] );
			$this->assertContains( 'https://menj.bio', $persons[0]['sameAs'] );
			$authors = array();
			$walk = static function ( $n ) use ( &$walk, &$authors ) {
				foreach ( (array) $n as $k => $v ) {
					if ( 'author' === $k ) {
						$authors[] = $v;
					} elseif ( is_array( $v ) ) {
						$walk( $v );
					}
				}
			};
			$walk( $nodes );
			$this->assertNotEmpty( $authors );
			foreach ( $authors as $a ) {
				$this->assertSame( array( '@id' => 'https://murtadd.org/#author' ), $a, 'Every author points to #author.' );
			}
			foreach ( $nodes as $n ) {
				if ( array_intersect( (array) ( $n['@type'] ?? array() ), murtadd_schema_authored_types() ) ) {
					$this->assertSame( array( '@id' => 'https://murtadd.org/#author' ), $n['author'], ( $n['@id'] ?? '' ) . ' author' );
				}
			}
		}
	}

	/** Rank Math's own author Person is folded into the one author. */
	public function test_rank_math_author_becomes_the_one_author() {
		$data = array(
			'richSnippet' => array( '@type' => 'Article', '@id' => 'https://murtadd.org/x/#richSnippet', 'author' => array( '@id' => 'https://murtadd.org/author/editor/' ) ),
			'ProfilePage' => array( '@type' => 'Person', '@id' => 'https://murtadd.org/author/editor/', 'name' => 'editor', 'sameAs' => array( 'https://example.org/editor' ) ),
			'publisher'   => array( '@type' => 'Organization', '@id' => 'https://murtadd.org/#organization' ),
		);
		$out = murtadd_schema_one_author( $data );
		$this->assertArrayNotHasKey( 'ProfilePage', $out );
		$this->assertSame( array( '@id' => 'https://murtadd.org/#author' ), $out['richSnippet']['author'] );
		$this->assertSame( array( 'https://example.org/editor', 'https://menj.bio' ), $out['murtadd_author']['sameAs'] );
		$this->assertSame( 'https://murtadd.org/#organization', $out['publisher']['@id'], 'The publisher stays the site.' );
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
