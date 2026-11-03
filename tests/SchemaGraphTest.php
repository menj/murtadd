<?php
/**
 * The full graph (inc/schema-graph.php): sample views rendered end to end.
 * Each graph encodes as JSON, carries one node per @id, and has the
 * properties its types need. Doubts stay QAPages, rebuttals keep their
 * Claim, and every page has the sidebar and footer it shows.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class SchemaGraphTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['murtadd_options']     = array();
		$GLOBALS['murtadd_test_fields'] = array();
		murtadd_test_view( array() );
	}

	protected function tearDown(): void {
		$GLOBALS['murtadd_test_fields'] = array();
		murtadd_test_view( array() );
	}

	private function term( $taxonomy, $slug, $name ) {
		return (object) array( 'term_id' => 70, 'taxonomy' => $taxonomy, 'slug' => $slug, 'name' => $name, 'description' => '' );
	}

	private function post( $type, $id, $slug, $title ) {
		return (object) array( 'ID' => $id, 'post_type' => $type, 'post_name' => $slug, 'post_title' => $title );
	}

	/** Render the current view's graph and check what every graph must hold. */
	private function graph() {
		$data = murtadd_structured_data_node();
		$this->assertIsArray( $data );
		$json = wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		$back = json_decode( $json, true );
		$this->assertIsArray( $back, 'The graph encodes as valid JSON.' );
		$this->assertSame( 'https://schema.org', $back['@context'] );

		$nodes = isset( $back['@graph'] ) ? $back['@graph'] : array( $back );
		$ids   = array();
		foreach ( $nodes as $node ) {
			$this->assertNotEmpty( $node['@type'], 'Every node has a type.' );
			if ( isset( $node['@id'] ) ) {
				$this->assertArrayNotHasKey( $node['@id'], $ids, 'Duplicate @id ' . $node['@id'] );
				$ids[ $node['@id'] ] = true;
				$this->assertMatchesRegularExpression( '#^https://murtadd\.org/[^\s]*\#[a-z0-9-]+$#', $node['@id'], 'An @id is a url and a fragment.' );
			}
			$this->required( $node );
		}
		return $nodes;
	}

	private function required( $node ) {
		$need = array(
			'WebPage'           => array( 'url', 'inLanguage', 'isPartOf' ),
			'CollectionPage'    => array( 'url', 'inLanguage', 'isPartOf' ),
			'SearchResultsPage' => array( 'url', 'inLanguage' ),
			'AboutPage'         => array( 'url', 'inLanguage' ),
			'ContactPage'       => array( 'url', 'inLanguage' ),
			'QAPage'            => array( 'mainEntity', 'url', 'inLanguage' ),
			'Article'           => array( 'headline', 'datePublished', 'author', 'publisher', 'inLanguage' ),
			'WPSideBar'         => array( 'cssSelector' ),
			'WPFooter'          => array( 'cssSelector' ),
			'Quotation'         => array( 'text', 'inLanguage' ),
			'Book'              => array( 'name' ),
			'DefinedTermSet'    => array( 'name', 'hasDefinedTerm' ),
			'ImageObject'       => array( 'contentUrl' ),
		);
		foreach ( $need[ $node['@type'] ] ?? array() as $prop ) {
			$this->assertNotEmpty( $node[ $prop ] ?? null, $node['@type'] . " needs $prop" );
		}
	}

	private function by_type( $nodes, $type ) {
		return array_values( array_filter( $nodes, static fn( $n ) => $type === $n['@type'] ) );
	}

	public function test_doubt_qa_page_has_about_mentions_citation_and_frame() {
		murtadd_test_view(
			array(
				'post'      => $this->post( 'murtadd_doubt', 5, 'd', 'D' ),
				'meta'      => array(
					5 => array(
						'_murtadd_doubt_statement'    => 'Is irtidad punished?',
						'_murtadd_short_response'     => 'Irtidad, in the classical books, is a legal term.',
						'_murtadd_external_deep_link' => 'https://example.org/case/',
					),
				),
				'terms'     => array( 'murtadd_topic' => array( $this->term( 'murtadd_topic', 'apostasy', 'Apostasy' ) ) ),
				'thumbnail' => array( 'id' => 40, 'src' => array( 'https://murtadd.org/u/hero.jpg', 1200, 630 ), 'caption' => 'Photo: A. Writer' ),
			)
		);
		$nodes = $this->graph();
		$qa    = $this->by_type( $nodes, 'QAPage' );
		$this->assertCount( 1, $qa, 'A doubt stays one QAPage.' );
		$qa = $qa[0];
		$this->assertSame( 'Apostasy', $qa['about'][0]['name'] );
		$this->assertSame( 'https://example.org/case/', $qa['citation'][0]['url'] );
		$this->assertContains( array( '@id' => 'https://murtadd.org/#glossary-irtidad' ), $qa['mentions'] );
		$this->assertSame( array( '@id' => 'https://murtadd.org/d/#primaryimage' ), $qa['primaryImageOfPage'] );
		$this->assertSame( 'SpeakableSpecification', $qa['speakable']['@type'] );
		$this->assertEmpty( $this->by_type( $nodes, 'WebPage' ), 'No second page node beside the QAPage.' );

		$image = $this->by_type( $nodes, 'ImageObject' )[0];
		$this->assertSame( 1200, $image['width'] );
		$this->assertSame( 'image/jpeg', $image['encodingFormat'] );
		$this->assertSame( 'Photo: A. Writer', $image['caption'] );

		$set = $this->by_type( $nodes, 'DefinedTermSet' )[0];
		$this->assertSame( 'DefinedTerm', $set['hasDefinedTerm'][0]['@type'] );
		$this->assertSame( 'irtidad', $set['hasDefinedTerm'][0]['name'] );
	}

	public function test_doubt_rail_is_a_sidebar_with_the_verse() {
		murtadd_test_view(
			array(
				'post' => $this->post( 'murtadd_doubt', 5, 'd', 'D' ),
				'meta' => array( 5 => array( '_murtadd_doubt_statement' => 'Q?', '_murtadd_short_response' => 'A.' ) ),
			)
		);
		$nodes = $this->graph();
		$bars  = $this->by_type( $nodes, 'WPSideBar' );
		$this->assertSame( array( '.murtadd-sidebar', '.murtadd-rail' ), array_column( $bars, 'cssSelector' ) );
		$this->assertSame( 'SiteNavigationElement', $bars[0]['hasPart'][0]['@type'] );
		$this->assertContains( 'Rebuttals', array_column( $bars[0]['hasPart'][0]['hasPart'], 'name' ) );

		$verse = $this->by_type( $nodes, 'Quotation' )[0];
		$this->assertSame( array( '@id' => $verse['@id'] ), $bars[1]['hasPart'][0] );
		$this->assertSame( 'Let there be no compulsion in religion.', $verse['text'] );
		$this->assertSame( 'Chapter', $verse['isPartOf']['@type'] );
		$this->assertSame( 2, $verse['isPartOf']['position'] );
		$this->assertSame( array( '@id' => 'https://murtadd.org/#quran' ), $verse['isPartOf']['isPartOf'] );
		$this->assertSame( 'Scripture', $this->by_type( $nodes, 'Book' )[0]['genre'] );
	}

	public function test_rail_verse_switched_off_is_not_marked() {
		$GLOBALS['murtadd_options']['murtadd_content_display'] = array( 'show_scripture' => 0 );
		murtadd_test_view(
			array(
				'post' => $this->post( 'murtadd_doubt', 5, 'd', 'D' ),
				'meta' => array( 5 => array( '_murtadd_doubt_statement' => 'Q?', '_murtadd_short_response' => 'A.' ) ),
			)
		);
		$nodes = $this->graph();
		$this->assertEmpty( $this->by_type( $nodes, 'Quotation' ) );
		$this->assertEmpty( $this->by_type( $nodes, 'Book' ) );
	}

	public function test_rebuttal_keeps_its_claim_and_cites_its_sources() {
		$GLOBALS['murtadd_test_fields'][6]['post_content'] = '<p class="murtadd-ayah" lang="ar" dir="rtl">لَا إِكْرَاهَ فِي الدِّينِ</p><p>Body text here.</p>';
		murtadd_test_view(
			array(
				'post'  => $this->post( 'murtadd_rebuttal', 6, 'r', 'R' ),
				'meta'  => array(
					6 => array(
						'_murtadd_claim'   => 'The claim as circulated.',
						'_murtadd_sources' => array(
							array( 'citation_text' => 'Ibn Qudamah, al-Mughni', 'url' => 'https://example.org/mughni' ),
							array( 'citation_text' => 'A book with no link' ),
						),
					),
				),
				'terms' => array( 'murtadd_topic' => array( $this->term( 'murtadd_topic', 'law', 'Law' ) ) ),
			)
		);
		$nodes   = $this->graph();
		$article = $this->by_type( $nodes, 'Article' )[0];
		$this->assertSame( 'Claim', $article['about']['@type'] );
		$this->assertSame( array( '@id' => 'https://murtadd.org/r/#article' ), $article['about']['appearance'] );
		$this->assertArrayNotHasKey( 'firstAppearance', $article['about'], 'No origin is linked, so none is marked.' );
		$this->assertSame( 'https://example.org/mughni', $article['citation'][0]['url'] );
		$this->assertArrayNotHasKey( 'url', $article['citation'][1] );
		$this->assertSame( 'Law', $article['articleSection'] );
		$this->assertTrue( $article['isAccessibleForFree'] );
		$this->assertGreaterThan( 0, $article['wordCount'] );
		$this->assertEmpty( $this->by_type( $nodes, 'ClaimReview' ), 'No rating is shown, so no ClaimReview.' );

		$verse = array_values( array_filter( $this->by_type( $nodes, 'Quotation' ), static fn( $q ) => 'ar' === $q['inLanguage'] && false !== strpos( $q['@id'], '#verse-' ) ) );
		$this->assertCount( 1, $verse, 'A verse marked in the content is a Quotation.' );
		$this->assertContains( array( '@id' => $verse[0]['@id'] ), $article['mentions'] );

		$page = $this->by_type( $nodes, 'WebPage' )[0];
		$this->assertSame( array( '@id' => 'https://murtadd.org/r/#article' ), $page['mainEntity'] );
	}

	public function test_fatwa_is_about_scholar_and_school() {
		murtadd_test_view(
			array(
				'post'  => $this->post( 'murtadd_fatwa', 7, 'f', 'F' ),
				'meta'  => array(
					7 => array(
						'_murtadd_scholar_or_body'  => 'Al-Azhar',
						'_murtadd_position_summary' => 'In our own words.',
						'_murtadd_citation_link'    => 'https://example.org/fatwa',
					),
				),
				'terms' => array( 'murtadd_school' => array( $this->term( 'murtadd_school', 'hanafi', 'Hanafi' ) ) ),
			)
		);
		$article = $this->by_type( $this->graph(), 'Article' )[0];
		$this->assertSame( 'Al-Azhar', $article['about'][0], 'The scholar stays the first subject.' );
		$this->assertSame( 'Hanafi', $article['about'][1]['name'] );
		$this->assertSame( 'In our own words.', $article['abstract'] );
		$this->assertSame( 'https://example.org/fatwa', $article['citation'] );
		$this->assertSame( 'en-US', $article['inLanguage'] );
	}

	public function test_letter_is_an_open_letter_article() {
		murtadd_test_view(
			array(
				'post' => $this->post( 'murtadd_letter', 8, 'l', 'L' ),
				'meta' => array(
					8 => array(
						'_murtadd_letter_date' => '2004-05-01',
						'_murtadd_letter_url'  => 'https://example.org/letter',
					),
				),
			)
		);
		$article = $this->by_type( $this->graph(), 'Article' )[0];
		$this->assertSame( 'open letter', $article['genre'] );
		$this->assertSame( '2004-05-01', $article['dateCreated'] );
		$this->assertSame( 'https://example.org/letter', $article['isBasedOn'] );
	}

	public function test_school_page_is_a_collection_about_its_school() {
		murtadd_test_view( array( 'term' => $this->term( 'murtadd_school', 'hanafi', 'Hanafi' ) ) );
		$page = $this->by_type( $this->graph(), 'CollectionPage' )[0];
		$this->assertSame( 'Hanafi', $page['about']['name'] );
		$this->assertSame( 'https://murtadd.org/school/hanafi/', $page['url'] );
	}

	public function test_page_subtypes() {
		$cases = array(
			array( array( 'archive' => 'murtadd_doubt' ), 'CollectionPage' ),
			array( array( 'search' => true ), 'SearchResultsPage' ),
			array( array( 'post' => $this->post( 'page', 9, 'about', 'About' ) ), 'AboutPage' ),
			array( array( 'post' => $this->post( 'page', 10, 'contact', 'Contact' ) ), 'ContactPage' ),
			array( array( 'front' => true ), 'WebPage' ),
		);
		foreach ( $cases as $case ) {
			murtadd_test_view( $case[0] );
			$this->assertCount( 1, $this->by_type( $this->graph(), $case[1] ), $case[1] );
		}
	}

	public function test_front_page_keeps_website_and_has_the_frame() {
		murtadd_test_view( array( 'front' => true ) );
		$types = array_column( $this->graph(), '@type' );
		foreach ( array( 'WebSite', 'Organization', 'WebPage', 'WPSideBar', 'WPFooter' ) as $type ) {
			$this->assertContains( $type, $types );
		}
		$this->assertNotContains( 'WPHeader', $types, 'The theme has no header element.' );
	}

	public function test_blog_verse_box_carries_its_translation() {
		murtadd_test_view( array( 'home' => true ) );
		$nodes = $this->graph();
		$quotes = $this->by_type( $nodes, 'Quotation' );
		$this->assertCount( 2, $quotes );
		$this->assertSame( 'ar', $quotes[0]['inLanguage'] );
		$this->assertSame( array( '@id' => $quotes[0]['@id'] ), $quotes[1]['translationOfWork'] );
	}

	public function test_footer_lists_the_fallback_links() {
		$GLOBALS['murtadd_posts']['page']['about'] = (object) array( 'ID' => 9, 'post_type' => 'page', 'post_name' => 'about', 'post_status' => 'publish' );
		murtadd_test_view( array( 'archive' => 'murtadd_letter' ) );
		$footer = $this->by_type( $this->graph(), 'WPFooter' )[0];
		$this->assertSame( 'SiteNavigationElement', $footer['hasPart'][0]['@type'] );
		unset( $GLOBALS['murtadd_posts']['page']['about'] );
	}

	public function test_quran_reference_parsing() {
		$this->assertSame( array( 'surah' => 2, 'ayah' => '256' ), murtadd_parse_quran_ref( 'Qur’an 2:256' ) );
		$this->assertSame( array( 'surah' => 4, 'ayah' => '34-35' ), murtadd_parse_quran_ref( 'Quran 4:34–35' ) );
		$this->assertNull( murtadd_parse_quran_ref( 'Qur’an 200:1' ) );
		$this->assertNull( murtadd_parse_quran_ref( 'Bukhari 6922' ) );
	}

	public function test_rank_math_merge_adds_frame_nodes_once() {
		$rm   = array(
			'WebPage' => array( '@type' => 'WebPage', '@id' => 'https://murtadd.org/x/#webpage' ),
			'side'    => array( '@type' => 'WPSideBar', '@id' => 'https://murtadd.org/#sidebar' ),
		);
		$ours = array(
			'@context' => 'https://schema.org',
			'@graph'   => array(
				array( '@type' => 'CollectionPage', '@id' => 'https://murtadd.org/x/#webpage', 'inLanguage' => 'en-US' ),
				array( '@type' => 'WPSideBar', '@id' => 'https://murtadd.org/#sidebar', 'cssSelector' => '.murtadd-sidebar' ),
				array( '@type' => 'WPSideBar', '@id' => 'https://murtadd.org/x/#rail', 'cssSelector' => '.murtadd-rail' ),
				array( '@type' => 'Quotation', '@id' => 'https://murtadd.org/x/#a', 'text' => 'a' ),
				array( '@type' => 'Quotation', '@id' => 'https://murtadd.org/x/#b', 'text' => 'b' ),
			),
		);
		$out = murtadd_rank_math_merge( $rm, $ours );
		$this->assertSame( 'WebPage', $out['WebPage']['@type'], "Rank Math's page node stands." );
		$this->assertSame( 'en-US', $out['WebPage']['inLanguage'] );
		$ids = array_column( array_values( $out ), '@id' );
		$this->assertSame( count( $ids ), count( array_unique( $ids ) ), 'One node per @id.' );
		$this->assertCount( 5, $out, 'Both verses and the rail are added, the sidebar is filled.' );
		$this->assertSame( '.murtadd-sidebar', $out['side']['cssSelector'] );
	}
}
