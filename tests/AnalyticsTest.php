<?php
/**
 * Reading time.
 *
 * @package Murtadd\Tests
 */

// Run from the command line only (phpdbg included): on a web server this file does nothing.
if ( ! in_array( PHP_SAPI, array( 'cli', 'phpdbg' ), true ) ) {
	exit;
}

use PHPUnit\Framework\TestCase;

final class AnalyticsTest extends TestCase {

	public function test_reading_minutes_never_returns_zero_for_real_text() {
		$this->assertSame( 1, max( 1, (int) ceil( 40 / MURTADD_WPM ) ) );
	}

	/** A 400-word doubt, the cap, must read as a short thing. */
	public function test_a_capped_doubt_reads_in_two_minutes() {
		$this->assertSame( 2, (int) ceil( 400 / MURTADD_WPM ) );
	}

	public function test_wpm_is_conservative() {
		$this->assertLessThanOrEqual( 220, MURTADD_WPM, 'This is careful reading, not skimming.' );
	}
}
