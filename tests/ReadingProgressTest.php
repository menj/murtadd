<?php
/**
 * Reading progress bar: never on doubts, whatever is saved; the settings
 * are clamped; the bar's files ship with their .min twins and the bar keeps
 * its labelled progressbar role.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class ReadingProgressTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['murtadd_options'] = array();
	}

	public function test_defaults() {
		$s = murtadd_progress_settings();
		$this->assertSame( 1, $s['on'] );
		$this->assertSame( 'top', $s['position'] );
		$this->assertSame( 3, $s['height'] );
		$this->assertContains( 'murtadd_rebuttal', $s['types'], 'Rebuttals keep the bar they had.' );
		$this->assertNotContains( 'murtadd_doubt', $s['types'] );
	}

	public function test_doubts_are_never_offered_or_saved() {
		$this->assertNotContains( 'murtadd_doubt', murtadd_progress_allowed_types() );
		$this->assertNotContains( 'attachment', murtadd_progress_allowed_types() );
		$clean = murtadd_sanitize_progress( array( 'progress_types' => array( 'murtadd_doubt', 'post', 'nope' ) ) );
		$this->assertSame( array( 'post' ), $clean['progress_types'] );
		$GLOBALS['murtadd_options']['murtadd_content_display'] = array( 'progress_types' => array( 'murtadd_doubt', 'page' ) );
		$this->assertSame( array( 'page' ), murtadd_progress_settings()['types'], 'A doubt saved by hand is still refused.' );
	}

	public function test_values_are_clamped() {
		$clean = murtadd_sanitize_progress( array( 'progress_height' => '99', 'progress_position' => 'left', 'progress_colour' => 'red', 'progress_bar' => '1' ) );
		$this->assertSame( 12, $clean['progress_height'] );
		$this->assertSame( 'top', $clean['progress_position'] );
		$this->assertSame( '', $clean['progress_colour'] );
		$this->assertSame( 1, $clean['progress_bar'] );
		$this->assertSame( 0, murtadd_sanitize_content_display( array() )['progress_bar'] );
	}

	public function test_assets_ship_with_twins_and_the_role() {
		$root = dirname( __DIR__ );
		foreach ( array( 'progress.js', 'progress.min.js' ) as $file ) {
			$js = (string) file_get_contents( $root . '/assets/js/' . $file );
			$this->assertStringContainsString( "'progressbar'", $js, $file );
			$this->assertStringContainsString( 'aria-valuenow', $js, $file );
			$this->assertStringContainsString( 'passive: true', $js, $file );
			$this->assertStringContainsString( 'requestAnimationFrame', $js, $file );
			$this->assertStringContainsString( 'window.murtaddProgress', $js, $file );
		}
		foreach ( array( 'progress.css', 'progress.min.css' ) as $file ) {
			$css = (string) file_get_contents( $root . '/assets/css/' . $file );
			$this->assertStringContainsString( 'prefers-reduced-motion', $css, $file );
			$this->assertStringContainsString( '--wp-admin--admin-bar--height', $css, $file );
		}
		foreach ( array( 'longform.js', 'longform.min.js' ) as $file ) {
			$this->assertStringNotContainsString( "'progressbar'", (string) file_get_contents( $root . '/assets/js/' . $file ), $file . ' must not draw a second bar.' );
		}
	}
}
