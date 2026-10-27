<?php
/**
 * Long-form guides: data, links, seeding and the /guides/ route.
 *
 * The Islamic dilemma guide is linked from six sister sites at
 * /guides/islamic-dilemma/, so its slug and route are pinned here.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../inc/seed-guides.php';
require_once __DIR__ . '/../inc/cpt-guide.php';

final class GuidesTest extends TestCase {

	protected function setUp(): void {
		murtadd_test_reset_posts();
	}

	/** Visible words of a guide body, without its sources list. */
	private function words( $html ) {
		$text = html_entity_decode( strip_tags( preg_replace( '/\{\{url:[^}]*\}\}/', '', $html ) ), ENT_QUOTES, 'UTF-8' );
		return preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );
	}

	private function guide( $slug ) {
		foreach ( murtadd_seed_guides() as $g ) {
			if ( $slug === $g['slug'] ) {
				return $g;
			}
		}
		$this->fail( "Guide {$slug} missing." );
	}

	public function test_nine_to_twelve_guides_load() {
		$n = count( murtadd_seed_guides() );
		$this->assertGreaterThanOrEqual( 9, $n );
		$this->assertLessThanOrEqual( 12, $n );
		$slugs = array_column( murtadd_seed_guides(), 'slug' );
		$this->assertSame( array_unique( $slugs ), $slugs, 'Duplicate guide slug.' );
	}

	public function test_every_guide_is_at_least_1500_words() {
		foreach ( murtadd_seed_guides() as $g ) {
			$this->assertGreaterThanOrEqual( 1500, count( $this->words( $g['body'] ) ), $g['slug'] . ' is under 1,500 words.' );
		}
	}

	public function test_excerpts_fit_a_meta_description() {
		foreach ( murtadd_seed_guides() as $g ) {
			$this->assertNotSame( '', trim( $g['excerpt'] ), $g['slug'] );
			$this->assertLessThanOrEqual( 155, strlen( $g['excerpt'] ), $g['slug'] . ' excerpt is over 155 characters.' );
		}
	}

	/** The sister sites link to this exact address. */
	public function test_islamic_dilemma_route_and_seo() {
		$g = $this->guide( 'islamic-dilemma' );
		$this->assertStringContainsStringIgnoringCase( 'Islamic dilemma', $g['title'] );
		$this->assertLessThanOrEqual( 60, strlen( $g['title'] ) );
		$this->assertStringContainsStringIgnoringCase( 'Islamic dilemma', $g['excerpt'] );
		$this->assertSame( 'Islamic dilemma', $g['focus_keyword'] );

		$words = $this->words( $g['body'] );
		$count = preg_match_all( '/islamic dilemma/i', implode( ' ', $words ) );
		$this->assertGreaterThanOrEqual( 0.02, $count / count( $words ), 'Keyword density under 2.0%.' );
		$this->assertMatchesRegularExpression( '/islamic dilemma/i', implode( ' ', array_slice( $words, 0, 100 ) ), 'Keyword not in the first 100 words.' );
		$this->assertGreaterThanOrEqual( 2, preg_match_all( '/<h2>[^<]*Islamic dilemma[^<]*<\/h2>/i', $g['body'] ) );

		foreach ( array(
			'https://abrahamic-religions.com/islamic-dilemma/',
			'https://injil.help/the-islamic-dilemma/',
			'https://compelling-evidence.com/articles/islamic-dilemma-quran-and-bible/',
			'https://bestofislam.org/islamic-dilemma/',
			'https://apostleofdoom.org/islamic-dilemma/',
			'https://lailatulqadar.guide/islamic-dilemma/',
		) as $url ) {
			$this->assertStringContainsString( 'href="' . $url . '"', $g['body'], "Network link {$url} missing." );
		}
		$this->assertStringNotContainsString( 'murtadd.org/guides/islamic-dilemma', $g['body'], 'The guide links to itself.' );
	}

	public function test_post_type_serves_guides_at_slash_guides() {
		$src = file_get_contents( __DIR__ . '/../inc/cpt-guide.php' );
		$this->assertMatchesRegularExpression( "/'slug'\s*=>\s*'guides'/", $src );
		$this->assertMatchesRegularExpression( "/'with_front'\s*=>\s*false/", $src );
		$fn = file_get_contents( __DIR__ . '/../functions.php' );
		$this->assertStringContainsString( "/inc/cpt-guide.php'", $fn );
		$this->assertStringContainsString( "/inc/seed-guides.php'", $fn );
	}

	/** The new rules reach sites already stamped with this version. */
	public function test_rewrite_flush_is_requested_once() {
		$GLOBALS['murtadd_options']['murtadd_rewrite_version'] = '1.43.0';
		murtadd_guide_rewrite_once();
		$this->assertFalse( get_option( 'murtadd_rewrite_version' ), 'Stamp not cleared.' );
		$GLOBALS['murtadd_options']['murtadd_rewrite_version'] = '1.43.0';
		murtadd_guide_rewrite_once();
		$this->assertSame( '1.43.0', get_option( 'murtadd_rewrite_version' ), 'Flush requested on every request.' );
		$this->assertSame( 98, murtadd_test_hook_priority( 'init', 'murtadd_guide_rewrite_once' ), 'Must run before the flusher at 99.' );
	}

	public function test_internal_links_resolve_to_seeded_content() {
		$doubts    = array_column( murtadd_seed_doubts(), 'slug' );
		$rebuttals = array_column( murtadd_seed_rebuttals(), 'slug' );
		$guides    = array_column( murtadd_seed_guides(), 'slug' );
		foreach ( murtadd_seed_guides() as $g ) {
			preg_match_all( '/\{\{url:\/([a-z]+)\/(?:([a-z0-9-]+)\/)?\}\}/', $g['body'], $m, PREG_SET_ORDER );
			$this->assertNotEmpty( $m, $g['slug'] . ' links nothing on this site.' );
			foreach ( $m as $link ) {
				$slug = $link[2] ?? '';
				if ( '' === $slug ) {
					$this->assertContains( $link[1], array( 'doubts', 'rebuttals' ), $g['slug'] );
					continue;
				}
				$pool = array(
					'doubts'    => $doubts,
					'rebuttals' => $rebuttals,
					'guides'    => $guides,
				)[ $link[1] ] ?? array();
				$this->assertContains( $slug, $pool, "{$g['slug']} links to missing /{$link[1]}/{$slug}/." );
			}
		}
	}

	public function test_topics_exist() {
		$topics = array_keys( murtadd_starter_topics() );
		foreach ( murtadd_seed_guides() as $g ) {
			$this->assertNotEmpty( $g['topics'] );
			foreach ( $g['topics'] as $t ) {
				$this->assertContains( $t, $topics, "{$g['slug']}: unknown topic {$t}." );
			}
		}
	}

	public function test_no_dark_mode_and_no_unresolved_tokens() {
		foreach ( murtadd_seed_guides() as $g ) {
			$this->assertStringNotContainsString( 'prefers-color-scheme', $g['body'] );
			$content = murtadd_guide_content( $g );
			$this->assertStringNotContainsString( '{{url:', $content, $g['slug'] );
		}
	}

	public function test_seeding_is_idempotent_and_never_resurrects() {
		$first = murtadd_seed_guide_posts();
		$this->assertSame( count( murtadd_seed_guides() ), $first );
		$this->assertSame( 0, murtadd_seed_guide_posts(), 'Second run created duplicates.' );

		$post = get_page_by_path( 'islamic-dilemma', OBJECT, 'murtadd_guide' );
		$this->assertNotNull( $post );
		$this->assertStringContainsString( 'https://murtadd.org/guides/how-to-assess-a-doubt/', $post->post_content );
		$this->assertContains( 'islamic-dilemma', murtadd_seeded_slugs()['murtadd_guide'] );

		murtadd_test_delete_post( 'murtadd_guide', 'islamic-dilemma' );
		$this->assertSame( 0, murtadd_seed_guide_posts(), 'A deleted guide was resurrected.' );
		$this->assertNull( get_page_by_path( 'islamic-dilemma', OBJECT, 'murtadd_guide' ) );
	}

	public function test_existing_post_is_adopted_not_overwritten() {
		wp_insert_post(
			array(
				'post_type'    => 'murtadd_guide',
				'post_name'    => 'islamic-dilemma',
				'post_content' => 'Edited by hand.',
			)
		);
		murtadd_seed_guide_posts();
		$this->assertSame( 'Edited by hand.', get_page_by_path( 'islamic-dilemma', OBJECT, 'murtadd_guide' )->post_content );
		$this->assertContains( 'islamic-dilemma', murtadd_seeded_slugs()['murtadd_guide'] );
	}

	public function test_maybe_seed_runs_once_per_list() {
		$this->assertSame( count( murtadd_seed_guides() ), murtadd_maybe_seed_guides() );
		murtadd_test_delete_post( 'murtadd_guide', 'what-is-apostasy' );
		$this->assertSame( 0, murtadd_maybe_seed_guides() );
	}

	public function test_bad_data_loads_as_nothing() {
		$this->assertTrue( murtadd_seed_entry_valid( murtadd_seed_guides()[0], array( 'slug' => 'string', 'sources' => 'sources' ) ) );
		$bad = murtadd_seed_guides()[0];
		$bad['topics'] = 'identity';
		$this->assertFalse( murtadd_seed_entry_valid( $bad, array( 'topics' => 'strings' ) ) );
		$this->assertSame( array(), murtadd_seed_load_entries( 'no-such-file', array( 'slug' => 'string' ) ) );
	}
}
