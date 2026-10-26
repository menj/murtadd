<?php
/**
 * Seed data is JSON, read and decoded, never executed.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class SeedDataTest extends TestCase {

	/** Every seed file is valid JSON, and the data directory holds no PHP. */
	public function test_seed_files_are_json_only() {
		$dir = dirname( __DIR__ ) . '/inc/data/seed';
		foreach ( array( 'topics', 'rebuttals', 'doubts', 'pages' ) as $name ) {
			$data = json_decode( (string) file_get_contents( "$dir/$name.json" ), true );
			$this->assertIsArray( $data, "$name.json does not decode." );
			$this->assertNotEmpty( $data, "$name.json is empty." );
		}
		$this->assertSame( array(), glob( "$dir/*.php" ) );
	}

	/** The loaders return what the files hold. */
	public function test_loaders_return_the_file_contents() {
		$this->assertSame( murtadd_seed_read_json( 'rebuttals' ), murtadd_seed_rebuttals() );
		$this->assertSame( murtadd_seed_read_json( 'doubts' ), murtadd_seed_doubts() );
		$this->assertSame( murtadd_seed_read_json( 'topics' ), murtadd_starter_topics() );
		$this->assertSame( array_keys( murtadd_seed_read_json( 'pages' ) ), array_keys( murtadd_seed_page_bodies() ) );
	}

	/** Every {{url:}} token is resolved through home_url(). */
	public function test_page_links_are_resolved() {
		$raw = implode( '', murtadd_seed_read_json( 'pages' ) );
		$this->assertStringContainsString( '{{url:/irtidad/}}', $raw );
		foreach ( murtadd_seed_page_bodies() as $slug => $html ) {
			$this->assertStringNotContainsString( '{{', $html, "$slug keeps a token." );
		}
		$this->assertStringContainsString( 'href="https://murtadd.org/irtidad/"', murtadd_seed_page_bodies()['faq'] );
	}

	/** Blank page copy is dropped, so it can never replace a page with nothing. */
	public function test_blank_page_copy_is_dropped() {
		$this->assertSame( array( 'faq' => '<p>x</p>' ), murtadd_seed_drop_blank( array( 'about' => '', 'contact' => " \n\t", 'faq' => '<p>x</p>' ) ) );
		foreach ( murtadd_seed_page_bodies() as $slug => $html ) {
			$this->assertNotSame( '', trim( $html ), "$slug is blank." );
		}
	}

	/** A missing file or a malformed entry loads as nothing, not as an error. */
	public function test_bad_data_loads_as_empty() {
		$this->assertSame( array(), murtadd_seed_read_json( 'no-such-file' ) );
		$this->assertSame( array(), murtadd_seed_load_entries( 'no-such-file', array( 'slug' => 'string' ) ) );
		$this->assertFalse( murtadd_seed_entry_valid( 'x', array( 'slug' => 'string' ) ) );
		$this->assertFalse( murtadd_seed_entry_valid( array( 'slug' => 1 ), array( 'slug' => 'string' ) ) );
		$this->assertFalse( murtadd_seed_entry_valid( array( 'slug' => 'a', 'ce_slug' => array() ), array( 'slug' => 'string' ), array( 'ce_slug' => 'string' ) ) );
		$this->assertTrue( murtadd_seed_entry_valid( array( 'slug' => 'a' ), array( 'slug' => 'string' ), array( 'ce_slug' => 'string' ) ) );
		// Lists are checked item by item: a malformed source row or topic rejects the entry.
		$src = array( 'sources' => 'sources', 'topics' => 'strings' );
		$this->assertTrue( murtadd_seed_entry_valid( array( 'sources' => array( array( 'citation_text' => 'a', 'url' => '' ) ), 'topics' => array( 'x' ) ), $src ) );
		$this->assertFalse( murtadd_seed_entry_valid( array( 'sources' => array( array( 'citation_text' => array(), 'url' => '' ) ), 'topics' => array( 'x' ) ), $src ) );
		$this->assertFalse( murtadd_seed_entry_valid( array( 'sources' => array( 'text' ), 'topics' => array( 'x' ) ), $src ) );
		$this->assertFalse( murtadd_seed_entry_valid( array( 'sources' => array(), 'topics' => array( 1 ) ), $src ) );
	}
}
