<?php
/**
 * Breadcrumbs: the visible trail and the BreadcrumbList that mirrors it.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class BreadcrumbsTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['murtadd_options'] = array();
		murtadd_test_view( array() );
	}

	protected function tearDown(): void {
		murtadd_test_view( array() );
	}

	private function term( $taxonomy, $slug, $name ) {
		return (object) array( 'taxonomy' => $taxonomy, 'slug' => $slug, 'name' => $name, 'term_id' => 9 );
	}

	private function post( $type, $slug, $title, $id = 5 ) {
		return (object) array( 'ID' => $id, 'post_type' => $type, 'post_name' => $slug, 'post_title' => $title, 'link' => 'https://murtadd.org/x/' . $slug . '/' );
	}

	private function labels( $trail ) {
		return array_map( static function ( $c ) { return $c[0]; }, $trail );
	}

	public function test_doubt_trail_runs_through_its_category() {
		murtadd_test_view(
			array(
				'post'  => $this->post( 'murtadd_doubt', 'is-it-true', 'Is it true?' ),
				'terms' => array( 'murtadd_doubt_category' => array( $this->term( 'murtadd_doubt_category', 'intellectual', 'Intellectual' ) ) ),
			)
		);
		$trail = murtadd_breadcrumb_trail();
		$this->assertSame( array( 'Home', 'Doubts', 'Intellectual', 'Is it true?' ), $this->labels( $trail ) );
		$this->assertSame( 'https://murtadd.org/doubt-category/intellectual/', $trail[2][1] );
		$this->assertSame( '', $trail[3][1], 'The current item has no address.' );
	}

	public function test_rebuttal_trail_runs_through_its_topic() {
		murtadd_test_view(
			array(
				'post'  => $this->post( 'murtadd_rebuttal', 'the-age-of-aisha', 'The age of Aisha' ),
				'terms' => array( 'murtadd_topic' => array( $this->term( 'murtadd_topic', 'marriage', 'Marriage' ) ) ),
			)
		);
		$this->assertSame( array( 'Home', 'Rebuttals', 'Marriage', 'The age of Aisha' ), $this->labels( murtadd_breadcrumb_trail() ) );
	}

	public function test_fatwa_trail_leaves_out_an_empty_index() {
		$view = array(
			'post'  => $this->post( 'murtadd_fatwa', 'a-ruling', 'A ruling' ),
			'terms' => array( 'murtadd_school' => array( $this->term( 'murtadd_school', 'hanafi', 'Hanafi' ) ) ),
		);
		murtadd_test_view( $view );
		$this->assertSame( array( 'Home', 'Hanafi', 'A ruling' ), $this->labels( murtadd_breadcrumb_trail() ), 'An empty fatwa index is linked from nowhere.' );

		$view['published'] = array( 'murtadd_fatwa' => 1 );
		murtadd_test_view( $view );
		$this->assertSame( array( 'Home', 'Fatwa', 'Hanafi', 'A ruling' ), $this->labels( murtadd_breadcrumb_trail() ) );
	}

	public function test_page_trail_follows_its_parents() {
		murtadd_test_view(
			array(
				'post'      => $this->post( 'page', 'child', 'Child', 7 ),
				'ancestors' => array( 4, 3 ),
				'titles'    => array( 3 => 'Grandparent', 4 => 'Parent' ),
				'links'     => array( 3 => 'https://murtadd.org/gp/', 4 => 'https://murtadd.org/gp/p/' ),
			)
		);
		$this->assertSame( array( 'Home', 'Grandparent', 'Parent', 'Child' ), $this->labels( murtadd_breadcrumb_trail() ) );
	}

	public function test_doubt_category_archive_and_front_page() {
		murtadd_test_view( array( 'term' => $this->term( 'murtadd_doubt_category', 'scriptural', 'Scriptural' ) ) );
		$this->assertSame( array( 'Home', 'Doubts', 'Scriptural' ), $this->labels( murtadd_breadcrumb_trail() ) );

		murtadd_test_view( array( 'front' => true ) );
		$this->assertSame( array(), murtadd_breadcrumb_trail() );
	}

	public function test_visible_row_marks_the_current_item_and_escapes() {
		murtadd_test_view( array( 'post' => $this->post( 'murtadd_letter', 'l', 'A <b>letter</b> & more' ) ) );
		ob_start();
		murtadd_breadcrumbs();
		$html = ob_get_clean();
		$this->assertStringContainsString( 'aria-label="Breadcrumb"', $html );
		$this->assertStringContainsString( '<a href="https://murtadd.org/letters/">Letters</a>', $html );
		$this->assertStringContainsString( '<span aria-current="page">A &lt;b&gt;letter&lt;/b&gt; &amp; more</span>', $html );
	}

	public function test_setting_switches_row_and_schema_off() {
		murtadd_test_view( array( 'post' => $this->post( 'murtadd_letter', 'l', 'Letter' ) ) );
		update_option( 'murtadd_content_display', array( 'show_breadcrumbs' => 0 ) );
		ob_start();
		murtadd_breadcrumbs();
		$this->assertSame( '', ob_get_clean() );
		$this->assertNull( murtadd_breadcrumb_node() );
		$this->assertSame( 0, murtadd_sanitize_content_display( array() )['show_breadcrumbs'] );
		$this->assertSame( 1, murtadd_sanitize_content_display( array( 'show_breadcrumbs' => '1' ) )['show_breadcrumbs'] );
	}

	public function test_breadcrumb_list_mirrors_the_trail() {
		murtadd_test_view(
			array(
				'post'  => $this->post( 'murtadd_rebuttal', 'r', 'R' ),
				'terms' => array( 'murtadd_topic' => array( $this->term( 'murtadd_topic', 't', 'T' ) ) ),
			)
		);
		$node = json_decode( wp_json_encode( murtadd_breadcrumb_node() ), true );
		$this->assertSame( 'BreadcrumbList', $node['@type'] );
		$this->assertCount( 4, $node['itemListElement'] );
		foreach ( $node['itemListElement'] as $i => $item ) {
			$this->assertSame( 'ListItem', $item['@type'] );
			$this->assertSame( $i + 1, $item['position'] );
			$this->assertNotSame( '', $item['name'] );
			$this->assertStringStartsWith( 'https://', $item['item'] );
		}
		$this->assertSame( 'https://murtadd.org/x/r/', end( $node['itemListElement'] )['item'], 'The last crumb points at the page itself.' );
	}

	public function test_breadcrumb_names_are_plain_text() {
		murtadd_test_view(
			array(
				'post'  => $this->post( 'murtadd_rebuttal', 'r', 'Don&#8217;t <em>ask</em>' ),
				'terms' => array( 'murtadd_topic' => array( $this->term( 'murtadd_topic', 't', 'Law &amp; state' ) ) ),
			)
		);
		$names = array_column( murtadd_breadcrumb_node()['itemListElement'], 'name' );
		$this->assertContains( 'Law & state', $names );
		$this->assertContains( "Don\u{2019}t ask", $names );
	}

	public function test_graph_carries_the_breadcrumbs_next_to_the_main_node() {
		murtadd_test_view(
			array(
				'post'  => $this->post( 'murtadd_doubt', 'd', 'D' ),
				'meta'  => array( 5 => array( '_murtadd_doubt_statement' => 'Q?', '_murtadd_short_response' => 'A.' ) ),
				'terms' => array( 'murtadd_doubt_category' => array( $this->term( 'murtadd_doubt_category', 'intellectual', 'Intellectual' ) ) ),
			)
		);
		$graph = murtadd_structured_data_node();
		$types = array_column( $graph['@graph'], '@type' );
		$this->assertSame( array( 'QAPage', 'BreadcrumbList' ), $types );
		foreach ( $graph['@graph'] as $node ) {
			$this->assertArrayNotHasKey( '@context', $node );
		}
	}

	public function test_rank_math_breadcrumb_list_wins() {
		$rm = array(
			'BreadcrumbList' => array( '@type' => 'BreadcrumbList', '@id' => 'https://murtadd.org/x/#breadcrumb', 'itemListElement' => array() ),
		);
		$ours = array(
			'@context' => 'https://schema.org',
			'@graph'   => array( array( '@type' => 'BreadcrumbList', 'itemListElement' => array( array( 'position' => 1 ) ) ) ),
		);
		$this->assertSame( $rm, murtadd_rank_math_merge( $rm, $ours ), 'Two BreadcrumbLists on one page is the duplicate the family rule exists to stop.' );
		$this->assertArrayHasKey( 'murtadd_breadcrumblist', murtadd_rank_math_merge( array(), $ours ) );
	}
}
