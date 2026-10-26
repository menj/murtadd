<?php
/**
 * FAQPage structured data: the FAQ page and the doubt category archives.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class FaqSchemaTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['murtadd_options'] = array();
		murtadd_test_view( array() );
		unset( $GLOBALS['wp_query'] );
	}

	protected function tearDown(): void {
		murtadd_test_view( array() );
		unset( $GLOBALS['wp_query'] );
	}

	private function category_view( $slug, $posts, $meta ) {
		murtadd_test_view(
			array(
				'term' => (object) array( 'taxonomy' => 'murtadd_doubt_category', 'slug' => $slug, 'name' => ucfirst( $slug ), 'term_id' => 3 ),
				'meta' => $meta,
			)
		);
		$GLOBALS['wp_query'] = (object) array( 'posts' => $posts );
	}

	private function doubt( $id ) {
		return (object) array( 'ID' => $id, 'post_type' => 'murtadd_doubt', 'post_name' => 'd' . $id, 'post_title' => 'D' . $id );
	}

	public function test_faq_page_questions_and_answers_come_from_the_seeded_copy() {
		$body = murtadd_seed_page_bodies()['faq'];
		murtadd_test_view( array( 'post' => (object) array( 'ID' => 8, 'post_type' => 'page', 'post_name' => 'faq', 'post_title' => 'FAQ', 'post_content' => $body ) ) );

		$node = json_decode( wp_json_encode( murtadd_faq_node() ), true );
		$this->assertSame( 'FAQPage', $node['@type'] );
		$this->assertSame( substr_count( $body, '<h2' ), count( $node['mainEntity'] ), 'Every question on the page is in the data.' );
		$first = $node['mainEntity'][0];
		$this->assertSame( 'Question', $first['@type'] );
		$this->assertSame( 'Who is this site for?', $first['name'] );
		$this->assertSame( 'Answer', $first['acceptedAnswer']['@type'] );
		$this->assertStringNotContainsString( '<', $first['acceptedAnswer']['text'] );
		$this->assertNotSame( '', $first['acceptedAnswer']['text'] );
	}

	public function test_answer_runs_to_the_next_question_across_paragraphs() {
		$pairs = murtadd_faq_pairs_from_html( "<p>Intro.</p><h2>One?</h2><p>A.</p>\n<!-- wp:paragraph --><p>B <a href=\"/x\">c</a>.</p><h2>Two?</h2><p>D.</p><h2>Empty?</h2>" );
		$this->assertSame( array( 'One?', 'Two?' ), array_column( $pairs, 'name' ), 'Text before the first question, and a question with no answer, are left out.' );
		$this->assertSame( 'A. B c.', $pairs[0]['acceptedAnswer']['text'] );
	}

	public function test_doubt_category_archive_answers_with_the_excerpt_shown() {
		$long = implode( ' ', array_fill( 0, 40, 'word' ) );
		$this->category_view(
			'intellectual',
			array( $this->doubt( 11 ), $this->doubt( 12 ) ),
			array(
				11 => array( '_murtadd_doubt_statement' => 'Is <em>this</em> true?', '_murtadd_short_response' => $long ),
				12 => array( '_murtadd_doubt_statement' => 'No answer yet', '_murtadd_short_response' => '' ),
			)
		);
		$node = murtadd_faq_node();
		$this->assertCount( 1, $node['mainEntity'], 'A doubt with no response is left out.' );
		$q = $node['mainEntity'][0];
		$this->assertSame( 'Is this true?', $q['name'] );
		$this->assertSame( wp_trim_words( $long, 24 ), $q['acceptedAnswer']['text'], 'The data says what the archive shows, no more.' );
		$this->assertSame( 'https://murtadd.org/x/d11/', $q['acceptedAnswer']['url'] );
		$this->assertSame( 'https://murtadd.org/doubt-category/intellectual/#faq', $node['@id'] );
	}

	public function test_emotional_and_identity_categories_get_no_faq() {
		foreach ( array( 'emotional', 'identity' ) as $slug ) {
			$this->category_view( $slug, array( $this->doubt( 21 ) ), array( 21 => array( '_murtadd_doubt_statement' => 'Q', '_murtadd_short_response' => 'A' ) ) );
			$this->assertNull( murtadd_faq_node(), "The $slug category is written care-first and stays out of answer boxes." );
		}
	}

	public function test_other_views_get_none_and_graph_includes_it() {
		murtadd_test_view( array( 'post' => (object) array( 'ID' => 9, 'post_type' => 'page', 'post_name' => 'about', 'post_title' => 'About', 'post_content' => '<h2>Q?</h2><p>A.</p>' ) ) );
		$this->assertNull( murtadd_faq_node(), 'Only the FAQ page is read as questions.' );

		$this->category_view( 'scriptural', array( $this->doubt( 31 ) ), array( 31 => array( '_murtadd_doubt_statement' => 'Q', '_murtadd_short_response' => 'A' ) ) );
		$graph = murtadd_structured_data_node();
		$this->assertSame( array( 'BreadcrumbList', 'FAQPage' ), array_column( $graph['@graph'], '@type' ) );
		$this->assertNotFalse( json_decode( wp_json_encode( $graph ) ), 'The graph encodes as valid JSON.' );
	}

	public function test_rank_math_faq_page_wins() {
		$rm   = array( 'faq' => array( '@type' => 'FAQPage', 'mainEntity' => array() ) );
		$ours = array( '@type' => 'FAQPage', 'mainEntity' => array( array( '@type' => 'Question' ) ) );
		$this->assertSame( $rm, murtadd_rank_math_merge( $rm, $ours ) );
		$this->assertArrayHasKey( 'murtadd_faqpage', murtadd_rank_math_merge( array(), $ours ) );
	}
}
