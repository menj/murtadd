<?php
/**
 * The Journal post page (inc/article.php).
 *
 * Pins the heading ids the contents links to, the three-heading threshold,
 * the place of the mid-article next step, the scrollable tables, the FAQ
 * pairs and their FAQPage on a Journal post only, the citation from the
 * sources, the related-post ranking, the settings and the template order.
 * Nothing here may sell: the test checks the wording too.
 *
 * @package Murtadd\Tests
 */

// Run from the command line only (phpdbg included): on a web server this file does nothing.
if ( ! in_array( PHP_SAPI, array( 'cli', 'phpdbg' ), true ) ) {
	exit;
}

use PHPUnit\Framework\TestCase;

if ( ! function_exists( 'wp_html_excerpt' ) ) {
	function wp_html_excerpt( $str, $count, $more = '' ) {
		$str = wp_strip_all_tags( (string) $str );
		return mb_strlen( $str ) > $count ? mb_substr( $str, 0, $count ) . $more : $str;
	}
}

require_once __DIR__ . '/../inc/article.php';

final class ArticleTest extends TestCase {

	private const BODY = '<p>Intro.</p><h2>The man at the head of them</h2><p>A.</p><h2 id="own">Words, said aloud</h2><p>B.</p><h2>The man at the head of them</h2><p>C.</p><h2>What it does not settle</h2><p>D.</p>';

	protected function setUp(): void {
		$GLOBALS['murtadd_options'] = array();
		murtadd_test_view( array() );
	}

	protected function tearDown(): void {
		murtadd_test_view( array() );
		$GLOBALS['murtadd_options'] = array();
		unset( $GLOBALS['murtadd_test_fields'][41] );
	}

	public function test_module_is_loaded_by_the_theme() {
		$this->assertContains( 'article', murtadd_theme_files() );
	}

	public function test_headings_get_unique_ids_and_keep_their_own() {
		$list = murtadd_article_headings( self::BODY );
		$this->assertSame( array( 'the-man-at-the-head-of-them', 'own', 'the-man-at-the-head-of-them-2', 'what-it-does-not-settle' ), array_column( $list, 0 ) );
		$this->assertSame( 'Words, said aloud', $list[1][1] );

		$html = murtadd_article_heading_ids( self::BODY );
		$this->assertStringContainsString( '<h2 id="the-man-at-the-head-of-them-2">', $html );
		$this->assertSame( 1, substr_count( $html, 'id="own"' ), 'An id the author set is kept as it is.' );
	}

	public function test_mid_step_goes_before_the_third_heading_only() {
		$out = murtadd_article_insert_mid( self::BODY, '<div class="x">step</div>' );
		$this->assertSame( strpos( $out, '<div class="x">' ) + strlen( '<div class="x">step</div>' ), strpos( $out, '<h2>The man at the head of them</h2><p>C.' ) );
		$two = '<h2>A</h2><p>1</p><h2>B</h2><p>2</p>';
		$this->assertSame( $two, murtadd_article_insert_mid( $two, '<div>step</div>' ) );
	}

	public function test_tables_become_labelled_scroll_regions() {
		$fig = murtadd_article_tables( '<figure class="wp-block-table"><table><tr><td>1</td></tr></table><figcaption>Reports cited</figcaption></figure>' );
		$this->assertStringContainsString( 'role="region" tabindex="0" aria-label="Reports cited"', $fig );
		$this->assertStringContainsString( 'class="wp-block-table murtadd-table"', $fig );
		$bare = murtadd_article_tables( '<p>x</p><table><tr><td>1</td></tr></table>' );
		$this->assertStringContainsString( '<div class="murtadd-table" role="region" tabindex="0" aria-label="Table"><table>', $bare );
		$this->assertSame( $fig, murtadd_article_tables( $fig ), 'A region is never wrapped twice.' );
	}

	public function test_faq_pairs_come_only_from_faq_details() {
		$html  = '<details class="wp-block-details is-style-faq"><summary>Did he know?</summary><!-- wp:paragraph --><p>He <em>knew</em> many.</p></details>'
			. '<details class="wp-block-details"><summary>Not a FAQ</summary><p>Plain.</p></details>';
		$pairs = murtadd_article_faq_pairs( $html );
		$this->assertCount( 1, $pairs );
		$this->assertSame( 'Did he know?', $pairs[0]['name'] );
		$this->assertSame( 'He knew many.', $pairs[0]['acceptedAnswer']['text'] );
	}

	public function test_faq_page_node_on_a_post_with_visible_questions_only() {
		$post = (object) array( 'ID' => 41, 'post_type' => 'post', 'post_name' => 'they-ask-you', 'post_title' => 'They ask you' );
		murtadd_test_view( array( 'post' => $post ) );
		$GLOBALS['murtadd_test_fields'][41]['post_content'] = '<h2>Q?</h2><p>A heading is not a question here.</p>';
		$this->assertNull( murtadd_faq_node(), 'A post without FAQ details gets no FAQPage.' );

		$GLOBALS['murtadd_test_fields'][41]['post_content'] = '<details class="is-style-faq"><summary>Q?</summary><p>A.</p></details>';
		$node = murtadd_faq_node();
		$this->assertSame( 'FAQPage', $node['@type'] );
		$this->assertSame( 'Q?', $node['mainEntity'][0]['name'] );
		$this->assertSame( array( '@id' => 'https://murtadd.org/they-ask-you/#webpage' ), $node['isPartOf'] );
	}

	public function test_sources_feed_the_citation() {
		murtadd_test_view(
			array(
				'post' => (object) array( 'ID' => 41, 'post_type' => 'post', 'post_name' => 'they-ask-you', 'post_title' => 'They ask you' ),
				'meta' => array(
					41 => array(
						'_murtadd_sources' => array(
							array( 'citation_text' => 'Sahih al-Bukhari 4905', 'url' => 'https://sunnah.com/bukhari:4905' ),
							array( 'citation_text' => '', 'url' => 'https://example.org/' ),
							array( 'citation_text' => 'Ibn Taymiyyah, al-Sarim al-Maslul', 'url' => '' ),
						),
					),
				),
			)
		);
		$node = murtadd_structured_data_primary_node();
		$this->assertSame( 'BlogPosting', $node['@type'] );
		$this->assertCount( 2, $node['citation'], 'A row without citation text is not shown, so it is not cited.' );
		$this->assertSame( 'https://sunnah.com/bukhari:4905', $node['citation'][0]['url'] );
		$this->assertArrayNotHasKey( 'url', $node['citation'][1] );
	}

	public function test_related_rank_by_shared_terms_then_newest() {
		$mine = array( 'murtadd_topic' => array( 5 ), 'post_tag' => array( 7 ), 'category' => array() );
		$cand = array(
			10 => array( 'murtadd_topic' => array(), 'post_tag' => array(), 'category' => array() ),
			11 => array( 'murtadd_topic' => array( 5 ), 'post_tag' => array(), 'category' => array() ),
			12 => array( 'murtadd_topic' => array(), 'post_tag' => array( 7 ), 'category' => array() ),
			13 => array( 'murtadd_topic' => array(), 'post_tag' => array(), 'category' => array() ),
		);
		$dates = array( 10 => 100, 11 => 50, 12 => 60, 13 => 200 );
		$this->assertSame( array( 11, 12, 13 ), murtadd_article_rank( $mine, $cand, $dates, 3 ) );
	}

	public function test_reading_order_breaks_date_ties_by_id() {
		$a = (object) array( 'ID' => 7, 'post_date' => '2026-10-08 09:00:00' );
		$b = (object) array( 'ID' => 9, 'post_date' => '2026-10-08 09:00:00' );
		$c = (object) array( 'ID' => 3, 'post_date' => '2026-10-09 09:00:00' );
		$this->assertSame( 1, murtadd_article_order( $b, $a ), 'Same date: the higher ID comes after.' );
		$this->assertSame( -1, murtadd_article_order( $a, $b ) );
		$this->assertSame( 1, murtadd_article_order( $c, $b ), 'A later date comes after, whatever the ID.' );
		$this->assertSame( 0, murtadd_article_order( $a, $a ) );
	}

	public function test_settings_defaults_and_sanitiser() {
		$this->assertTrue( murtadd_article_on( 'contents' ) );
		$this->assertTrue( murtadd_article_on( 'answer_box' ) );
		$this->assertTrue( murtadd_article_on( 'related' ) );
		$this->assertFalse( murtadd_article_on( 'mid_next' ), 'The one-line next step in the text is off until the owner turns it on.' );
		$clean = murtadd_sanitize_content_display( array( 'journal_mid_next' => '1' ) );
		$this->assertSame( 1, $clean['journal_mid_next'] );
		$this->assertSame( 0, $clean['journal_contents'], 'A switch left off on the Content tab is saved off.' );
		$GLOBALS['murtadd_options']['murtadd_content_display'] = $clean;
		$this->assertFalse( murtadd_article_on( 'contents' ) );
		$this->assertTrue( murtadd_article_on( 'mid_next' ) );
	}

	public function test_template_order_and_no_byline() {
		$php   = (string) file_get_contents( dirname( __DIR__ ) . '/single.php' );
		$order = array( 'murtadd_breadcrumbs()', 'murtadd_article_meta_row()', 'the_title()', 'murtadd_article_dek()', 'murtadd_article_answer_box()', 'murtadd_article_contents()', '<?php the_content(); ?>', 'murtadd_article_sources_panel()', "murtadd_article_next_step( 'end' )", 'murtadd_article_related()' );
		$last  = -1;
		foreach ( $order as $call ) {
			$at = strpos( $php, $call );
			$this->assertNotFalse( $at, $call );
			$this->assertGreaterThan( $last, $at, $call . ' is out of order.' );
			$last = $at;
		}
		$this->assertStringNotContainsString( 'the_author', $php, 'No visible byline on a Journal post.' );
	}

	public function test_no_sales_wording() {
		$src = (string) file_get_contents( dirname( __DIR__ ) . '/inc/article.php' );
		foreach ( array( 'Buy', 'buy now', 'Get started', 'price', 'Subscribe', 'sister site', 'network' ) as $word ) {
			$this->assertStringNotContainsString( $word, preg_replace( '#/\*.*?\*/|//[^\n]*#s', '', $src ), $word );
		}
	}

	public function test_assets_ship_with_their_min_twins() {
		$root = dirname( __DIR__ );
		foreach ( array( 'murtadd-dek', 'murtadd-answer', 'murtadd-toc--inline', 'murtadd-table', 'is-style-faq', 'murtadd-next--mid', 'murtadd-related-card' ) as $class ) {
			$this->assertStringContainsString( $class, (string) file_get_contents( "$root/assets/css/article.css" ), $class );
			$this->assertStringContainsString( $class, (string) file_get_contents( "$root/assets/css/article.min.css" ), $class );
		}
		$this->assertStringContainsString( "'/assets/css/article' . MURTADD_ASSET_SUFFIX . '.css'", (string) file_get_contents( "$root/functions.php" ) );
		foreach ( array( 'longform.js', 'longform.min.js' ) as $file ) {
			$this->assertStringContainsString( '.murtadd-blog-post', (string) file_get_contents( "$root/assets/js/$file" ), $file );
		}
	}
}
