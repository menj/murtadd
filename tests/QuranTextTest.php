<?php
/**
 * Qur'anic verses: the KFGQPC face, and the text mapped to its encoding.
 *
 * The face goes on Qur'anic verse Arabic only, never on a hadith or any other
 * Arabic. Verse text in the Tanzil encoding is mapped to the Complex's own
 * encoding as it renders, once, and nothing else is touched.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class QuranTextTest extends TestCase {

	private function css( $name ) {
		return file_get_contents( dirname( __DIR__ ) . '/assets/css/' . $name . '.css' );
	}

	public function test_font_ships_as_woff2_with_its_licence() {
		$dir = dirname( __DIR__ ) . '/assets/fonts/';
		$this->assertFileExists( $dir . 'kfgqpc-hafs-uthmanic-script.woff2' );
		$this->assertFileExists( $dir . 'kfgqpc-hafs-uthmanic-script-LICENSE.txt' );
		$this->assertSame( 'wOF2', file_get_contents( $dir . 'kfgqpc-hafs-uthmanic-script.woff2', false, null, 0, 4 ) );
		$this->assertStringContainsString( "url('../fonts/kfgqpc-hafs-uthmanic-script.woff2') format('woff2')", $this->css( 'tokens' ) );
	}

	public function test_face_is_used_only_through_the_quran_token_on_verse_selectors() {
		foreach ( glob( dirname( __DIR__ ) . '/assets/css/*.css' ) as $file ) {
			if ( false !== strpos( $file, '.min.css' ) ) {
				continue;
			}
			$css = preg_replace( '#/\*.*?\*/#s', '', file_get_contents( $file ) );
			$uses = array();
			preg_match_all( '/([^{}]+)\{[^}]*var\(--murtadd-font-quran\)[^}]*\}/', $css, $uses );
			foreach ( $uses[1] as $selector ) {
				$selector = trim( $selector );
				// Only text the mapper has fitted to the face's encoding gets the
				// face; unmapped Tanzil text in it shows round sukun and stray
				// ornaments, so it keeps the Arabic stack.
				$this->assertMatchesRegularExpression( '/^(\.murtadd-ayah\[data-quran-encoding="kfgqpc"\]:lang\(ar\)|\.murtadd-verse p\[data-quran-encoding="kfgqpc"\]:lang\(ar\))$/', $selector, basename( $file ) . ': ' . $selector );
			}
			if ( 'tokens.css' !== basename( $file ) ) {
				$this->assertStringNotContainsString( 'KFGQPC', $css, basename( $file ) . ' names the face directly' );
			}
		}
		$this->assertStringContainsString( ':lang(ar) { font-family: var(--murtadd-font-arabic)', $this->css( 'main' ) );
	}

	public function test_min_twins_carry_the_face_and_the_rules() {
		$this->assertStringContainsString( 'kfgqpc-hafs-uthmanic-script.woff2', $this->css( 'tokens.min' ) );
		$this->assertStringContainsString( '.murtadd-ayah[data-quran-encoding="kfgqpc"]:lang(ar){font-family:var(--murtadd-font-quran)}', $this->css( 'main.min' ) );
		$this->assertStringContainsString( '.murtadd-verse p[data-quran-encoding="kfgqpc"]:lang(ar){font-family:var(--murtadd-font-quran)}', $this->css( 'rail.min' ) );
	}

	public function test_tanzil_marks_map_to_the_complex_encoding() {
		$out = murtadd_quran_text_for_font( "قُلْ تَعَالَوْا۟" );
		$this->assertSame( "قُلۡ تَعَالَوۡاْ", $out );
		$this->assertStringNotContainsString( "\u{06DF}", $out );
	}

	public function test_text_already_in_the_complex_encoding_is_left_alone() {
		$this->assertSame( "قُلۡ تَعَالَوۡاْ", murtadd_quran_text_for_font( "قُلۡ تَعَالَوۡاْ" ) );
	}

	public function test_brackets_round_an_ayah_number_are_dropped() {
		$this->assertSame( "وَلَدًا ٨٨", murtadd_quran_text_for_font( "وَلَدًا ﴿٨٨﴾" ) );
	}

	public function test_only_verse_elements_are_mapped_and_marked() {
		$html = '<p class="murtadd-ayah" lang="ar">قَالُوا۟ وَلَدًا</p><p class="murtadd-hadith" lang="ar">سُكْنٌ</p><p class="murtadd-ayah-tr">“Say”</p>';
		$out  = murtadd_quran_verses( $html );
		$this->assertStringContainsString( '<p class="murtadd-ayah" lang="ar" data-quran-encoding="kfgqpc">قَالُواْ وَلَدًا</p>', $out );
		$this->assertStringContainsString( '<p class="murtadd-hadith" lang="ar">سُكْنٌ</p>', $out );
		$this->assertStringContainsString( '<p class="murtadd-ayah-tr">“Say”</p>', $out );
	}

	public function test_a_second_pass_changes_nothing() {
		// A rounded zero and no sukun: after one pass the text holds no
		// U+06E1, so only the marker keeps a second pass from remapping it.
		$once  = murtadd_quran_verses( '<p class="murtadd-ayah" lang="ar">قَالُوا۟ وَلَدًا</p>' );
		$twice = murtadd_quran_verses( $once );
		$this->assertSame( $once, $twice );
		$this->assertStringNotContainsString( "\u{06E1}", $twice );
	}

	public function test_hooked_before_the_contextual_links() {
		$this->assertSame( 6, murtadd_test_hook_priority( 'the_content', 'murtadd_quran_verses' ) );
	}

	public function test_arabic_detection_for_the_rail_verse() {
		$this->assertTrue( murtadd_is_arabic( 'لَا إِكْرَاهَ فِي الدِّينِ' ) );
		$this->assertFalse( murtadd_is_arabic( 'Let there be no compulsion in religion.' ) );
	}
}
