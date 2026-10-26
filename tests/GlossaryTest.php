<?php
/**
 * Glossary of terms: the first mention of an Arabic or Malay term carries a
 * short definition and its source.
 *
 * The failures worth pinning are the quiet ones: a definition inside a verse
 * or a link, a term defined twice down the page, a definition on a doubt
 * written care-first, a definition the data cannot back.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class GlossaryTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['murtadd_options']       = array();
		$GLOBALS['murtadd_test_request']  = array();
		$GLOBALS['murtadd_test_types']    = array();
		$GLOBALS['murtadd_test_terms']    = array();
		$GLOBALS['murtadd_test_fields']   = array();
		$GLOBALS['murtadd_test_meta']     = array();
		$GLOBALS['murtadd_test_enqueued'] = array();
	}

	private function apply( $html, array &$seen = null ) {
		$seen = $seen ?? array();
		return murtadd_glossary_apply( $html, $seen, 'murtadd-term-t' );
	}

	private function count_terms( $html ) {
		return substr_count( $html, '<span class="murtadd-term">' );
	}

	/** Every seeded entry and page, as text, for checking the data against. */
	private function seed_text() {
		$text = '';
		foreach ( array_merge( murtadd_seed_rebuttals(), murtadd_seed_doubts() ) as $e ) {
			foreach ( array( 'body', 'claim', 'statement', 'response' ) as $k ) {
				$text .= ' ' . ( $e[ $k ] ?? '' );
			}
		}
		foreach ( murtadd_seed_page_bodies() as $body ) {
			$text .= ' ' . $body;
		}
		return html_entity_decode( strip_tags( str_replace( "\\'", "'", $text ) ), ENT_QUOTES, 'UTF-8' );
	}

	public function test_data_is_complete_and_in_house_style() {
		$terms = murtadd_glossary_terms();
		$this->assertGreaterThanOrEqual( 30, count( $terms ) );
		foreach ( $terms as $slug => $t ) {
			$this->assertNotSame( '', trim( $t['definition'] ), "$slug has no definition." );
			$this->assertNotSame( '', trim( $t['source'] ), "$slug has no source." );
			foreach ( array( $t['definition'], $t['source'] ) as $s ) {
				$this->assertStringNotContainsString( '—', $s, "$slug has an em dash." );
				$this->assertDoesNotMatchRegularExpression( '/\bactually\b/i', $s, "$slug uses \"actually\"." );
				$this->assertDoesNotMatchRegularExpression( "/\b\w+n't\b|\b(?:it|that|there|what)'s\b/i", $s, "$slug has a contraction." );
			}
		}
	}

	/** Every term is one the entries use, so the glossary defines what readers meet. */
	public function test_every_term_appears_in_the_seed() {
		require_once __DIR__ . '/../inc/seed-data.php';
		$text = murtadd_glossary_key( $this->seed_text() );
		foreach ( murtadd_glossary_terms() as $slug => $t ) {
			$found = false;
			foreach ( array_merge( array( $t['term'] ), $t['aliases'] ) as $s ) {
				if ( preg_match( '/(?<![\p{L}\p{N}])' . preg_quote( murtadd_glossary_key( $s ), '/' ) . '(?![\p{L}\p{N}])/u', $text ) ) {
					$found = true;
					break;
				}
			}
			$this->assertTrue( $found, "$slug is not used anywhere in the seeded content." );
		}
	}

	/** A source given as "(this site)" names an entry or page that exists. */
	public function test_site_sources_name_real_entries() {
		require_once __DIR__ . '/../inc/seed-data.php';
		$titles = array( 'Irtidad' );
		foreach ( array_merge( murtadd_seed_rebuttals(), murtadd_seed_doubts() ) as $e ) {
			$titles[] = str_replace( "\\'", "'", $e['title'] );
		}
		foreach ( murtadd_glossary_terms() as $slug => $t ) {
			if ( preg_match( '/^(.*?) \(this site\)\.$/', $t['source'], $m ) || preg_match( '/as cited in (.*?)\.$/', $t['source'], $m ) || preg_match( '/discussed in (.*?)\.$/', $t['source'], $m ) ) {
				$this->assertContains( $m[1], $titles, "$slug cites an entry that does not exist: {$m[1]}." );
			}
		}
	}

	public function test_first_mention_is_defined_once() {
		$out = $this->apply( '<p>The word irtidad means turning back.</p><p>Irtidad again, and irtidad.</p>' );
		$this->assertSame( 1, $this->count_terms( $out ) );
		$this->assertStringContainsString( '>irtidad</button>', $out );
		$this->assertStringContainsString( '<p>Irtidad again, and irtidad.</p>', $out );
	}

	public function test_markup_is_an_accessible_button_and_tooltip() {
		$out = $this->apply( '<p>On mahr.</p>' );
		$this->assertMatchesRegularExpression( '#<button type="button" class="murtadd-term__word" aria-describedby="(murtadd-term-t-\d+)" aria-expanded="false">mahr</button><span class="murtadd-term__tip" id="\1" role="tooltip" hidden>#', $out );
		$this->assertStringContainsString( '<span class="murtadd-term__def">', $out );
		$this->assertStringContainsString( '<span class="murtadd-term__src">The inheritance shares (this site).</span>', $out );
	}

	public function test_skip_regions_are_left_alone_and_the_text_mention_is_defined() {
		$html = '<h2>Why hudud</h2>'
			. '<p><a href="/x/">hudud</a></p>'
			. '<p><a class="murtadd-ctx-link" href="/y/">jizya</a></p>'
			. '<blockquote><p>hudud and jizya</p></blockquote>'
			. '<p class="murtadd-ayah">hudud</p>'
			. '<p lang="ar">hudud</p>'
			. '<div class="murtadd-sources-panel"><p>hudud</p></div>'
			. '<p class="murtadd-disclaimer">hudud</p>'
			. '<p><button>hudud</button></p>'
			. '<p>The hudud apply here.</p>';
		$out = $this->apply( $html );
		$this->assertSame( 1, $this->count_terms( $out ) );
		$this->assertStringContainsString( '<p>The <span class="murtadd-term"><button', $out );
		$this->assertStringContainsString( '<h2>Why hudud</h2>', $out );
		$this->assertStringContainsString( '<a class="murtadd-ctx-link" href="/y/">jizya</a>', $out );
	}

	public function test_a_mention_the_text_glosses_itself_is_skipped() {
		$out = $this->apply( '<p>Where hudud (fixed prescribed penalties) apply.</p><p>Later, hudud again.</p>' );
		$this->assertStringContainsString( 'Where hudud (fixed prescribed penalties)', $out );
		$this->assertSame( 1, $this->count_terms( $out ) );
		$this->assertStringContainsString( '<p>Later, <span class="murtadd-term">', $out );
	}

	public function test_apostrophe_forms_and_capitals_all_match() {
		foreach ( array( "'ayb", "\u{2018}ayb", '&#8216;ayb', 'aib', "Ma\u{2019}" ) as $i => $form ) {
			$seen = array();
			$out  = murtadd_glossary_apply( '<p>The ' . $form . ' of it.</p>', $seen, 'x' );
			if ( 4 === $i ) {
				$this->assertSame( 0, $this->count_terms( $out ), 'A stray apostrophe matched a term.' );
				continue;
			}
			$this->assertSame( 1, $this->count_terms( $out ), "$form was not matched." );
			$this->assertArrayHasKey( 'ayb', $seen );
		}
		$out = $this->apply( '<p>Iman requires assent.</p>' );
		$this->assertStringContainsString( '>Iman</button>', $out, 'The text keeps its own capitals.' );
	}

	public function test_whole_words_only() {
		$out = $this->apply( '<p>Imani and Imanuel, riba-free and fiqhi.</p>' );
		$this->assertSame( 1, $this->count_terms( $out ), 'Only "riba" before a hyphen is a whole word.' );
		$this->assertStringContainsString( '>riba</button>', $out );
		$this->assertStringContainsString( '</span></span>-free and fiqhi.', $out );
	}

	public function test_seen_terms_carry_across_render_paths() {
		$seen = array();
		murtadd_glossary_apply( '<p>On nafaqah.</p>', $seen, 'x' );
		$out = murtadd_glossary_apply( '<p>Nafaqah again, and mahr.</p>', $seen, 'x' );
		$this->assertStringNotContainsString( '>Nafaqah</button>', $out );
		$this->assertStringContainsString( '>mahr</button>', $out );
	}

	public function test_ids_are_unique_on_a_page() {
		$seen = array();
		$out  = murtadd_glossary_apply( '<p>mahr and nafaqah and riba</p>', $seen, 'x' );
		preg_match_all( '/ id="([^"]+)"/', $out, $m );
		$this->assertCount( 3, $m[1] );
		$this->assertSame( $m[1], array_unique( $m[1] ) );
	}

	public function test_invalid_utf8_keeps_the_text() {
		$out = $this->apply( "<p>Bad \xC3 byte and mahr.</p><p>Second.</p>" );
		$this->assertStringContainsString( 'Second.', $out );
		$this->assertStringContainsString( 'byte and', $out );
	}

	/** Render path: queried entry, the switch, the care-first doubts, feeds. */
	public function test_render_respects_request_switch_and_care_first() {
		$html = '<p>On mahr.</p>';

		$GLOBALS['murtadd_test_request'] = array( 'queried' => 501 );
		$GLOBALS['murtadd_test_types']   = array( 501 => 'murtadd_rebuttal' );
		$this->assertSame( 1, $this->count_terms( murtadd_glossary_render( $html, 501 ) ) );

		$GLOBALS['murtadd_test_request'] = array( 'queried' => 999 );
		$this->assertSame( $html, murtadd_glossary_render( $html, 502 ), 'Another entry on the page got definitions.' );

		$GLOBALS['murtadd_test_request'] = array( 'queried' => 503, 'feed' => true );
		$this->assertSame( $html, murtadd_glossary_render( $html, 503 ), 'A feed got definitions.' );

		$GLOBALS['murtadd_test_request'] = array( 'queried' => 504 );
		$GLOBALS['murtadd_test_types']   = array( 504 => 'murtadd_doubt', 505 => 'murtadd_doubt', 506 => 'murtadd_doubt' );
		$GLOBALS['murtadd_test_terms']   = array( 504 => array( 'emotional' ), 505 => array( 'identity' ), 506 => array( 'intellectual' ) );
		$this->assertSame( $html, murtadd_glossary_render( $html, 504 ), 'An Emotional doubt got definitions.' );
		$GLOBALS['murtadd_test_request'] = array( 'queried' => 505 );
		$this->assertSame( $html, murtadd_glossary_render( $html, 505 ), 'An Identity doubt got definitions.' );
		$GLOBALS['murtadd_test_request'] = array( 'queried' => 506 );
		$this->assertSame( 1, $this->count_terms( murtadd_glossary_render( $html, 506 ) ) );

		$GLOBALS['murtadd_options']['murtadd_content_display'] = array( 'glossary_terms' => 0 );
		$GLOBALS['murtadd_test_request'] = array( 'queried' => 507 );
		$this->assertSame( $html, murtadd_glossary_render( $html, 507 ), 'The switch on the Content tab was ignored.' );
	}

	/** A term defined in the short response is not defined again in the body. */
	public function test_render_defines_once_per_entry() {
		$GLOBALS['murtadd_test_request'] = array( 'queried' => 601 );
		$GLOBALS['murtadd_test_types']   = array( 601 => 'murtadd_doubt' );
		$first  = murtadd_glossary_render( '<p>On mahr.</p>', 601 );
		$second = murtadd_glossary_render( '<p>Mahr again.</p>', 601 );
		$this->assertSame( 1, $this->count_terms( $first ) );
		$this->assertSame( 0, $this->count_terms( $second ) );
	}

	public function test_assets_load_only_where_a_term_appears() {
		$GLOBALS['murtadd_test_request'] = array( 'queried' => 701 );
		$GLOBALS['murtadd_test_types']   = array( 701 => 'murtadd_rebuttal', 702 => 'murtadd_rebuttal', 703 => 'murtadd_doubt', 704 => 'murtadd_doubt' );
		$GLOBALS['murtadd_test_fields']  = array( 701 => array( 'post_content' => '<p>On riba.</p>' ), 702 => array( 'post_content' => '<p>Nothing to define.</p>' ) );
		$GLOBALS['murtadd_test_meta']    = array(
			703 => array( '_murtadd_short_response' => 'On tudung.' ),
			704 => array( '_murtadd_short_response' => 'On tudung.' ),
		);
		$GLOBALS['murtadd_test_terms']   = array( 704 => array( 'emotional' ) );

		murtadd_glossary_enqueue();
		$this->assertSame( array( 'style:murtadd-glossary', 'script:murtadd-glossary' ), $GLOBALS['murtadd_test_enqueued'] );

		foreach ( array( 702, 704 ) as $id ) {
			$GLOBALS['murtadd_test_enqueued'] = array();
			$GLOBALS['murtadd_test_request']  = array( 'queried' => $id );
			murtadd_glossary_enqueue();
			$this->assertSame( array(), $GLOBALS['murtadd_test_enqueued'], "Assets loaded on entry $id." );
		}

		$GLOBALS['murtadd_test_enqueued'] = array();
		$GLOBALS['murtadd_test_request']  = array( 'queried' => 703 );
		murtadd_glossary_enqueue();
		$this->assertContains( 'script:murtadd-glossary', $GLOBALS['murtadd_test_enqueued'], 'A term in the short response did not load the assets.' );
	}

	public function test_setting_sanitises_to_a_flag() {
		$on  = murtadd_sanitize_content_display( array( 'glossary_terms' => 'yes' ) );
		$off = murtadd_sanitize_content_display( array() );
		$this->assertSame( 1, $on['glossary_terms'] );
		$this->assertSame( 0, $off['glossary_terms'] );
	}

	public function test_min_twins_exist_and_are_smaller() {
		foreach ( array( 'css/glossary.css', 'js/glossary.js' ) as $f ) {
			$src = __DIR__ . '/../assets/' . $f;
			$min = preg_replace( '/\.(css|js)$/', '.min.$1', $src );
			$this->assertFileExists( $min );
			$this->assertLessThan( filesize( $src ), filesize( $min ) );
		}
	}
}
