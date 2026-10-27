<?php
/**
 * Seeder invariants.
 *
 * The defect that motivated this file: the "already there" check was
 * get_page_by_path() alone, and WordPress renames a trashed post's slug while
 * a deleted post vanishes entirely — so re-running setup rebuilt content an
 * editor had removed on purpose, directly contradicting the seeder's own
 * documentation. Found while preparing the live-site deployment of 1.26.x,
 * where the operator's next two actions were exactly "delete the placeholder
 * letters" (no longer seeded since 1.41.0) and "press Create anything missing".
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class SeedTest extends TestCase {

	protected function setUp(): void {
		murtadd_test_reset_posts();
	}

	/** Running the seeder twice must create everything exactly once. */
	public function test_seeding_is_idempotent() {
		$first  = murtadd_seed_content();
		$second = murtadd_seed_content();

		$this->assertSame( count( murtadd_seed_rebuttals() ), $first['rebuttals'] );
		$this->assertSame( count( murtadd_seed_doubts() ), $first['doubts'] );
		$this->assertSame( count( murtadd_seed_letters() ), $first['letters'] );

		$this->assertSame( 0, $second['rebuttals'], 'Second run created rebuttal duplicates.' );
		$this->assertSame( 0, $second['doubts'], 'Second run created doubt duplicates.' );
		$this->assertSame( 0, $second['letters'], 'Second run created letter duplicates.' );
		$this->assertCount( count( murtadd_seed_letters() ), $GLOBALS['murtadd_posts']['murtadd_letter'] ?? array() );
	}

	/**
	 * The seeded letters are the site's own open letters, never reader
	 * correspondence: signed by the site, marked open, dated, addressed to a
	 * situation, and never one of the retired lorem-ipsum slugs.
	 */
	public function test_letters_are_open_letters_from_the_site() {
		$letters = murtadd_seed_letters();
		$this->assertGreaterThanOrEqual( 3, count( $letters ) );
		$this->assertLessThanOrEqual( 5, count( $letters ) );

		murtadd_seed_content();
		foreach ( $letters as $l ) {
			$this->assertStringStartsWith( 'To ', $l['title'] );
			$this->assertStringNotContainsString( 'placeholder', $l['slug'] );
			$this->assertMatchesRegularExpression( '#<p>Murtadd</p>\s*$#', $l['body'], $l['slug'] . ' is not signed by the site.' );
			$this->assertMatchesRegularExpression( '/open letter from this site/i', $l['note'] ?? '', $l['slug'] . ' does not say whose letter it is.' );
			$this->assertDoesNotMatchRegularExpression( '/\{\{url:|lorem ipsum|placeholder/i', $l['body'] );
			$text = murtadd_normalise_copy( $l['body'] );
			$this->assertStringNotContainsString( '—', $text, $l['slug'] . ' has an em dash.' );
			$this->assertDoesNotMatchRegularExpression( "/\\b\\w+(n't|'re|'ve|'ll|'m)\\b/i", $text, $l['slug'] . ' uses a contraction.' );

			$post = get_page_by_path( $l['slug'], OBJECT, 'murtadd_letter' );
			$this->assertNotNull( $post );
			$this->assertSame( 'publish', $post->post_status );
			$meta = $GLOBALS['murtadd_written_meta'][ $post->ID ];
			$this->assertSame( 'open', $meta['_murtadd_letter_kind'] );
			$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}$/', $meta['_murtadd_letter_date'], 'A letter needs its dateline.' );
			$this->assertArrayNotHasKey( '_murtadd_letter_outlet', $meta, 'An open letter names no outlet: it was not published elsewhere.' );
		}
	}

	/** A deleted open letter stays deleted, and an editor's letter at the same slug is untouched. */
	public function test_letters_are_never_resurrected_or_overwritten() {
		$slugs = array_column( murtadd_seed_letters(), 'slug' );
		wp_insert_post(
			array(
				'post_type'    => 'murtadd_letter',
				'post_name'    => $slugs[1],
				'post_title'   => 'Mine',
				'post_content' => '<p>An editor wrote this.</p>',
			)
		);
		murtadd_seed_content();
		$this->assertSame( '<p>An editor wrote this.</p>', get_page_by_path( $slugs[1], OBJECT, 'murtadd_letter' )->post_content );

		murtadd_test_delete_post( 'murtadd_letter', $slugs[0] );
		$this->assertSame( 0, murtadd_seed_content()['letters'], 'A deleted letter was resurrected.' );
		$this->assertSame( 0, murtadd_seed_missing_count() );
	}

	/** Every link in a letter leads to something the seed creates. */
	public function test_letter_links_resolve() {
		$exists = array( '/doubts/', '/rebuttals/', '/fatwa/' );
		foreach ( array( 'home', 'blog', 'start-here', 'irtidad', 'about', 'faq', 'contact', 'privacy' ) as $p ) {
			$exists[] = '/' . $p . '/';
		}
		foreach ( murtadd_seed_doubts() as $d ) {
			$exists[] = '/doubts/' . $d['slug'] . '/';
		}
		foreach ( murtadd_seed_letters() as $l ) {
			preg_match_all( '/href="([^"]+)"/', $l['body'], $m );
			foreach ( $m[1] as $href ) {
				$this->assertStringStartsWith( 'https://murtadd.org/', $href );
				$this->assertContains( (string) parse_url( $href, PHP_URL_PATH ), $exists, $l['slug'] . " links to $href." );
			}
		}
	}

	/** A deleted seeded post must stay deleted across any number of re-runs. */
	public function test_deleted_content_is_never_resurrected() {
		murtadd_seed_content();

		murtadd_test_delete_post( 'murtadd_doubt', 'the-hadith-about-leaving' );
		murtadd_test_delete_post( 'murtadd_rebuttal', 'is-islam-a-cult' );

		$report = murtadd_seed_content();

		$this->assertSame( 0, $report['doubts'], 'A deleted doubt was resurrected.' );
		$this->assertSame( 0, $report['rebuttals'], 'A deleted rebuttal was resurrected.' );
		$this->assertNull( get_page_by_path( 'the-hadith-about-leaving', OBJECT, 'murtadd_doubt' ) );
		$this->assertNull( get_page_by_path( 'is-islam-a-cult', OBJECT, 'murtadd_rebuttal' ) );
	}

	/** The Setup tab must not nag forever about content the editor removed. */
	public function test_missing_count_ignores_deliberate_deletions() {
		murtadd_seed_content();
		$this->assertSame( 0, murtadd_seed_missing_count() );

		murtadd_test_delete_post( 'murtadd_rebuttal', 'is-islam-a-cult' );
		$this->assertSame( 0, murtadd_seed_missing_count(), 'A deleted seeded post counts as "missing" and the Setup tab will nag forever.' );
	}

	/**
	 * Installs seeded before the record existed must be adopted, not re-seeded.
	 *
	 * This is the live murtadd.org case: 31 posts created by 1.25.0, no
	 * murtadd_seeded_slugs option. The first 1.27.0 run must create only the
	 * genuinely new entries, adopt the rest into the record, and thereafter
	 * honour deletions of the pre-record posts too.
	 */
	public function test_pre_record_installs_are_adopted_without_duplicates() {
		// Simulate the live site: every 1.25.0-era slug present, no record.
		$old_rebuttals = array_diff(
			array_column( murtadd_seed_rebuttals(), 'slug' ),
			array( 'the-polemical-echo-chamber', 'the-scientific-miracles-genre', 'apostasy-and-the-classical-law' )
		);
		foreach ( $old_rebuttals as $slug ) {
			wp_insert_post(
				array(
					'post_type'  => 'murtadd_rebuttal',
					'post_name'  => $slug,
					'post_title' => $slug,
				)
			);
		}

		$report = murtadd_seed_content();
		$this->assertSame( 3, $report['rebuttals'], 'Setup on the live site must create exactly the three new rebuttals.' );

		// The pre-record posts are now adopted: deleting one must be honoured.
		murtadd_test_delete_post( 'murtadd_rebuttal', 'is-islam-a-cult' );
		$second = murtadd_seed_content();
		$this->assertSame( 0, $second['rebuttals'], 'An adopted pre-record post was resurrected after deletion.' );
	}
}
