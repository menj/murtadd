<?php
/**
 * Rank Math coexistence.
 *
 * With Rank Math active, the page used to carry two descriptions, two Open
 * Graph blocks and two JSON-LD graphs, and the docs told the owner to switch
 * the theme's off by hand. The merge rule that replaced that is pinned here:
 * a theme node is added only when Rank Math's graph has no node of its
 * family; where it has one, the theme fills empty fields and overwrites none.
 *
 * @package Murtadd\Tests
 */

// Run from the command line only: on a web server this file does nothing.
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

use PHPUnit\Framework\TestCase;

final class RankMathTest extends TestCase {

	private function rank_math_article() {
		return array(
			'publisher' => array( '@type' => 'Organization', '@id' => 'https://murtadd.org/#organization', 'name' => 'Murtadd' ),
			'WebPage'   => array( '@type' => 'WebPage', '@id' => 'https://murtadd.org/rebuttals/x/#webpage' ),
			'richSnippet' => array( '@type' => 'BlogPosting', '@id' => 'https://murtadd.org/rebuttals/x/#richSnippet', 'headline' => 'Rank Math headline' ),
		);
	}

	public function test_missing_family_is_added_with_references_to_known_nodes() {
		$qa   = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'QAPage',
			'@id'        => 'https://murtadd.org/doubts/x/#qa',
			'mainEntity' => array( '@type' => 'Question', 'name' => 'Q' ),
			'publisher'  => array( '@type' => 'Organization', '@id' => 'https://murtadd.org/#organization', 'name' => 'Murtadd', 'logo' => array() ),
		);
		$data = murtadd_rank_math_merge( $this->rank_math_article(), $qa );

		$this->assertArrayHasKey( 'murtadd_qapage', $data );
		$this->assertArrayNotHasKey( '@context', $data['murtadd_qapage'], 'A node inside a graph carries no @context.' );
		$this->assertSame( array( '@id' => 'https://murtadd.org/#organization' ), $data['murtadd_qapage']['publisher'], 'An organisation Rank Math defines is referenced, not restated.' );
	}

	public function test_present_family_gains_empty_fields_and_keeps_its_own() {
		$ours = array(
			'@context' => 'https://schema.org',
			'@type'    => 'Article',
			'headline' => 'Theme headline',
			'about'    => array( '@type' => 'Claim', 'text' => 'The claim.' ),
			'citation' => array( 'Source one' ),
		);
		$data = murtadd_rank_math_merge( $this->rank_math_article(), $ours );

		$this->assertArrayNotHasKey( 'murtadd_article', $data, 'Two Article nodes for one page is the duplicate this module exists to stop.' );
		$this->assertSame( 'Rank Math headline', $data['richSnippet']['headline'] );
		$this->assertSame( 'Claim', $data['richSnippet']['about']['@type'] );
		$this->assertSame( array( 'Source one' ), $data['richSnippet']['citation'] );
	}

	public function test_a_field_rank_math_set_is_never_overwritten() {
		$base                             = $this->rank_math_article();
		$base['richSnippet']['citation']  = array( 'Editor citation' );
		$data = murtadd_rank_math_merge( $base, array( '@type' => 'Article', 'citation' => array( 'Theme citation' ) ) );
		$this->assertSame( array( 'Editor citation' ), $data['richSnippet']['citation'] );
	}

	public function test_front_page_graph_adds_nothing_rank_math_has() {
		$base  = array(
			'WebSite'   => array( '@type' => 'WebSite', '@id' => 'https://murtadd.org/#website' ),
			'publisher' => array( '@type' => 'Organization', '@id' => 'https://murtadd.org/#organization' ),
		);
		$ours = array(
			'@context' => 'https://schema.org',
			'@graph'   => array(
				array( '@type' => 'WebSite', '@id' => 'https://murtadd.org/#website' ),
				array( '@type' => 'Organization', '@id' => 'https://murtadd.org/#organization' ),
			),
		);
		$this->assertSame( $base, murtadd_rank_math_merge( $base, $ours ) );
	}

	public function test_no_theme_node_leaves_the_graph_untouched() {
		$this->assertSame( $this->rank_math_article(), murtadd_rank_math_merge( $this->rank_math_article(), null ) );
	}

	public function test_article_subtypes_count_as_one_family() {
		foreach ( array( 'Article', 'BlogPosting', 'NewsArticle' ) as $type ) {
			$this->assertSame( 'Article', murtadd_schema_family( $type ) );
		}
		$this->assertSame( 'QAPage', murtadd_schema_family( 'QAPage' ) );
	}

	public function test_without_rank_math_nothing_stands_down() {
		$this->assertFalse( murtadd_rank_math_active(), 'The suite runs without the plugin; the theme must print its own tags.' );
		$this->assertFalse( murtadd_rank_math_schema_active() );
	}

	public function test_a_rank_math_article_without_image_or_dates_is_backfilled() {
		$data  = array(
			'richSnippet' => array( '@type' => 'Article', '@id' => 'https://murtadd.org/#richSnippet', 'headline' => 'Home' ),
			'WebPage'     => array( '@type' => 'WebPage', '@id' => 'https://murtadd.org/#webpage' ),
		);
		$image = array( '@type' => 'ImageObject', 'url' => 'https://murtadd.org/a.jpg', 'width' => 1600, 'height' => 900 );
		$dates = array( 'datePublished' => '2026-01-01T00:00:00+00:00', 'dateModified' => '2026-01-02T00:00:00+00:00' );
		$out   = murtadd_article_backfill( $data, $image, $dates );

		$this->assertSame( $image, $out['richSnippet']['image'] );
		$this->assertSame( '2026-01-01T00:00:00+00:00', $out['richSnippet']['datePublished'] );
		$this->assertSame( '2026-01-02T00:00:00+00:00', $out['richSnippet']['dateModified'] );
		$this->assertArrayNotHasKey( 'image', $out['WebPage'], 'Only Article-family nodes are touched.' );
	}

	public function test_backfill_keeps_what_rank_math_set_and_invents_no_date() {
		$data = array(
			'richSnippet' => array( '@type' => 'BlogPosting', 'image' => array( 'https://murtadd.org/rm.jpg' ), 'datePublished' => '2025-05-05' ),
			'other'       => array( '@type' => 'Article' ),
		);
		$out  = murtadd_article_backfill( $data, array( 'https://murtadd.org/x.png' ), array() );

		$this->assertSame( array( 'https://murtadd.org/rm.jpg' ), $out['richSnippet']['image'] );
		$this->assertSame( '2025-05-05', $out['richSnippet']['datePublished'] );
		$this->assertArrayNotHasKey( 'datePublished', $out['other'], 'A view with no post gets no date.' );
	}

	public function test_backfill_image_prefers_the_featured_image() {
		murtadd_test_view(
			array(
				'post'      => (object) array( 'ID' => 9, 'post_type' => 'murtadd_doubt', 'post_name' => 'x', 'post_title' => 'X' ),
				'thumbnail' => array( 'id' => 4, 'src' => array( 'https://murtadd.org/f.jpg', 1400, 800 ) ),
			)
		);
		$image = murtadd_article_backfill_image();
		$dates = murtadd_article_backfill_dates();
		murtadd_test_view( array() );

		$this->assertSame( array( '@type' => 'ImageObject', 'url' => 'https://murtadd.org/f.jpg', 'width' => 1400, 'height' => 800 ), $image );
		$this->assertArrayHasKey( 'datePublished', $dates );
		$saved = $GLOBALS['murtadd_test_request'] ?? null;
		$GLOBALS['murtadd_test_request'] = array( 'singular' => false );
		$none  = murtadd_article_backfill_dates();
		if ( null === $saved ) {
			unset( $GLOBALS['murtadd_test_request'] );
		} else {
			$GLOBALS['murtadd_test_request'] = $saved;
		}
		$this->assertSame( array(), $none, 'Off a single view, no dates.' );
	}
}
