<?php
/**
 * The ce_slug upgrade step: fills an empty counterpart on seeded posts only.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class CeSlugBackfillTest extends TestCase {

	protected function setUp(): void {
		murtadd_test_reset_posts();
		$GLOBALS['murtadd_view'] = array();
	}

	/** Posts with a ce_slug in the seed data and the slug recorded as seeded. */
	private function seeded_with_ce( $type, array $rows ) {
		$out = array();
		foreach ( $rows as $r ) {
			if ( ! empty( $r['ce_slug'] ) ) {
				$out[ $r['slug'] ] = $r['ce_slug'];
			}
		}
		return $out;
	}

	public function test_fills_only_empty_meta_on_seeded_posts() {
		murtadd_seed_content();
		$GLOBALS['murtadd_written_meta'] = array(); // A site seeded before the slugs existed.

		$doubt  = get_page_by_path( 'the-hadith-about-leaving', OBJECT, 'murtadd_doubt' );
		$edited = get_page_by_path( 'is-islam-a-cult', OBJECT, 'murtadd_rebuttal' );
		update_post_meta( $edited->ID, '_murtadd_ce_slug', 'an-editors-choice' );

		// A post with a seed slug the seeder never recorded is not the seeder's.
		$record = murtadd_seeded_slugs();
		$record['murtadd_doubt'] = array_values( array_diff( $record['murtadd_doubt'], array( 'can-i-come-back' ) ) );
		update_option( 'murtadd_seeded_slugs', $record );
		$foreign = get_page_by_path( 'can-i-come-back', OBJECT, 'murtadd_doubt' );

		$expected = count( $this->seeded_with_ce( 'r', murtadd_seed_rebuttals() ) ) + count( $this->seeded_with_ce( 'd', murtadd_seed_doubts() ) ) - 2;
		$this->assertSame( $expected, murtadd_backfill_ce_slugs() );

		$this->assertSame( 'kill-him-who-changes-religion', get_post_meta( $doubt->ID, '_murtadd_ce_slug', true ) );
		$this->assertSame( 'an-editors-choice', get_post_meta( $edited->ID, '_murtadd_ce_slug', true ), 'An editor\'s value was overwritten.' );
		$this->assertSame( '', get_post_meta( $foreign->ID, '_murtadd_ce_slug', true ), 'A post the seeder did not create was touched.' );
	}

	public function test_is_idempotent_and_skips_deleted_posts() {
		murtadd_seed_content();
		$GLOBALS['murtadd_written_meta'] = array();
		murtadd_test_delete_post( 'murtadd_rebuttal', 'the-age-of-aisha' );

		$this->assertGreaterThan( 0, murtadd_backfill_ce_slugs() );
		$this->assertSame( 0, murtadd_backfill_ce_slugs(), 'A second run changed something.' );

		// Even with the version record cleared, nothing is empty any more.
		update_option( 'murtadd_ce_slug_backfill', '' );
		$this->assertSame( 0, murtadd_backfill_ce_slugs() );
	}

	public function test_hooked_on_admin_init() {
		$this->assertNotNull( murtadd_test_hook_priority( 'admin_init', 'murtadd_backfill_ce_slugs' ) );
	}
}
