<?php
/**
 * The title tag: "<page title> | <site name>", under 60 characters.
 *
 * @package Murtadd\Tests
 */

// Run from the command line only: on a web server this file does nothing.
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

use PHPUnit\Framework\TestCase;

final class TitleTest extends TestCase {

	public function test_title_is_page_then_site_name() {
		$this->assertSame( 'About | Murtadd', murtadd_build_title( 'About' ) );
		$this->assertSame( 'Doubts, page 2 | Murtadd', murtadd_build_title( 'Doubts', '', 2 ) );
		$this->assertSame( 'Murtadd', murtadd_build_title( '' ) );
	}

	public function test_listed_title_wins_and_a_long_one_cuts_at_a_word() {
		$long = str_repeat( 'Longword ', 10 );
		$this->assertSame( 'Short | Murtadd', murtadd_build_title( 'Shared', 'Short' ) );
		$cut = murtadd_build_title( $long );
		$this->assertLessThan( 60, mb_strlen( $cut ) );
		$this->assertStringEndsWith( 'Longword | Murtadd', $cut );
	}

	/**
	 * Every seeded title, with the longest site name the audit assumed.
	 */
	public function test_every_seeded_title_fits_without_a_cut() {
		$suffix = ' | murtadd.org';
		$budget = MURTADD_TITLE_MAX - mb_strlen( $suffix );
		$short  = murtadd_short_titles();
		$titles = array();
		foreach ( array( 'guides' => 'murtadd_guide', 'doubts' => 'murtadd_doubt', 'rebuttals' => 'murtadd_rebuttal' ) as $file => $type ) {
			foreach ( json_decode( file_get_contents( dirname( __DIR__ ) . "/inc/data/seed/$file.json" ), true ) as $e ) {
				$titles[ "$file {$e['slug']}" ] = $short[ "$type/{$e['slug']}" ] ?? $e['title'];
			}
		}
		foreach ( json_decode( file_get_contents( dirname( __DIR__ ) . '/inc/data/seed/journal.json' ), true ) as $e ) {
			$titles[ "journal {$e['slug']}" ] = $short[ "post/{$e['slug']}" ] ?? $e['title'];
		}
		foreach ( murtadd_seed_page_bodies() as $slug => $body ) {
			$titles[ "page $slug" ] = $short[ "page/$slug" ] ?? ucfirst( $slug );
		}
		foreach ( $titles as $key => $title ) {
			$this->assertLessThanOrEqual( $budget, mb_strlen( $title ), "$key: \"$title\" needs a short title." );
		}
		$this->assertSame( count( $titles ), count( array_unique( $titles ) ), 'Titles are unique.' );
	}
}
