<?php
/**
 * Contextual links: the first mention of a known phrase links to the page that
 * treats it, here or on a sister site.
 *
 * The failures worth pinning are the quiet ones. A link inside a heading or a
 * Qur'anic verse renders and nobody files a bug. A shared-map path that 404s
 * here is invisible from the other six sites. A care-first doubt that sprouts
 * a link to a rebuttal breaks the one promise those entries make. Each test
 * below is one of those.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class ContextualLinksTest extends TestCase {

	private const REBUTTAL = 'https://murtadd.org/rebuttals/quran-4-34-in-context/';
	private const JIZYA    = 'https://murtadd.org/rebuttals/dhimmi-status-and-jizya/';
	private const THOMAS   = 'https://injil.help/gospels/thomas/';
	private const KAABA    = 'https://abrahamic-religions.com/journal/the-kaaba/';

	protected function setUp(): void {
		$GLOBALS['murtadd_options'] = array();
	}

	private function entries() {
		return array(
			array( 'phrase' => "Qur'an 4:34", 'url' => self::REBUTTAL, 'own' => true ),
			array( 'phrase' => 'jizya', 'url' => self::JIZYA, 'own' => true ),
			array( 'phrase' => 'dhimmi', 'url' => self::JIZYA, 'own' => true ),
			array( 'phrase' => 'Gospel of Thomas', 'url' => self::THOMAS, 'own' => false ),
			array( 'phrase' => 'Kaʿba', 'url' => self::KAABA, 'own' => false ),
		);
	}

	private function apply( $html, array $args = array() ) {
		return murtadd_ctx_apply( $html, $this->entries(), $args );
	}

	private function count_links( $html ) {
		return substr_count( $html, 'murtadd-ctx-link"' ) + substr_count( $html, 'murtadd-ctx-link murtadd-ctx-link--sister"' );
	}

	public function test_own_and_sister_links_carry_the_right_markup() {
		$out = $this->apply( '<p>On jizya.</p><p>On the Gospel of Thomas.</p>' );
		$this->assertStringContainsString( '<a class="murtadd-ctx-link" href="' . self::JIZYA . '">jizya</a>', $out );
		$this->assertStringContainsString( '<a class="murtadd-ctx-link murtadd-ctx-link--sister" href="' . self::THOMAS . '" rel="noopener">Gospel of Thomas</a>', $out );
		$this->assertStringNotContainsString( 'target=', $out, 'Contextual links open in the same tab.' );
	}

	public function test_headings_are_never_linked_and_the_text_mention_is() {
		$out = $this->apply( '<h2>Why jizya</h2><p>The jizya was a tax.</p>' );
		$this->assertStringContainsString( '<h2>Why jizya</h2>', $out );
		$this->assertSame( 1, $this->count_links( $out ) );
	}

	public function test_existing_links_quotations_and_arabic_are_left_alone() {
		$html = '<p><a href="/x/">jizya</a></p><blockquote><p>dhimmi</p></blockquote>'
			. '<p lang="ar" dir="rtl">Gospel of Thomas</p><p class="murtadd-ayah-tr">Kaʿba</p>'
			. '<figure><figcaption>Qur’an 4:34</figcaption></figure>';
		$this->assertSame( $html, $this->apply( $html ) );
	}

	/** Paulus's skip-stack closed a skipped div at the first inner </div>; a full element stack does not. */
	public function test_a_nested_element_does_not_end_a_skipped_region_early() {
		$out = $this->apply( '<div class="murtadd-verse"><div>a</div> jizya</div><p>jizya</p>' );
		$this->assertStringStartsWith( '<div class="murtadd-verse"><div>a</div> jizya</div>', $out );
		$this->assertStringEndsWith( '<p><a class="murtadd-ctx-link" href="' . self::JIZYA . '">jizya</a></p>', $out, 'Text after the region is linked again.' );
	}

	public function test_only_the_first_mention_of_an_address_is_linked() {
		$out = $this->apply( '<p>jizya.</p><p>jizya again, and dhimmi.</p>' );
		$this->assertSame( 1, $this->count_links( $out ), 'dhimmi shares the jizya address, so it is not linked a second time.' );
	}

	public function test_the_current_page_is_never_linked() {
		$out = $this->apply( '<p>jizya</p>', array( 'here' => 'https://murtadd.org/rebuttals/dhimmi-status-and-jizya/?utm=x' ) );
		$this->assertSame( 0, $this->count_links( $out ) );
	}

	public function test_one_link_per_paragraph_and_per_list_item() {
		$this->assertSame( 1, $this->count_links( $this->apply( '<p>jizya and the Gospel of Thomas.</p>' ) ) );
		$this->assertSame( 1, $this->count_links( $this->apply( '<ul><li><p>jizya</p><p>Gospel of Thomas</p></li></ul>' ) ) );
		$this->assertSame( 2, $this->count_links( $this->apply( '<ul><li>jizya</li><li>Gospel of Thomas</li></ul>' ) ) );
	}

	public function test_the_two_caps_and_switches_are_separate() {
		$html = "<p>jizya</p><p>Qur'an 4:34</p><p>Gospel of Thomas</p><p>Kaʿba</p>";
		$this->assertSame( 3, $this->count_links( $this->apply( $html, array( 'own_max' => 1 ) ) ) );
		$this->assertSame( 2, $this->count_links( $this->apply( $html, array( 'sister_max' => 0 ) ) ) );
		$this->assertSame( 2, $this->count_links( $this->apply( $html, array( 'own_enabled' => 0 ) ) ) );
		$this->assertSame( $html, $this->apply( $html, array( 'own_enabled' => 0, 'sister_enabled' => 0 ) ) );
	}

	public function test_diacritics_case_and_texturised_apostrophes_match() {
		$out = $this->apply( '<p>The KAʿBA stood.</p><p>Read Qur&#8217;an 4:34 first.</p>' );
		$this->assertStringContainsString( '>KAʿBA</a>', $out, 'Matched case-insensitively, original casing kept.' );
		$this->assertStringContainsString( '>Qur&#8217;an 4:34</a>', $out, 'the_content has texturised the apostrophe by now.' );
	}

	public function test_a_phrase_is_matched_whole() {
		$this->assertSame( 0, $this->count_links( $this->apply( '<p>jizyas and predhimmi</p>' ) ) );
	}

	public function test_admin_lines_win_and_foreign_hosts_are_refused() {
		$GLOBALS['murtadd_options']['murtadd_contextual_links'] = array(
			'extra' => "jizya | /rebuttals/other/\nGospel of Thomas | https://example.com/thomas/\ndhimmi | https://murtadd.org/rebuttals/x/",
		);
		$out = murtadd_ctx_apply( '<p>jizya</p><p>Gospel of Thomas</p><p>dhimmi</p>', murtadd_ctx_entries(), array() );
		$this->assertStringContainsString( 'href="https://murtadd.org/rebuttals/other/">jizya', $out );
		$this->assertStringNotContainsString( 'example.com', $out );
		$this->assertStringContainsString( 'href="' . self::THOMAS . '"', $out, 'A refused admin line leaves the shared entry in force.' );
		$this->assertStringContainsString( '<a class="murtadd-ctx-link" href="https://murtadd.org/rebuttals/x/">dhimmi', $out, 'This site\'s own host is an own-site link.' );
		$this->assertNull( murtadd_ctx_parse_line( 'no address' ) );
		$this->assertNull( murtadd_ctx_parse_line( 'x | //evil.example/' ) );
	}

	public function test_care_first_doubts_and_excluded_slugs_get_no_links() {
		$GLOBALS['murtadd_test_types'] = array( 1 => 'murtadd_doubt', 2 => 'murtadd_doubt', 3 => 'murtadd_doubt', 4 => 'murtadd_rebuttal' );
		$GLOBALS['murtadd_test_terms'] = array( 1 => array( 'emotional' ), 2 => array( 'identity' ), 3 => array( 'intellectual' ) );
		$GLOBALS['murtadd_test_slugs'] = array( 4 => 'the-age-of-aisha' );
		$this->assertFalse( murtadd_ctx_post_allowed( 1 ) );
		$this->assertFalse( murtadd_ctx_post_allowed( 2 ) );
		$this->assertTrue( murtadd_ctx_post_allowed( 3 ) );
		$this->assertTrue( murtadd_ctx_post_allowed( 4 ) );
		$GLOBALS['murtadd_options']['murtadd_contextual_links'] = array( 'excluded' => "the-age-of-aisha\n" );
		$this->assertFalse( murtadd_ctx_post_allowed( 4 ) );
	}

	/** The shared map's entries for this site must exist here, and must not lead into a care-first doubt. */
	public function test_every_own_site_map_path_exists_and_none_is_care_first() {
		$network = murtadd_ctx_network();
		$this->assertSame( 'https://murtadd.org', $network['sites']['murtadd'] ?? null );
		$this->assertCount( 7, $network['sites'] );

		$rebuttals = array_column( murtadd_seed_rebuttals(), 'slug' );
		$doubts    = array_column( murtadd_seed_doubts(), 'category', 'slug' );
		$own       = array_filter( $network['links'], static function ( $l ) {
			return 'murtadd' === $l['site'];
		} );
		$this->assertNotEmpty( $own );
		foreach ( $own as $link ) {
			$this->assertMatchesRegularExpression( '#^/(rebuttals|doubts)/([a-z0-9-]+)/$#', $link['path'] );
			preg_match( '#^/(rebuttals|doubts)/([a-z0-9-]+)/$#', $link['path'], $m );
			if ( 'rebuttals' === $m[1] ) {
				$this->assertContains( $m[2], $rebuttals, $link['path'] . ' is in the shared map but not seeded here.' );
			} else {
				$this->assertArrayHasKey( $m[2], $doubts, $link['path'] . ' is in the shared map but not seeded here.' );
				$this->assertNotContains( $doubts[ $m[2] ], array( 'emotional', 'identity' ), $link['path'] . ' is a care-first doubt; it stays unlinked.' );
			}
		}
	}

	public function test_each_phrase_has_one_owner_across_the_network() {
		$seen = array();
		foreach ( murtadd_ctx_network()['links'] as $link ) {
			foreach ( $link['phrases'] as $phrase ) {
				$key = murtadd_ctx_key( $phrase );
				$owner = $link['site'] . $link['path'];
				// Spelling variants (straight and curly apostrophe) may share an owner, never split one.
				$this->assertSame( $seen[ $key ] ?? $owner, $owner, sprintf( '"%s" is claimed by %s and %s.', $phrase, $seen[ $key ] ?? '', $owner ) );
				$seen[ $key ] = $owner;
			}
		}
	}

	/** End to end on real seed prose: the caps hold and an entry never links to itself. */
	public function test_seed_rebuttals_stay_within_the_caps() {
		$entries = murtadd_ctx_entries();
		foreach ( murtadd_seed_rebuttals() as $r ) {
			$here = 'https://murtadd.org/rebuttals/' . $r['slug'] . '/';
			$out  = murtadd_ctx_apply( $r['body'], $entries, array( 'here' => $here ) );
			$this->assertLessThanOrEqual( 4, substr_count( $out, '<a class="murtadd-ctx-link" ' ), $r['slug'] );
			$this->assertLessThanOrEqual( 3, substr_count( $out, 'murtadd-ctx-link--sister' ), $r['slug'] );
			$this->assertStringNotContainsString( 'href="' . $here . '"', $out, $r['slug'] . ' links to itself.' );
			$this->assertSame( wp_strip_all_tags( $r['body'] ), wp_strip_all_tags( $out ), $r['slug'] . ': linking changed the text.' );
		}
	}
}
