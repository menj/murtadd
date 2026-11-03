<?php
/**
 * The Journal: dated posts at /journal/ beside the guides (owner, 2026-11-03).
 *
 * Pins the calendar, the fixed dates and their status, a second run that
 * changes nothing, the move from the old Blog page, the BlogPosting node,
 * the navigation and footer links, and the copy rules every post follows.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../inc/activation.php';
require_once __DIR__ . '/../inc/seed-journal.php';
require_once __DIR__ . '/../inc/seed-guides.php';

final class JournalTest extends TestCase {

	/** 3 November 2026, noon UTC: the day the Journal was set up. */
	private const NOW = 1793707200;

	protected function setUp(): void {
		murtadd_test_reset_posts();
		$GLOBALS['murtadd_test_meta'] = array();
		murtadd_test_view( array() );
	}

	protected function tearDown(): void {
		murtadd_test_view( array() );
		$GLOBALS['murtadd_test_meta'] = array();
	}

	private function text( $html ) {
		$html = preg_replace( '/\{\{url:[^}]*\}\}/', '', $html );
		return html_entity_decode( strip_tags( $html ), ENT_QUOTES, 'UTF-8' );
	}

	public function test_now_constant_is_the_set_up_day() {
		$this->assertSame( '2026-11-03 12:00:00', gmdate( 'Y-m-d H:i:s', self::NOW ) );
	}

	public function test_the_calendar() {
		$dates = array_column( murtadd_seed_journal(), 'date' );
		$this->assertCount( 8, $dates );
		$oct = array_filter( $dates, static function ( $d ) { return $d > '2026-10-01 23:59:59' && $d < '2026-11-01'; } );
		$dec = array_filter( $dates, static function ( $d ) { return 0 === strpos( $d, '2026-12-' ); } );
		$this->assertCount( 3, $oct, 'Three backdated posts in October, after 1 October.' );
		$this->assertCount( 1, array_filter( $dates, static function ( $d ) { return 0 === strpos( $d, '2026-11-16 ' ); } ) );
		$this->assertCount( 4, $dec, 'Four scheduled posts in December.' );
		$sorted = $dates;
		sort( $sorted );
		$this->assertSame( $sorted, $dates, 'The file runs in date order.' );
	}

	/** 1 Rajab 1448 is 10 December 2026 by the Umm al-Qura calendar. */
	public function test_the_rajab_post() {
		foreach ( murtadd_seed_journal() as $p ) {
			if ( 0 === strpos( $p['date'], '2026-12-10 ' ) ) {
				$this->assertStringContainsString( 'Rajab', $p['title'] );
				$this->assertStringContainsString( 'Umm al-Qura', $p['body'] );
				$this->assertStringContainsString( '9:36', $p['body'] );
				return;
			}
		}
		$this->fail( 'No post on 10 December 2026.' );
	}

	public function test_slugs_and_titles() {
		$suffix = ' | murtadd.org';
		$slugs  = array_column( murtadd_seed_journal(), 'slug' );
		$this->assertSame( array_unique( $slugs ), $slugs );
		foreach ( murtadd_seed_journal() as $p ) {
			$this->assertMatchesRegularExpression( '/^[a-z]+(-[a-z]+)*$/', $p['slug'], 'Slugs carry no digits.' );
			$this->assertLessThan( 60, mb_strlen( $p['title'] . $suffix ), $p['slug'] );
			$this->assertLessThanOrEqual( MURTADD_TITLE_MAX, mb_strlen( murtadd_build_title( $p['title'] ) ), $p['slug'] );
			$this->assertStringEndsWith( ' | Murtadd', murtadd_build_title( $p['title'] ) );
			$this->assertLessThanOrEqual( 155, strlen( $p['excerpt'] ), $p['slug'] . ' excerpt.' );
		}
	}

	public function test_every_post_is_700_to_1100_words() {
		foreach ( murtadd_seed_journal() as $p ) {
			$n = count( preg_split( '/\s+/u', trim( $this->text( $p['body'] ) ), -1, PREG_SPLIT_NO_EMPTY ) );
			$this->assertGreaterThanOrEqual( 700, $n, $p['slug'] );
			$this->assertLessThanOrEqual( 1100, $n, $p['slug'] );
		}
	}

	/** Internal links name pages the seed creates; one sister link a post. */
	public function test_links() {
		$known = array();
		foreach ( array( 'guides' => 'guides', 'rebuttals' => 'rebuttals', 'doubts' => 'doubts' ) as $file => $base ) {
			foreach ( json_decode( file_get_contents( dirname( __DIR__ ) . "/inc/data/seed/$file.json" ), true ) as $e ) {
				$known[] = "/$base/{$e['slug']}/";
			}
		}
		$sites = json_decode( file_get_contents( dirname( __DIR__ ) . '/inc/data/network-links.json' ), true )['sites'];
		unset( $sites['murtadd'] );
		$hosts = array_map(
			static function ( $u ) {
				return parse_url( $u, PHP_URL_HOST );
			},
			$sites
		);
		foreach ( murtadd_seed_journal() as $p ) {
			preg_match_all( '/href="\{\{url:([^}]*)\}\}"/', $p['body'], $m );
			$this->assertGreaterThanOrEqual( 2, count( array_unique( $m[1] ) ), $p['slug'] . ' needs internal links.' );
			foreach ( $m[1] as $path ) {
				$this->assertContains( $path, $known, "{$p['slug']} links to $path, which the seed does not create." );
			}
			$this->assertDoesNotMatchRegularExpression( '/(?<!href=")\{\{url:/', $p['body'], $p['slug'] . ' has a bare link token.' );
			preg_match_all( '/href="(https?:[^"]*)"/', $p['body'], $out );
			$this->assertCount( 1, $out[1], $p['slug'] . ' carries one sister link.' );
			$this->assertContains( parse_url( $out[1][0], PHP_URL_HOST ), $hosts, $p['slug'] );
		}
	}

	/** The copy rules a script can check: no contractions, em dashes or the banned reframe. */
	public function test_copy_rules() {
		foreach ( murtadd_seed_journal() as $p ) {
			$text = $this->text( $p['title'] . ' ' . $p['excerpt'] . ' ' . $p['body'] );
			$this->assertDoesNotMatchRegularExpression( "/\\b\\w+'(t|re|ve|ll|d|m)\\b/i", $text, $p['slug'] . ' has a contraction.' );
			$this->assertDoesNotMatchRegularExpression( "/\\b(it|that|there|here|what|who|let|he|she)'s\\b/i", $text, $p['slug'] . ' has a contraction.' );
			$this->assertStringNotContainsString( '—', $text, $p['slug'] );
			$this->assertDoesNotMatchRegularExpression( '/\b(delve|tapestry|crucial|pivotal|testament|navigate|journey|moreover|furthermore|additionally|straightforward)\b/i', $text, $p['slug'] );
			$this->assertMatchesRegularExpression( '/\(\d{1,3}:\d{1,3}(-\d{1,3})?\)/', $p['body'], $p['slug'] . ' cites the Qur\'an by surah and verse.' );
			$this->assertMatchesRegularExpression( '/\((al-Bukhari|Muslim|Abu Dawud) \d+/', $p['body'], $p['slug'] . ' cites a hadith by collection and number.' );
		}
	}

	public function test_status_follows_the_date() {
		$this->assertSame( 'publish', murtadd_journal_status( '2026-10-08 09:00:00', self::NOW ) );
		$this->assertSame( 'future', murtadd_journal_status( '2026-11-16 09:00:00', self::NOW ) );
		$this->assertSame( 'publish', murtadd_journal_status( '2026-11-03 12:00:00', self::NOW ), 'The present moment is not ahead.' );
	}

	public function test_seeding_dates_statuses_and_a_second_run_changes_nothing() {
		$this->assertSame( 8, murtadd_seed_journal_posts( self::NOW ) );
		$statuses = array();
		foreach ( murtadd_seed_journal() as $p ) {
			$post = get_page_by_path( $p['slug'], OBJECT, 'post' );
			$this->assertSame( $p['date'], $post->post_date );
			$this->assertTrue( $post->edit_date );
			$this->assertSame( 1, $post->meta_input['_murtadd_seeded'] );
			$this->assertStringNotContainsString( '{{url:', $post->post_content );
			$statuses[ $post->post_status ][] = $p['slug'];
		}
		$this->assertCount( 3, $statuses['publish'] );
		$this->assertCount( 5, $statuses['future'] );

		$before = serialize( $GLOBALS['murtadd_posts'] );
		$this->assertSame( 0, murtadd_seed_journal_posts( self::NOW ) );
		$this->assertSame( 0, murtadd_seed_journal_posts( self::NOW + 90 * DAY_IN_SECONDS ) );
		$this->assertSame( $before, serialize( $GLOBALS['murtadd_posts'] ), 'A re-run changes nothing.' );
	}

	public function test_deleted_post_is_not_resurrected_and_existing_post_is_adopted() {
		wp_insert_post( array( 'post_type' => 'post', 'post_name' => 'they-ask-you', 'post_content' => 'Edited by hand.' ) );
		murtadd_seed_journal_posts( self::NOW );
		$this->assertSame( 'Edited by hand.', get_page_by_path( 'they-ask-you', OBJECT, 'post' )->post_content );
		murtadd_test_delete_post( 'post', 'the-hudaybiyyah-clause' );
		$this->assertSame( 0, murtadd_seed_journal_posts( self::NOW ) );
		$this->assertNull( get_page_by_path( 'the-hudaybiyyah-clause', OBJECT, 'post' ) );
	}

	public function test_maybe_seed_runs_once_per_list() {
		$this->assertGreaterThan( 0, murtadd_maybe_seed_journal() );
		$this->assertSame( 0, murtadd_maybe_seed_journal() );
		$this->assertSame( 10, murtadd_test_hook_priority( 'admin_init', 'murtadd_maybe_seed_journal' ) );
	}

	/** A failed insert leaves the list unrecorded, so the next run retries it. */
	public function test_failed_insert_is_retried() {
		$GLOBALS['murtadd_fail_insert'] = array( 'they-ask-you' );
		murtadd_maybe_seed_journal();
		unset( $GLOBALS['murtadd_fail_insert'] );
		$this->assertNull( get_page_by_path( 'they-ask-you', OBJECT, 'post' ) );
		$this->assertFalse( get_option( 'murtadd_journal_seeded' ) );
		$this->assertSame( 1, murtadd_maybe_seed_journal() );
		$this->assertNotNull( get_page_by_path( 'they-ask-you', OBJECT, 'post' ) );
		$this->assertSame( 0, murtadd_maybe_seed_journal() );
	}

	/** A run in progress holds the lock; a stale lock is taken over; the lock is always released. */
	public function test_seeding_lock() {
		update_option( 'murtadd_journal_seeding', time() );
		$this->assertSame( 0, murtadd_maybe_seed_journal() );
		$this->assertNull( get_page_by_path( 'they-ask-you', OBJECT, 'post' ) );
		update_option( 'murtadd_journal_seeding', time() - 3600 );
		$this->assertSame( 8, murtadd_maybe_seed_journal() );
		$this->assertFalse( get_option( 'murtadd_journal_seeding' ) );
	}

	/** Seeded Blog page beside an existing Journal page: Blog to draft, posts page to the Journal. */
	public function test_seeded_blog_beside_journal_is_retired() {
		$blog = wp_insert_post( array( 'post_type' => 'page', 'post_name' => 'blog', 'post_title' => 'Blog', 'post_status' => 'publish' ) );
		$GLOBALS['murtadd_test_meta'][ $blog ]['_murtadd_seeded'] = 1;
		$journal = wp_insert_post( array( 'post_type' => 'page', 'post_name' => 'journal', 'post_title' => 'Journal' ) );
		update_option( 'page_for_posts', $blog );
		$this->assertSame( 'retired', murtadd_journal_page_once() );
		$this->assertSame( 'draft', get_page_by_path( 'blog' )->post_status );
		$this->assertSame( $journal, (int) get_option( 'page_for_posts' ) );

		// An editor's Blog page beside the Journal is left published.
		murtadd_test_reset_posts();
		$GLOBALS['murtadd_test_meta'] = array();
		wp_insert_post( array( 'post_type' => 'page', 'post_name' => 'blog', 'post_title' => 'Mine', 'post_status' => 'publish' ) );
		wp_insert_post( array( 'post_type' => 'page', 'post_name' => 'journal', 'post_title' => 'Journal' ) );
		$this->assertSame( 'present', murtadd_journal_page_once() );
		$this->assertSame( 'publish', get_page_by_path( 'blog' )->post_status );
	}

	/** /blog/ goes to the posts page wherever it is. */
	public function test_blog_redirect_follows_the_posts_page() {
		$target = murtadd_legacy_redirect_map()['blog'];
		$this->assertSame( 'https://murtadd.org/journal/', murtadd_legacy_redirect_target( $target ) );
		update_option( 'page_for_posts', 12 );
		murtadd_test_view( array( 'links' => array( 12 => 'https://murtadd.org/notes/' ) ) );
		$this->assertSame( 'https://murtadd.org/notes/', murtadd_legacy_redirect_target( $target ) );
	}

	/** Rank Math's Article becomes a BlogPosting and gains the Journal in isPartOf. */
	public function test_rank_math_article_gains_the_journal() {
		murtadd_test_view( array( 'post' => (object) array( 'ID' => 31, 'post_type' => 'post', 'post_name' => 'they-ask-you', 'post_title' => 'They ask you' ) ) );
		$ours = murtadd_structured_data_primary_node();
		$rm   = array( 'article' => array( '@type' => 'Article', '@id' => 'https://murtadd.org/they-ask-you/#richSnippet', 'isPartOf' => array( '@id' => 'https://murtadd.org/they-ask-you/#webpage' ) ) );
		$out  = murtadd_rank_math_merge( $rm, $ours );
		$this->assertArrayNotHasKey( 'murtadd_article', $out );
		$this->assertSame( 'BlogPosting', $out['article']['@type'] );
		$ids = array_column( $out['article']['isPartOf'], '@id' );
		$this->assertContains( 'https://murtadd.org/they-ask-you/#webpage', $ids, 'Rank Math\'s own isPartOf is kept.' );
		$this->assertContains( 'https://murtadd.org/journal/#blog', $ids );
	}

	/** Guides share single.php: only a post links back to the Journal. */
	public function test_single_back_link_by_type() {
		$php = file_get_contents( dirname( __DIR__ ) . '/single.php' );
		$this->assertMatchesRegularExpression( "/if \( 'post' === get_post_type\(\) \) : \?>\s*<a class=\"murtadd-back\" href=\"<\?php echo esc_url\( murtadd_journal_url\(\) \)/", $php );
		$this->assertStringContainsString( "'Back to Guides'", $php );
	}

	public function test_bad_date_loads_nothing() {
		$this->assertFalse( murtadd_seed_entry_valid( array( 'slug' => 'x' ), array( 'date' => 'string' ) ) );
		$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', murtadd_seed_journal()[0]['date'] );
	}

	/** A seeded Blog page becomes the Journal; the posts page follows it. */
	public function test_seeded_blog_page_is_renamed() {
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_name' => 'blog', 'post_title' => 'Blog' ) );
		$GLOBALS['murtadd_test_meta'][ $id ]['_murtadd_seeded'] = 1;
		update_option( 'page_for_posts', $id );
		$this->assertSame( 'renamed', murtadd_journal_page_once() );
		$this->assertNull( get_page_by_path( 'blog' ) );
		$this->assertSame( $id, get_page_by_path( 'journal' )->ID );
		$this->assertSame( 'Journal', get_page_by_path( 'journal' )->post_title );
		$this->assertSame( $id, (int) get_option( 'page_for_posts' ) );
		$this->assertSame( 'present', murtadd_journal_page_once() );
	}

	/** A Blog page an editor made is left alone, and so is their posts page. */
	public function test_editor_blog_page_is_kept() {
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_name' => 'blog', 'post_title' => 'My blog' ) );
		update_option( 'page_for_posts', $id );
		murtadd_journal_page_once();
		$this->assertSame( 'My blog', get_page_by_path( 'blog' )->post_title );
		$this->assertNotNull( get_page_by_path( 'journal' ) );
		$this->assertSame( $id, (int) get_option( 'page_for_posts' ) );
	}

	public function test_fresh_site_gets_a_journal_page_as_posts_page() {
		$this->assertArrayHasKey( 'journal', murtadd_required_pages() );
		$this->assertArrayNotHasKey( 'blog', murtadd_required_pages() );
		$this->assertSame( 'created', murtadd_journal_page_once() );
		$this->assertSame( get_page_by_path( 'journal' )->ID, (int) get_option( 'page_for_posts' ) );
		$this->assertSame( 'posts', murtadd_legacy_redirect_map()['blog']['kind'] );
	}

	public function test_journal_url() {
		$this->assertSame( 'https://murtadd.org/journal/', murtadd_journal_url() );
		update_option( 'page_for_posts', 12 );
		murtadd_test_view( array( 'links' => array( 12 => 'https://murtadd.org/notes/' ) ) );
		$this->assertSame( 'https://murtadd.org/notes/', murtadd_journal_url() );
	}

	public function test_post_is_a_blog_posting_in_the_journal() {
		$GLOBALS['murtadd_test_fields'][31]['post_excerpt'] = 'A short summary.';
		murtadd_test_view( array( 'post' => (object) array( 'ID' => 31, 'post_type' => 'post', 'post_name' => 'they-ask-you', 'post_title' => 'They ask you' ) ) );
		$node = murtadd_structured_data_primary_node();
		unset( $GLOBALS['murtadd_test_fields'][31] );
		$this->assertSame( 'BlogPosting', $node['@type'] );
		$this->assertSame( 'https://murtadd.org/they-ask-you/#article', $node['@id'] );
		$this->assertSame( 'Blog', $node['isPartOf'][1]['@type'] );
		$this->assertSame( 'https://murtadd.org/journal/', $node['isPartOf'][1]['url'] );
		$this->assertSame( 'A short summary.', $node['description'] );
		$this->assertNotEmpty( $node['author']['url'] );

		$graph = murtadd_structured_data_node();
		$this->assertArrayHasKey( '@graph', $graph, 'One graph.' );
		$types = array_column( $graph['@graph'], '@type' );
		$this->assertSame( 1, count( array_keys( $types, 'BlogPosting', true ) ) );
		foreach ( $graph['@graph'] as $n ) {
			if ( 'WebPage' === $n['@type'] ) {
				$this->assertSame( array( '@id' => 'https://murtadd.org/they-ask-you/#article' ), $n['mainEntity'] );
			}
		}
	}

	public function test_navigation_footer_and_styles() {
		$root = dirname( __DIR__ );
		$this->assertStringContainsString( "murtadd_nav_state( 'journal' ); ?> href=\"<?php echo esc_url( murtadd_journal_url() ); ?>\"", file_get_contents( "$root/sidebar.php" ) );
		$this->assertStringContainsString( 'murtadd_journal_url()', file_get_contents( "$root/footer.php" ) );
		$this->assertStringContainsString( 'murtadd_journal_url()', file_get_contents( "$root/front-page.php" ) );
		foreach ( array( 'sidebar.php', 'footer.php', 'front-page.php', 'single.php', 'home.php' ) as $f ) {
			$this->assertStringNotContainsString( "'/blog/'", file_get_contents( "$root/$f" ), $f );
		}

		$fn = file_get_contents( "$root/functions.php" );
		$this->assertStringContainsString( "/inc/seed-journal.php'", $fn );
		$this->assertStringContainsString( "'/assets/css/journal' . MURTADD_ASSET_SUFFIX . '.css'", $fn );

		$css = file_get_contents( "$root/assets/css/journal.css" ) . file_get_contents( "$root/assets/css/main.css" );
		$min = file_get_contents( "$root/assets/css/journal.min.css" ) . file_get_contents( "$root/assets/css/main.min.css" );
		foreach ( array( 'murtadd-journal-intro', 'murtadd-journal-dateline', 'murtadd-footer-journal' ) as $class ) {
			$this->assertStringContainsString( '.' . $class, $css, $class );
			$this->assertStringContainsString( '.' . $class, $min, $class . ' (min)' );
		}
		foreach ( array( 'journal.css', 'journal.min.css' ) as $f ) {
			$this->assertStringNotContainsString( 'prefers-color-scheme', file_get_contents( "$root/assets/css/$f" ), 'Light only.' );
		}
	}

	/** Guides stay their own post type, at their fixed addresses. */
	public function test_guides_stay_separate() {
		$ssot = file_get_contents( dirname( __DIR__ ) . '/docs/ssot.md' );
		$this->assertStringContainsString( '## Journal', $ssot );
		$this->assertStringContainsString( '2026-11-03', $ssot );
		$guides = array_column( murtadd_seed_guides(), 'slug' );
		$this->assertEmpty( array_intersect( $guides, array_column( murtadd_seed_journal(), 'slug' ) ) );
	}
}
