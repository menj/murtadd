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
 * letters" and "press Create anything missing".
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
	}

	/** A deleted seeded post must stay deleted across any number of re-runs. */
	public function test_deleted_content_is_never_resurrected() {
		murtadd_seed_content();

		murtadd_test_delete_post( 'murtadd_letter', 'placeholder-letter-one' );
		murtadd_test_delete_post( 'murtadd_letter', 'placeholder-letter-two' );
		murtadd_test_delete_post( 'murtadd_rebuttal', 'is-islam-a-cult' );

		$report = murtadd_seed_content();

		$this->assertSame( 0, $report['letters'], 'Deleted placeholder letters were resurrected.' );
		$this->assertSame( 0, $report['rebuttals'], 'A deleted rebuttal was resurrected.' );
		$this->assertNull( get_page_by_path( 'placeholder-letter-one', OBJECT, 'murtadd_letter' ) );
		$this->assertNull( get_page_by_path( 'is-islam-a-cult', OBJECT, 'murtadd_rebuttal' ) );
	}

	/** The Setup tab must not nag forever about content the editor removed. */
	public function test_missing_count_ignores_deliberate_deletions() {
		murtadd_seed_content();
		$this->assertSame( 0, murtadd_seed_missing_count() );

		murtadd_test_delete_post( 'murtadd_letter', 'placeholder-letter-one' );
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
