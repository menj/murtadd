<?php
/**
 * Bad input never costs the reader text.
 *
 * Every regular expression with the /u flag gives up on malformed UTF-8 and
 * returns null or false. Text imported from the pre-2024 site can carry such
 * a byte. Whatever fails here must leave the text as it came, never empty it.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class RobustnessTest extends TestCase {

	private const JIZYA = 'https://murtadd.org/rebuttals/dhimmi-status-and-jizya/';

	protected function setUp(): void {
		$GLOBALS['murtadd_options']   = array();
		$GLOBALS['murtadd_test_caps'] = array();
		$GLOBALS['murtadd_test_user'] = 0;
	}

	private function entries() {
		return array(
			array( 'phrase' => 'jizya', 'url' => self::JIZYA, 'own' => true ),
			array( 'phrase' => 'Ibn Isḥāq', 'url' => 'https://bestofislam.org/sira-ibn-ishaq-hisham-tabari/', 'own' => false ),
		);
	}

	public function test_a_malformed_byte_never_blanks_the_post_in_the_verse_mapper() {
		$html = "<p>Opening.</p><p class=\"murtadd-ayah\" lang=\"ar\">قُلْ</p><p>Old text \xE2\x80 broken.</p>";
		$this->assertSame( $html, murtadd_quran_verses( $html ) );
	}

	public function test_a_malformed_byte_never_blanks_a_rail_verse() {
		// The bracket pass gives up on the bad byte; the mark mapping, which is
		// byte-safe, still fits the sukun to the face.
		$this->assertSame( "قُلۡ \xE2\x80", murtadd_quran_text_for_font( "قُلْ \xE2\x80" ) );
	}

	public function test_a_malformed_byte_never_empties_a_paragraph_in_the_linker() {
		$html = "<p>Legacy text \xC3\x28 on the jizya.</p><p>Then the jizya again.</p>";
		$out  = murtadd_ctx_apply( $html, $this->entries(), array() );
		$this->assertStringContainsString( "Legacy text \xC3\x28 on the jizya.", $out, 'The paragraph with the bad byte keeps its text.' );
		$this->assertStringContainsString( '<a class="murtadd-ctx-link" href="' . self::JIZYA . '">jizya</a> again', $out, 'The clean paragraph is still linked.' );
	}

	public function test_a_pattern_too_large_to_compile_still_links_and_never_blanks() {
		$entries = $this->entries();
		for ( $i = 0; $i < 9000; $i++ ) {
			$entries[] = array( 'phrase' => 'filler phrase number ' . $i . ' of many', 'url' => 'https://murtadd.org/x' . $i . '/', 'own' => true );
		}
		$out = murtadd_ctx_apply( '<p>Before.</p><p>On the jizya.</p>', $entries, array() );
		$this->assertStringContainsString( '<p>Before.</p>', $out );
		$this->assertStringContainsString( '>jizya</a>', $out, 'Phrases still link when the combined pattern is over the PCRE limit.' );
	}

	public function test_saving_bad_bytes_neither_fatals_nor_wipes_the_extra_phrases() {
		$saved = murtadd_sanitize_contextual_links(
			array(
				'extra'    => "jizya | /rebuttals/dhimmi-status-and-jizya/\nbad \xC3\x28 | /x/",
				'excluded' => "where-was-god-when-i-was-hurt\nbad-\xC3\x28",
			)
		);
		$this->assertStringContainsString( 'jizya | /rebuttals/dhimmi-status-and-jizya/', $saved['extra'] );
		$this->assertStringContainsString( 'where-was-god-when-i-was-hurt', $saved['excluded'] );
	}

	public function test_excluded_list_with_bad_bytes_is_still_read() {
		$this->assertTrue( murtadd_ctx_is_excluded( 'the-dare', "the-dare\nbad-\xC3\x28" ) );
	}

	public function test_lookup_finds_non_ascii_capitals_when_the_key_misses() {
		// Without mbstring the index key and the matched text lower-case
		// differently; the lookup must still find the entry.
		$index = array( 'ibn isḥāq' => $this->entries()[1] );
		$this->assertSame( $this->entries()[1], murtadd_ctx_lookup( $index, 'IBN ISḤĀQ' ) );
		$this->assertNull( murtadd_ctx_lookup( $index, 'Ibn Hisham' ) );
	}

	public function test_rest_user_routes_stay_for_logged_in_staff() {
		$GLOBALS['murtadd_test_user'] = 7;
		$GLOBALS['murtadd_test_caps'] = array( 'edit_posts' );
		$routes = array( '/wp/v2/posts' => array(), '/wp/v2/users' => array(), '/wp/v2/users/me' => array() );
		$this->assertSame( array_keys( $routes ), array_keys( murtadd_hardening_rest_endpoints( $routes ) ), 'The block editor needs /wp/v2/users for authors and editors.' );
	}

	public function test_rest_user_routes_still_go_for_visitors() {
		$routes = array( '/wp/v2/posts' => array(), '/wp/v2/users' => array() );
		$this->assertSame( array( '/wp/v2/posts' ), array_keys( murtadd_hardening_rest_endpoints( $routes ) ) );
	}
}
