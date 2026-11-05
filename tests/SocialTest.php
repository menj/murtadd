<?php
/**
 * Social profile and sameAs invariants.
 *
 * The whole point of this system is that one saved URL does two jobs, a footer
 * icon and a schema sameAs, from one source. The tests pin that the registry is
 * complete and renderable, that every glyph produces valid single-viewBox SVG
 * (a malformed path would break silently in the footer), and that the profiles
 * helper and the sameAs set are the same data, so the two can never disagree.
 *
 * @package Murtadd\Tests
 */

// Run from the command line only: on a web server this file does nothing.
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

use PHPUnit\Framework\TestCase;

final class SocialTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['murtadd_options'] = array();
	}

	/** Every registry entry has a label, a kind, and glyph markup. */
	public function test_registry_entries_are_complete() {
		$reg = murtadd_social_registry();
		$this->assertGreaterThanOrEqual( 40, count( $reg ), 'The registry should carry the full icon set.' );

		foreach ( $reg as $slug => $meta ) {
			$this->assertMatchesRegularExpression( '/^[a-z0-9-]+$/', $slug );
			$this->assertArrayHasKey( 'label', $meta );
			$this->assertArrayHasKey( 'kind', $meta );
			$this->assertArrayHasKey( 'svg', $meta );
			$this->assertNotSame( '', trim( $meta['label'] ) );
			$this->assertContains( $meta['kind'], array( 'path', 'raw' ) );
			$this->assertNotSame( '', trim( $meta['svg'] ) );
		}
	}

	/** Every glyph renders to a single-viewBox SVG with a currentColor fill. */
	public function test_every_glyph_renders() {
		foreach ( array_keys( murtadd_social_registry() ) as $slug ) {
			$svg = murtadd_social_icon( $slug );
			$this->assertStringStartsWith( '<svg', $svg, "$slug did not render." );
			$this->assertStringContainsString( 'viewBox="0 0 24 24"', $svg, "$slug is not on the shared viewBox." );
			$this->assertStringContainsString( 'fill="currentColor"', $svg, "$slug does not inherit colour." );
			$this->assertSame( 1, substr_count( $svg, '<svg' ), "$slug produced nested or malformed SVG." );
			// No hardcoded colours should survive into the output.
			$this->assertStringNotContainsString( '#000', $svg, "$slug leaked a hardcoded fill." );
		}
	}

	/** An unknown slug renders nothing rather than a broken tag. */
	public function test_unknown_slug_renders_empty() {
		$this->assertSame( '', murtadd_social_icon( 'no-such-platform' ) );
	}

	/** The profiles helper returns only non-empty URLs, in registry order. */
	public function test_profiles_helper_filters_and_orders() {
		update_option(
			'murtadd_profiles',
			array(
				'youtube' => 'https://youtube.com/@author',
				'github'  => '',            // empty: must be dropped
				'x'       => 'https://x.com/author',
				'unknown' => 'https://nope', // not in registry: must be dropped
			)
		);

		$profiles = murtadd_social_profiles();
		$this->assertArrayHasKey( 'youtube', $profiles );
		$this->assertArrayHasKey( 'x', $profiles );
		$this->assertArrayNotHasKey( 'github', $profiles, 'Empty URLs must not appear.' );
		$this->assertArrayNotHasKey( 'unknown', $profiles, 'Slugs outside the registry must not appear.' );

		// Order follows the registry, not the order the URLs were entered.
		$keys      = array_keys( $profiles );
		$reg_order = array_keys( murtadd_social_registry() );
		$expected  = array_values( array_intersect( $reg_order, $keys ) );
		$this->assertSame( $expected, $keys, 'Profiles must come out in registry order, regardless of input order.' );
	}

	/**
	 * The footer icons and the schema sameAs are the same data, and sameAs
	 * always ends with https://menj.bio.
	 *
	 * This is the invariant the whole feature exists to guarantee: a link shown
	 * to a human and a link handed to a parser are one datum, never two.
	 */
	public function test_footer_and_sameas_are_the_same_set() {
		update_option(
			'murtadd_profiles',
			array(
				'youtube' => 'https://youtube.com/@author',
				'orcid'   => 'https://orcid.org/0000-0000-0000-0000',
			)
		);
		update_option( 'murtadd_author_name', 'Test Author' );

		$footer = array_values( murtadd_social_profiles() );
		$person = murtadd_author_node();

		$this->assertSame( array_merge( $footer, array( 'https://menj.bio' ) ), $person['sameAs'], 'The Person sameAs is the footer links, then https://menj.bio.' );
		$this->assertContains( 'https://menj.bio', murtadd_organization_node()['sameAs'], 'The Organization sameAs includes https://menj.bio.' );
		update_option( 'murtadd_profiles', array() );
		$this->assertSame( array( 'https://menj.bio' ), murtadd_author_node()['sameAs'], 'With no profiles the author still carries https://menj.bio.' );
		$this->assertArrayNotHasKey( 'sameAs', murtadd_organization_node(), 'With no profiles the Organization gets no sameAs.' );
	}

	/** The author Person does not depend on the Author name setting. */
	public function test_author_node_names_the_owner_without_a_name_setting() {
		update_option( 'murtadd_author_name', '' );
		$person = murtadd_author_node();
		$this->assertSame( 'Mohd Elfie Nieshaem Juferi', $person['name'] );
		$this->assertSame( 'https://menj.bio', $person['url'] );
		$this->assertSame( 'https://murtadd.org/#author', $person['@id'] );
	}

	/** The shortcode row: each link named for screen readers, glyphs hidden, nothing when no profile is saved. */
	public function test_shortcode_row_is_accessible() {
		update_option( 'murtadd_profiles', array() );
		$this->assertSame( '', murtadd_social_icons_shortcode() );

		update_option( 'murtadd_profiles', array( 'github' => 'https://github.com/example' ) );
		$html = murtadd_social_icons_shortcode();
		$this->assertStringContainsString( 'murtadd-social-row--inline', $html );
		$this->assertStringContainsString( '<span class="screen-reader-text">GitHub</span>', $html );
		$this->assertStringContainsString( 'aria-hidden="true"', $html );
		$this->assertNotNull( murtadd_test_hook_priority( 'shortcode:murtadd_social_icons', 'murtadd_social_icons_shortcode' ) );
	}

	/** Targets are at least 24px, focus is visible, and the colours are variables, minified twin included. */
	public function test_icon_styles_meet_the_target_size() {
		foreach ( array( 'social.css', 'social.min.css' ) as $file ) {
			$css = (string) file_get_contents( dirname( __DIR__ ) . '/assets/css/' . $file );
			$this->assertMatchesRegularExpression( '/min-width:\s*32px/', $css, $file );
			$this->assertStringContainsString( ':focus-visible', $css, $file );
			$this->assertStringContainsString( 'var(--murtadd-social-ink)', $css, $file );
		}
	}
}
