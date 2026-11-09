<?php
/**
 * Seeded content that follows the bundle (inc/content-sync.php): the decisions, the guards, the checksums
 * and the rules that keep an item the owner edited untouched.
 *
 * @package Murtadd\Tests
 */

// Run from the command line only (phpdbg included): on a web server this file does nothing.
if ( ! in_array( PHP_SAPI, array( 'cli', 'phpdbg' ), true ) ) {
	exit;
}

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/inc/seed-guides.php';
require_once dirname( __DIR__ ) . '/inc/seed-journal.php';
require_once dirname( __DIR__ ) . '/inc/content-sync.php';

final class ContentSyncTest extends TestCase {

	private function read( $path ) {
		return (string) file_get_contents( dirname( __DIR__ ) . '/' . $path );
	}

	public function test_an_untouched_item_takes_the_new_text() {
		$this->assertSame( 'update', murtadd_sync_decide( 'new', 'old', 'old', '' ) );
	}

	public function test_an_edited_item_is_never_overwritten_and_the_owner_is_told() {
		$this->assertSame( 'newer', murtadd_sync_decide( 'new', 'mine', 'old', '' ) );
	}

	public function test_an_item_with_no_record_counts_as_edited() {
		$this->assertSame( 'newer', murtadd_sync_decide( 'new', 'old', '', '' ) );
	}

	public function test_an_item_equal_to_the_bundle_is_current_even_with_no_record() {
		$this->assertSame( 'current', murtadd_sync_decide( 'same', 'same', '', '' ) );
	}

	public function test_a_choice_to_keep_is_remembered_for_that_version_only() {
		$this->assertSame( 'kept', murtadd_sync_decide( 'v2', 'mine', 'old', 'v2' ) );
		$this->assertSame( 'newer', murtadd_sync_decide( 'v3', 'mine', 'old', 'v2' ) );
	}

	public function test_a_dropped_untouched_item_goes_to_the_trash() {
		$this->assertSame( 'trash', murtadd_sync_decide_removed( 'a', 'a', '' ) );
	}

	public function test_a_dropped_edited_item_or_one_with_no_record_is_listed() {
		$this->assertSame( 'removed', murtadd_sync_decide_removed( 'b', 'a', '' ) );
		$this->assertSame( 'removed', murtadd_sync_decide_removed( 'a', '', '' ) );
		$this->assertSame( 'kept', murtadd_sync_decide_removed( 'b', 'a', 'removed' ) );
	}

	public function test_guards_stop_removals() {
		$this->assertSame( '', murtadd_sync_guard( false, true, 100, 100 ) );
		$this->assertSame( '', murtadd_sync_guard( false, true, 5, 0 ) );
		$this->assertNotSame( '', murtadd_sync_guard( true, true, 100, 100 ) );
		$this->assertNotSame( '', murtadd_sync_guard( false, false, 100, 100 ) );
		$this->assertSame( '', murtadd_sync_guard( false, true, 90, 100 ) );
		$this->assertNotSame( '', murtadd_sync_guard( false, true, 89, 100 ) );
	}

	public function test_the_shipped_checksums_match_the_seed_files() {
		$this->assertTrue( murtadd_sync_checksums_ok(), 'Run php bin/seed-checksums.php after a change to a seed file.' );
	}

	public function test_the_supersedes_map_ships_empty() {
		$this->assertSame( array(), murtadd_supersedes() );
	}

	public function test_the_bundle_covers_every_seeded_type_without_a_clash() {
		$bundle = murtadd_sync_bundle();
		$types  = array_unique( array_column( $bundle, 'type' ) );
		sort( $types );
		$this->assertSame( array( 'murtadd_doubt', 'murtadd_guide', 'murtadd_rebuttal', 'post' ), $types );
		$this->assertCount( count( murtadd_seed_rebuttals() ) + count( murtadd_seed_doubts() ) + count( murtadd_seed_guides() ) + count( murtadd_seed_journal() ), $bundle );
	}

	public function test_a_change_to_a_bundled_text_changes_its_fingerprint() {
		$entry = murtadd_seed_doubts()[0];
		$a     = murtadd_sync_fp( murtadd_sync_fields_from_bundle( 'murtadd_doubt', $entry ) );
		$entry['response'] .= ' More.';
		$b     = murtadd_sync_fp( murtadd_sync_fields_from_bundle( 'murtadd_doubt', $entry ) );
		$this->assertNotSame( $a, $b );
	}

	public function test_include_lists_lose_superseded_ids() {
		$this->assertNull( murtadd_superseded_cut_includes( array(), array( 5 ) ) );
		$this->assertSame( array( 3, 7 ), murtadd_superseded_cut_includes( array( 3, 5, 7 ), array( 5 ) ) );
		$this->assertSame( array( 0 ), murtadd_superseded_cut_includes( array( 5 ), array( 5 ) ) );
	}

	public function test_the_module_trashes_and_never_deletes() {
		$src = $this->read( 'inc/content-sync.php' );
		$this->assertStringNotContainsString( 'wp_delete_post', $src );
		$this->assertStringContainsString( 'wp_trash_post', $src );
	}

	public function test_only_items_the_theme_created_are_touched() {
		$src = $this->read( 'inc/content-sync.php' );
		$this->assertStringContainsString( "get_post_meta( \$post->ID, '_murtadd_seeded', true )", $src );
	}

	public function test_every_action_checks_a_capability_and_a_nonce() {
		$src = $this->read( 'inc/content-sync.php' );
		$this->assertSame( 2, substr_count( $src, "check_admin_referer( 'murtadd_sync_" ) );
		$this->assertGreaterThanOrEqual( 2, substr_count( $src, "current_user_can( 'manage_options' )" ) );
	}

	public function test_the_kill_switch_comes_before_any_choice() {
		$src = $this->read( 'inc/content-sync.php' );
		$this->assertMatchesRegularExpression( "/check_admin_referer\\( 'murtadd_sync_item' \\);\\s+if \\( murtadd_sync_disabled\\(\\) \\)/", $src );
		$this->assertMatchesRegularExpression( "/check_admin_referer\\( 'murtadd_sync_now' \\);\\s+if \\( murtadd_sync_disabled\\(\\) \\)/", $src );
		$this->assertStringContainsString( "! murtadd_sync_disabled() && in_array( \$row['action']", $src );
	}

	public function test_the_sync_forms_are_not_inside_the_settings_form() {
		$src = $this->read( 'inc/settings-page.php' );
		$this->assertStringNotContainsString( "function murtadd_render_upkeep_tab() {\n\tif ( function_exists( 'murtadd_render_content_sync' ) )", $src );
		$this->assertMatchesRegularExpression( "/echo '<\\/form>';\\s+if \\( 'security' === \\\$slug.*?if \\( 'upkeep' === \\\$slug \\) \\{[^}]*murtadd_render_content_sync/s", $src );
	}

	public function test_the_fingerprint_of_a_sync_includes_the_guard_state() {
		$this->assertStringContainsString( 'murtadd_sync_removal_block() );', $this->read( 'inc/content-sync.php' ) );
	}

	public function test_the_redirect_runs_first() {
		$this->assertStringContainsString( "'template_redirect', 'murtadd_superseded_redirect', 1", $this->read( 'inc/content-sync.php' ) );
	}

	public function test_the_unlist_module_includes_superseded_items() {
		$this->assertStringContainsString( 'murtadd_superseded_ids', $this->read( 'inc/unlist.php' ) );
	}
}
