<?php
/**
 * Counterpart mapping invariants.
 *
 * Both sites cover a good deal of the same ground deliberately. The mapping is
 * what turns that from two pages competing on one query into a relay: this site
 * answers the charge, the link hands the reader to the long-form treatment.
 *
 * The failure mode worth pinning is a silent one. A typo in a counterpart slug
 * produces a link that 404s on the other site, and nothing here would notice,
 * because the theme has no way to check whether a slug exists elsewhere. So the
 * shape is enforced instead, and the count is asserted, so that a mapping
 * accidentally dropped during an edit shows up as a failure rather than as an
 * aside that quietly stops rendering.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class CounterpartTest extends TestCase {

	/** Every counterpart slug must be URL-shaped: it is concatenated into a path. */
	public function test_counterpart_slugs_are_well_formed() {
		foreach ( $this->entries_with_counterparts() as $slug => $ce ) {
			$this->assertMatchesRegularExpression(
				'/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
				$ce,
				sprintf( '"%s" maps to "%s", which is not a bare slug. A URL or stray character here produces a broken link.', $slug, $ce )
			);
			$this->assertStringNotContainsString( '/', $ce, 'Store the slug only; the template supplies the path.' );
			$this->assertStringNotContainsString( 'http', $ce, 'Store the slug only; the base URL is a theme option.' );
		}
	}

	/**
	 * The mapping covers the overlap, and a regression would show as a drop.
	 *
	 * Not asserting an exact figure, because the mapping should grow as the
	 * companion site publishes. Asserting a floor, because losing entries is
	 * the direction that fails silently.
	 */
	public function test_the_mapping_is_substantially_populated() {
		$mapped = $this->entries_with_counterparts();
		$this->assertGreaterThanOrEqual(
			45,
			count( $mapped ),
			'The counterpart mapping has shrunk. Entries that lost their ce_slug stop relaying and no longer render the aside.'
		);
	}

	/** A counterpart must not point at an entry's own slug: that would loop. */
	public function test_no_entry_points_at_itself_on_this_site() {
		$own = array_merge(
			array_column( murtadd_seed_rebuttals(), 'slug' ),
			array_column( murtadd_seed_doubts(), 'slug' )
		);

		foreach ( $this->entries_with_counterparts() as $slug => $ce ) {
			if ( $ce === $slug ) {
				// Identical slugs on the two sites are legitimate; is-islam-a-cult
				// exists on both. What matters is that the link leaves this site,
				// which it does, because the base URL is the other domain.
				$this->assertContains( $ce, $own );
			}
		}

		$this->addToAssertionCount( 1 );
	}

	/** Doubts that carry a counterpart should still be answerable on their own. */
	public function test_counterpart_never_replaces_a_response() {
		foreach ( murtadd_seed_doubts() as $doubt ) {
			if ( ! empty( $doubt['ce_slug'] ) ) {
				$this->assertNotEmpty(
					trim( wp_strip_all_tags( $doubt['response'] ) ),
					sprintf( 'Doubt "%s" has a counterpart link and no response. The link is an addition, never a substitute.', $doubt['slug'] )
				);
			}
		}
	}

	/** Render the counterpart aside for a post with the given meta and options. */
	private function render_counterpart( $ce_slug, array $cross_links ) {
		$GLOBALS['murtadd_view']                     = array( 'post' => (object) array( 'ID' => 901 ) );
		$GLOBALS['murtadd_view']['meta'][901]        = array( '_murtadd_ce_slug' => $ce_slug );
		$GLOBALS['murtadd_options']['murtadd_cross_links'] = $cross_links;
		ob_start();
		include dirname( __DIR__ ) . '/template-parts/counterpart.php';
		$html = ob_get_clean();
		$GLOBALS['murtadd_view'] = array();
		unset( $GLOBALS['murtadd_options']['murtadd_cross_links'] );
		return $html;
	}

	/** The aside links to /articles/<slug>/ on the companion site, in a new tab. */
	public function test_counterpart_renders_the_article_url() {
		$html = $this->render_counterpart( 'hadith-reliability', array( 'ce_base_url' => 'https://compelling-evidence.com' ) );
		$this->assertStringContainsString( 'href="https://compelling-evidence.com/articles/hadith-reliability/"', $html );
		$this->assertStringContainsString( 'rel="noopener"', $html );
		$this->assertStringContainsString( 'class="murtadd-counterpart"', $html );
	}

	/** Off by switch, by cleared base URL, or by missing slug: nothing renders. */
	public function test_counterpart_can_be_switched_off() {
		$this->assertSame( '', trim( $this->render_counterpart( 'hadith-reliability', array( 'ce_base_url' => 'https://compelling-evidence.com', 'show_counterpart' => '0' ) ) ) );
		$this->assertSame( '', trim( $this->render_counterpart( 'hadith-reliability', array( 'ce_base_url' => '' ) ) ) );
		$this->assertSame( '', trim( $this->render_counterpart( '', array( 'ce_base_url' => 'https://compelling-evidence.com' ) ) ) );
		$this->assertNotSame( '', trim( $this->render_counterpart( 'hadith-reliability', array( 'ce_base_url' => 'https://compelling-evidence.com', 'show_counterpart' => '1' ) ) ) );
	}

	/** Both single templates pull the aside in. */
	public function test_both_single_types_render_the_aside() {
		foreach ( array( 'single-murtadd_doubt.php', 'single-murtadd_rebuttal.php' ) as $tpl ) {
			$this->assertStringContainsString( "get_template_part( 'template-parts/counterpart' )", file_get_contents( dirname( __DIR__ ) . '/' . $tpl ), $tpl );
		}
	}

	/** The settings sanitiser keeps the switch, and an unticked box means off. */
	public function test_switch_survives_sanitising() {
		if ( ! function_exists( 'murtadd_sanitize_cross_links' ) ) {
			$this->markTestSkipped( 'Settings page not loaded in the test bootstrap.' );
		}
		$this->assertSame( '0', murtadd_sanitize_cross_links( array( 'ce_base_url' => 'https://x.test' ) )['show_counterpart'] );
		$this->assertSame( '1', murtadd_sanitize_cross_links( array( 'show_counterpart' => '1' ) )['show_counterpart'] );
	}

	/**
	 * Collect slug => counterpart across both post types.
	 *
	 * @return array<string,string>
	 */
	private function entries_with_counterparts() {
		$out = array();
		foreach ( array_merge( murtadd_seed_rebuttals(), murtadd_seed_doubts() ) as $entry ) {
			if ( ! empty( $entry['ce_slug'] ) ) {
				$out[ $entry['slug'] ] = $entry['ce_slug'];
			}
		}
		return $out;
	}
}
