<?php
/**
 * Renamed slugs: old addresses resolve to the new ones, and live posts are
 * renamed in place once, without the seeder making a second copy.
 *
 * @package Murtadd\Tests
 */

// Run from the command line only: on a web server this file does nothing.
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

use PHPUnit\Framework\TestCase;

final class SlugRenameTest extends TestCase {

	protected function setUp(): void {
		murtadd_test_reset_posts();
	}

	/** Every old path resolves to its new slug. */
	public function test_old_paths_resolve_to_new_slugs() {
		$cases = array(
			'/doubts/quran-4-34-wife-beating/'    => array( 'murtadd_doubt', 'quran-wife-beating-verse' ),
			'doubts/quran-4-34-wife-beating'      => array( 'murtadd_doubt', 'quran-wife-beating-verse' ),
			'/rebuttals/quran-4-34-in-context/'   => array( 'murtadd_rebuttal', 'quran-wife-beating-verse-in-context' ),
			'/Rebuttals/Quran-4-34-In-Context/'   => array( 'murtadd_rebuttal', 'quran-wife-beating-verse-in-context' ),
		);
		foreach ( $cases as $path => $want ) {
			$hit = murtadd_renamed_slug_for_path( $path );
			$this->assertNotNull( $hit, $path );
			$this->assertSame( $want[0], $hit['type'], $path );
			$this->assertSame( $want[1], $hit['new'], $path );
		}

		$this->assertNull( murtadd_renamed_slug_for_path( '/doubts/quran-wife-beating-verse/' ) );
		$this->assertNull( murtadd_renamed_slug_for_path( '/rebuttals/quran-4-34-wife-beating/' ), 'The base must match the post type.' );
		$this->assertNull( murtadd_renamed_slug_for_path( '/quran-4-34-in-context/' ) );
	}

	/** Every new slug is in the seed data, and no old slug is seeded. */
	public function test_new_slugs_are_seeded() {
		$seeded = array(
			'murtadd_doubt'    => array_column( murtadd_seed_doubts(), 'slug' ),
			'murtadd_rebuttal' => array_column( murtadd_seed_rebuttals(), 'slug' ),
		);
		foreach ( murtadd_slug_renames() as $type => $pairs ) {
			foreach ( $pairs as $old => $new ) {
				$this->assertContains( $new, $seeded[ $type ] );
				$this->assertNotContains( $old, $seeded[ $type ] );
				$this->assertDoesNotMatchRegularExpression( '/\d/', $new, 'Addresses carry no digits.' );
			}
		}
	}

	/** A live post is renamed in place and the seeder adopts it. */
	public function test_live_post_is_renamed_in_place_and_adopted() {
		$id = wp_insert_post( array( 'post_type' => 'murtadd_rebuttal', 'post_name' => 'quran-4-34-in-context', 'post_title' => 'x' ) );
		murtadd_mark_slug_seeded( 'murtadd_rebuttal', 'quran-4-34-in-context' );

		$this->assertSame( 1, murtadd_rename_seeded_slugs() );
		$this->assertNull( get_page_by_path( 'quran-4-34-in-context', OBJECT, 'murtadd_rebuttal' ) );
		$this->assertSame( $id, get_page_by_path( 'quran-wife-beating-verse-in-context', OBJECT, 'murtadd_rebuttal' )->ID );
		$this->assertTrue( murtadd_slug_was_seeded( 'murtadd_rebuttal', 'quran-wife-beating-verse-in-context' ) );

		// Once per map: a second run does nothing.
		$this->assertSame( 0, murtadd_rename_seeded_slugs() );
	}

	/** A seeded post an editor deleted stays deleted under its new name. */
	public function test_deleted_post_is_not_resurrected() {
		murtadd_mark_slug_seeded( 'murtadd_doubt', 'quran-4-34-wife-beating' );
		murtadd_rename_seeded_slugs();
		$this->assertTrue( murtadd_slug_was_seeded( 'murtadd_doubt', 'quran-wife-beating-verse' ) );
	}

	/** A fresh install renames nothing and leaves the new slug to the seeder. */
	public function test_fresh_install_leaves_the_seeder_free() {
		$this->assertSame( 0, murtadd_rename_seeded_slugs() );
		$this->assertFalse( murtadd_slug_was_seeded( 'murtadd_doubt', 'quran-wife-beating-verse' ) );
	}

	/** A failed rename records nothing, is retried, and blocks a second copy. */
	public function test_failed_rename_is_not_recorded_and_retried() {
		$id = wp_insert_post( array( 'post_type' => 'murtadd_rebuttal', 'post_name' => 'quran-4-34-in-context', 'post_title' => 'x' ) );
		murtadd_mark_slug_seeded( 'murtadd_rebuttal', 'quran-4-34-in-context' );

		$GLOBALS['murtadd_fail_update'] = true;
		$this->assertSame( 0, murtadd_rename_seeded_slugs() );
		unset( $GLOBALS['murtadd_fail_update'] );

		$this->assertFalse( murtadd_slug_was_seeded( 'murtadd_rebuttal', 'quran-wife-beating-verse-in-context' ) );
		$this->assertTrue( murtadd_slug_rename_pending( 'murtadd_rebuttal', 'quran-wife-beating-verse-in-context' ) );
		$this->assertSame( array( 'type' => 'murtadd_rebuttal', 'old' => 'quran-4-34-in-context' ), murtadd_pending_rename_for_path( '/rebuttals/quran-wife-beating-verse-in-context/' ) );

		// The next request retries and succeeds.
		$this->assertSame( 1, murtadd_rename_seeded_slugs() );
		$this->assertSame( $id, get_page_by_path( 'quran-wife-beating-verse-in-context', OBJECT, 'murtadd_rebuttal' )->ID );
		$this->assertTrue( murtadd_slug_was_seeded( 'murtadd_rebuttal', 'quran-wife-beating-verse-in-context' ) );
		$this->assertFalse( murtadd_slug_rename_pending( 'murtadd_rebuttal', 'quran-wife-beating-verse-in-context' ) );
	}
}
