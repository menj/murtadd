<?php
/**
 * Page copy and the retirement of the placeholders.
 *
 * The defect: for 39 releases activation published /irtidad/, /about/,
 * /faq/, /contact/ and /privacy/ reading "Placeholder. Replace this text.",
 * and seeded two lorem-ipsum letters, all public the moment the theme was
 * switched on. /irtidad/ is the second route from Start Here and the target
 * of the legacy redirects, so the frightened reader the site is built for was
 * sent to a placeholder.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

if ( ! function_exists( 'wp_update_post' ) ) {
	function wp_update_post( $args ) {
		if ( ! empty( $GLOBALS['murtadd_fail_update'] ) ) {
			return 0;
		}
		foreach ( $GLOBALS['murtadd_posts'] as $type => $posts ) {
			foreach ( $posts as $slug => $post ) {
				if ( $post->ID === $args['ID'] ) {
					foreach ( $args as $k => $v ) {
						$GLOBALS['murtadd_posts'][ $type ][ $slug ]->$k = $v;
					}
					return $args['ID'];
				}
			}
		}
		return 0;
	}
}

require_once __DIR__ . '/../inc/activation.php';

final class PageCopyTest extends TestCase {

	protected function setUp(): void {
		murtadd_test_reset_posts();
		$GLOBALS['murtadd_fail_update'] = false;
	}

	/** Insert a post as an earlier release would have left it. */
	private function legacy_post( $type, $slug, $content, $status = 'publish' ) {
		wp_insert_post(
			array(
				'post_type'    => $type,
				'post_name'    => $slug,
				'post_title'   => $slug,
				'post_content' => $content,
				'post_status'  => $status,
			)
		);
		return get_page_by_path( $slug, OBJECT, $type );
	}

	/** The old lorem bodies, rebuilt from their opening and closing words. */
	private function lorem_one() {
		return '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p>
<p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</p>';
	}

	/** Nothing the theme seeds may say it is a placeholder or be lorem ipsum. */
	public function test_no_seeded_text_is_placeholder() {
		$texts = array();
		foreach ( murtadd_required_pages() as $slug => $page ) {
			$texts[ "page $slug" ] = $page['body'];
		}
		foreach ( murtadd_seed_rebuttals() as $r ) {
			$texts[ 'rebuttal ' . $r['slug'] ] = $r['title'] . ' ' . $r['body'];
		}
		foreach ( murtadd_seed_doubts() as $d ) {
			$texts[ 'doubt ' . $d['slug'] ] = $d['title'] . ' ' . $d['response'];
		}
		foreach ( $texts as $where => $text ) {
			$this->assertDoesNotMatchRegularExpression( '/placeholder|lorem ipsum|replace this text/i', $text, "$where carries placeholder text." );
		}
	}

	/** Every page that renders its content has copy of substance. */
	public function test_rendered_pages_have_real_copy() {
		$pages = murtadd_required_pages();
		foreach ( array( 'irtidad', 'about', 'faq', 'contact', 'privacy' ) as $slug ) {
			$words = str_word_count( murtadd_normalise_copy( $pages[ $slug ]['body'] ) );
			$this->assertGreaterThan( 'irtidad' === $slug ? 500 : 80, $words, "/$slug/ is too thin to publish." );
		}
	}

	/** House style: no em dashes, no contractions, no "actually", in the new copy. */
	public function test_page_copy_keeps_house_style() {
		foreach ( murtadd_seed_page_bodies() as $slug => $html ) {
			$text = murtadd_normalise_copy( preg_replace( '/<!--.*?-->/s', '', $html ) );
			$this->assertStringNotContainsString( '—', $text, "/$slug/ has an em dash." );
			$this->assertDoesNotMatchRegularExpression( '/\bactually\b/i', $text, "/$slug/ uses \"actually\"." );
			$this->assertDoesNotMatchRegularExpression( "/\\b\\w+(n't|'re|'ve|'ll|'m)\\b/i", $text, "/$slug/ uses a contraction." );
		}
	}

	/** Every internal link in the page copy leads to something the seed creates. */
	public function test_page_links_resolve_to_seeded_content() {
		$exists = array( '/doubts/', '/rebuttals/', '/fatwa/' );
		foreach ( murtadd_seed_rebuttals() as $r ) {
			$exists[] = '/rebuttals/' . $r['slug'] . '/';
		}
		foreach ( murtadd_seed_doubts() as $d ) {
			$exists[] = '/doubts/' . $d['slug'] . '/';
		}
		foreach ( array_keys( murtadd_starter_topics() ) as $t ) {
			$exists[] = '/topic/' . $t . '/';
		}
		foreach ( array_keys( murtadd_required_pages() ) as $p ) {
			$exists[] = '/' . $p . '/';
		}

		$checked = 0;
		foreach ( murtadd_seed_page_bodies() as $slug => $html ) {
			preg_match_all( '/href="([^"]+)"/', $html, $m );
			foreach ( $m[1] as $href ) {
				$path = (string) parse_url( $href, PHP_URL_PATH );
				$this->assertStringStartsWith( 'https://murtadd.org/', $href, "/$slug/ links off the site: $href" );
				$this->assertContains( $path, $exists, "/$slug/ links to $path, which the seed does not create." );
				++$checked;
			}
		}
		$this->assertGreaterThan( 10, $checked );
	}

	/** Contact and Privacy tell the owner what only the owner can supply. */
	public function test_owner_notes_are_comments() {
		$bodies = murtadd_seed_page_bodies();
		foreach ( array( 'contact', 'privacy' ) as $slug ) {
			$this->assertMatchesRegularExpression( '/<!-- Site owner:.+?-->/s', $bodies[ $slug ], "/$slug/ has no note for the owner." );
			$this->assertDoesNotMatchRegularExpression( '/@|mailto:/', $bodies[ $slug ], "/$slug/ invents an address." );
		}
	}

	/** A fresh install creates the pages with the written copy. */
	public function test_fresh_install_gets_written_copy() {
		murtadd_create_missing_pages();
		$bodies = murtadd_seed_page_bodies();
		$this->assertSame( $bodies['irtidad'], get_page_by_path( 'irtidad' )->post_content );
		$this->assertSame( $bodies['privacy'], get_page_by_path( 'privacy' )->post_content );
	}

	/** An unedited placeholder page is replaced; an edited one is not. */
	public function test_retirement_replaces_only_unedited_pages() {
		$this->legacy_post( 'page', 'about', 'Placeholder. Replace this text.' );
		// Opened in the block editor and saved unchanged: still unedited.
		$this->legacy_post( 'page', 'faq', "<!-- wp:paragraph -->\n<p>Placeholder. Replace this text.</p>\n<!-- /wp:paragraph -->" );
		$this->legacy_post( 'page', 'irtidad', 'Placeholder. This page carries the second route from Start Here: what irtidad actually is, and what does not take a person out of Islam. Replace this text.' );
		$this->legacy_post( 'page', 'contact', 'Placeholder. Replace this text. Write to us at the office.' );
		$this->legacy_post( 'page', 'privacy', '<p>Our own privacy policy.</p>' );

		$done   = murtadd_retire_placeholder_copy();
		$bodies = murtadd_seed_page_bodies();

		$this->assertSame( array( 'irtidad', 'about', 'faq' ), $done['pages'] );
		$this->assertSame( $bodies['about'], get_page_by_path( 'about' )->post_content );
		$this->assertSame( $bodies['faq'], get_page_by_path( 'faq' )->post_content );
		$this->assertSame( $bodies['irtidad'], get_page_by_path( 'irtidad' )->post_content );
		$this->assertSame( 'Placeholder. Replace this text. Write to us at the office.', get_page_by_path( 'contact' )->post_content, 'An edited page was overwritten.' );
		$this->assertSame( '<p>Our own privacy policy.</p>', get_page_by_path( 'privacy' )->post_content, 'An owner-written page was overwritten.' );
	}

	/** The lorem letters go to draft; a letter with real text stays published. */
	public function test_retirement_drafts_only_unedited_letters() {
		$this->legacy_post( 'murtadd_letter', 'placeholder-letter-one', $this->lorem_one() );
		$this->legacy_post( 'murtadd_letter', 'placeholder-letter-two', '<p>The real letter, filed over the placeholder.</p>' );

		$done = murtadd_retire_placeholder_copy();

		$this->assertSame( array( 'placeholder-letter-one' ), $done['letters'] );
		$this->assertSame( 'draft', get_page_by_path( 'placeholder-letter-one', OBJECT, 'murtadd_letter' )->post_status );
		$this->assertSame( 'publish', get_page_by_path( 'placeholder-letter-two', OBJECT, 'murtadd_letter' )->post_status, 'A letter with real text was unpublished.' );
	}

	/** It runs once, and a second pass with the flag cleared changes nothing more. */
	public function test_retirement_is_idempotent() {
		$this->legacy_post( 'page', 'about', 'Placeholder. Replace this text.' );
		$this->legacy_post( 'murtadd_letter', 'placeholder-letter-one', $this->lorem_one() );

		$first = murtadd_retire_placeholder_copy();
		$this->assertSame( array( 'about' ), $first['pages'] );

		$second = murtadd_retire_placeholder_copy();
		$this->assertSame( array(), $second['pages'] );
		$this->assertSame( array(), $second['letters'] );

		update_option( 'murtadd_placeholder_copy_retired', 0 );
		$third = murtadd_retire_placeholder_copy();
		$this->assertSame( array(), $third['pages'], 'Written copy was treated as a placeholder.' );
		$this->assertSame( array(), $third['letters'], 'A drafted letter was touched again.' );
	}

	/** A site with none of the pages is not an error. */
	public function test_retirement_on_empty_site_does_nothing() {
		$done = murtadd_retire_placeholder_copy();
		$this->assertSame( array( 'pages' => array(), 'letters' => array() ), $done );
	}

	/** A failed write leaves the job unfinished, so the next admin request retries it. */
	public function test_failed_write_is_retried() {
		$this->legacy_post( 'page', 'about', 'Placeholder. Replace this text.' );

		$GLOBALS['murtadd_fail_update'] = true;
		$first = murtadd_retire_placeholder_copy();
		$this->assertSame( array(), $first['pages'], 'A failed write was reported as done.' );
		$this->assertEmpty( get_option( 'murtadd_placeholder_copy_retired' ), 'The flag was set although a write failed.' );

		$GLOBALS['murtadd_fail_update'] = false;
		$second = murtadd_retire_placeholder_copy();
		$this->assertSame( array( 'about' ), $second['pages'] );
		$this->assertNotEmpty( get_option( 'murtadd_placeholder_copy_retired' ) );
	}

	/** Markup the theme never seeded (a link, an image) means the page was edited. */
	public function test_added_markup_counts_as_an_edit() {
		$this->legacy_post( 'page', 'about', 'Placeholder. <a href="https://example.org/">Replace this text.</a>' );
		$this->legacy_post( 'page', 'faq', '<p>Placeholder. Replace this text.</p><img src="x.png" alt="">' );

		$done = murtadd_retire_placeholder_copy();

		$this->assertSame( array(), $done['pages'], 'A page with added markup was overwritten.' );
	}
}
