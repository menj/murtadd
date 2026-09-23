<?php
/**
 * Documentation viewer invariants.
 *
 * The viewer reads files off disk and prints them into an admin page, so the
 * two things worth pinning are that the manifest cannot point outside the docs
 * directory, and that every entry in it actually resolves in the shipped
 * build. A manifest listing a file that was never packaged produces a tab of
 * error notices, which is the failure most likely to survive code review.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class DocsTest extends TestCase {

	/** Every manifest entry must resolve to a readable file in this build. */
	public function test_every_manifest_document_ships() {
		foreach ( murtadd_docs_manifest() as $slug => $doc ) {
			$path = murtadd_docs_path( $slug );
			$this->assertNotSame( '', $path, sprintf( 'Manifest lists %s but the file does not resolve.', $doc['file'] ) );
			$this->assertFileIsReadable( $path );
		}
	}

	/** Each entry needs a file, a label, and a blurb — the nav renders all three. */
	public function test_manifest_entries_are_complete() {
		foreach ( murtadd_docs_manifest() as $slug => $doc ) {
			$this->assertMatchesRegularExpression( '/^[a-z0-9-]+$/', $slug, 'Slugs travel in a URL and through sanitize_key().' );
			$this->assertArrayHasKey( 'file', $doc );
			$this->assertArrayHasKey( 'label', $doc );
			$this->assertArrayHasKey( 'blurb', $doc );
			$this->assertNotSame( '', trim( $doc['label'] ) );
			$this->assertNotSame( '', trim( $doc['blurb'] ) );
		}
	}

	/** An unknown slug resolves to nothing rather than to a file. */
	public function test_unknown_slug_resolves_to_nothing() {
		$this->assertSame( '', murtadd_docs_path( 'no-such-document' ) );
		$this->assertSame( '', murtadd_docs_render( 'no-such-document' ) );
	}

	/**
	 * Traversal attempts resolve to nothing.
	 *
	 * The slug is whitelisted against the manifest before the path is built, so
	 * these cannot reach the filesystem — this test is the regression net for
	 * anyone who later "simplifies" murtadd_docs_path() into concatenation.
	 */
	public function test_traversal_attempts_are_refused() {
		foreach ( array( '../style', '../../wp-config', 'docs/../../functions', './readme' ) as $slug ) {
			$this->assertSame( '', murtadd_docs_path( $slug ) );
		}
	}

	/** Markdown renders to HTML, and script content does not survive. */
	public function test_rendering_produces_sanitised_html() {
		$html = murtadd_docs_render( 'readme' );

		$this->assertStringContainsString( '<h1', $html, 'The overview should render its heading.' );
		$this->assertStringNotContainsString( '<script', $html );
		$this->assertStringNotContainsString( 'javascript:', $html );
		$this->assertStringNotContainsString( 'onerror=', $html );
	}

	/** The changelog must render, since it is the longest and most table-heavy doc. */
	public function test_changelog_renders() {
		$html = murtadd_docs_render( 'changelog' );
		$this->assertNotSame( '', $html );
		$this->assertStringContainsString( MURTADD_VERSION, wp_strip_all_tags( $html ), 'The current version should appear in the changelog.' );
	}
}
