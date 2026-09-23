<?php
/**
 * Legacy redirect invariants.
 *
 * The map exists to stop nine recovered URLs from returning 404s. Two ways it
 * could silently fail: a target descriptor pointing at a page or term this
 * theme never creates, which would leave the 404 in place while looking
 * correct in review; and a stale slug in the map, which would map nothing.
 * Both are pinned here against the theme's own scaffolding and taxonomy.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class RedirectTest extends TestCase {

	/** The slugs recovered from the old site's sitemap caches. */
	private const RECOVERED = array(
		'definition',
		'related-definitions',
		'irtidad-ridda',
		'who-murtadd',
		'conclusion',
		'yasir-qadhi-on-murtadd',
		'bilal-philips-on-murtadd',
		'zakir-naik-on-murtadd',
		'sheikh-assim-al-hakeem-on-murtadd',
		'sitemap',
		'privacy-policy',
	);

	/** Every recovered URL must be covered, or it stays a 404. */
	public function test_every_recovered_url_is_mapped() {
		$map = murtadd_legacy_redirect_map();

		foreach ( self::RECOVERED as $slug ) {
			$this->assertArrayHasKey( $slug, $map, sprintf( '/%s/ was live on the old site and is unmapped.', $slug ) );
		}
	}

	/** Descriptors must be complete and use a known kind. */
	public function test_descriptors_are_well_formed() {
		foreach ( murtadd_legacy_redirect_map() as $from => $target ) {
			$this->assertMatchesRegularExpression( '/^[a-z0-9-]+$/', $from, 'Keys are matched against sanitize_title() output.' );
			$this->assertArrayHasKey( 'kind', $target );
			$this->assertArrayHasKey( 'slug', $target );
			$this->assertArrayHasKey( 'note', $target, 'Each entry records why it maps where it does.' );
			$this->assertContains( $target['kind'], array( 'page', 'topic', 'home' ) );
			$this->assertNotSame( '', trim( $target['note'] ) );
		}
	}

	/**
	 * Page targets must be pages the theme actually scaffolds.
	 *
	 * Setup creates a fixed set; a descriptor naming anything else resolves to
	 * an empty URL at runtime and the reader keeps their 404.
	 */
	public function test_page_targets_are_scaffolded_by_setup() {
		$scaffolded = array( 'home', 'blog', 'start-here', 'irtidad', 'about', 'faq', 'contact', 'privacy' );

		foreach ( murtadd_legacy_redirect_map() as $from => $target ) {
			if ( 'page' === $target['kind'] ) {
				$this->assertContains(
					$target['slug'],
					$scaffolded,
					sprintf( '/%s/ points at page "%s", which setup does not create.', $from, $target['slug'] )
				);
			}
		}
	}

	/** Topic targets must exist in the starter taxonomy. */
	public function test_topic_targets_exist_in_the_starter_taxonomy() {
		$topics = array_keys( murtadd_starter_topics() );

		foreach ( murtadd_legacy_redirect_map() as $from => $target ) {
			if ( 'topic' === $target['kind'] ) {
				$this->assertContains(
					$target['slug'],
					$topics,
					sprintf( '/%s/ points at topic "%s", which is not a starter term.', $from, $target['slug'] )
				);
			}
		}
	}

	/**
	 * The scholar pages land on the apostasy-law topic.
	 *
	 * They carried named individuals' rulings. The topic page is the one place
	 * on this site that treats the subject as legal history with the modern
	 * debate stated as open, which is the honest destination for that traffic.
	 */
	public function test_scholar_pages_land_on_the_apostasy_law_topic() {
		$map = murtadd_legacy_redirect_map();

		foreach ( array( 'yasir-qadhi-on-murtadd', 'bilal-philips-on-murtadd', 'zakir-naik-on-murtadd', 'sheikh-assim-al-hakeem-on-murtadd' ) as $slug ) {
			$this->assertSame( 'topic', $map[ $slug ]['kind'] );
			$this->assertSame( 'apostasy-law', $map[ $slug ]['slug'] );
		}
	}

	/** Nothing maps to a path the current site would itself 404 on. */
	public function test_no_entry_maps_onto_another_legacy_slug() {
		$map = murtadd_legacy_redirect_map();

		foreach ( $map as $from => $target ) {
			$this->assertArrayNotHasKey(
				$target['slug'],
				$map,
				sprintf( '/%s/ redirects to /%s/, which is itself a redirect.', $from, $target['slug'] )
			);
		}
	}
}
