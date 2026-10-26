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
	private function legacy_post( $type, $slug, $content, $status = 'publish', $extra = array() ) {
		wp_insert_post(
			array_merge(
				array(
					'post_type'    => $type,
					'post_name'    => $slug,
					'post_title'   => $slug,
					'post_content' => $content,
					'post_status'  => $status,
				),
				$extra
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

	/**
	 * Contact and Privacy publish without an operator: no placeholder note,
	 * no name to fill in, no invented address. The form replaced the
	 * "add an address" note in 1.43.0, and Privacy is written from the code.
	 */
	public function test_contact_and_privacy_need_no_operator() {
		$bodies = murtadd_seed_page_bodies();
		foreach ( array( 'contact', 'privacy' ) as $slug ) {
			$this->assertDoesNotMatchRegularExpression( '/Site owner|<!--/i', $bodies[ $slug ], "/$slug/ still carries a note for the owner." );
			$this->assertDoesNotMatchRegularExpression( '/@|mailto:/', $bodies[ $slug ], "/$slug/ invents an address." );
		}
		// Privacy covers what the theme does: the form, cookies, comments, fonts, analytics, Rank Math.
		foreach ( array( 'contact form', 'cookies', 'comments', 'typefaces', 'analytics', 'Rank Math', 'one-way code' ) as $topic ) {
			$this->assertStringContainsStringIgnoringCase( $topic, murtadd_normalise_copy( $bodies['privacy'] ), "/privacy/ says nothing about $topic." );
		}
	}

	/** The About page is expanded, within bounds, and claims nothing about people. */
	public function test_about_is_expanded_modestly() {
		$words = str_word_count( murtadd_normalise_copy( murtadd_seed_page_bodies()['about'] ) );
		$this->assertGreaterThanOrEqual( 500, $words );
		$this->assertLessThanOrEqual( 720, $words );
		$this->assertDoesNotMatchRegularExpression( '/\b(founded by|our team|qualified|degree|PhD|Dr\.|Shaykh|Ustaz)\b/i', murtadd_seed_page_bodies()['about'], '/about/ makes a claim about people or credentials.' );
	}

	/** The Contact and Privacy copy 1.41.0 to 1.43.0 seeded, as it was stored. */
	private function written_141( $slug ) {
		$old = json_decode( (string) file_get_contents( __DIR__ . '/fixtures/pages-1.41.json' ), true );
		return $old[ $slug ];
	}

	/** An existing site still holding the 1.41.0 Contact and Privacy copy gets the new copy. */
	public function test_retirement_replaces_unedited_141_contact_and_privacy() {
		$this->legacy_post( 'page', 'contact', $this->written_141( 'contact' ) );
		// Converted to blocks and saved: still unedited.
		$this->legacy_post( 'page', 'privacy', "<!-- wp:html -->\n" . str_replace( '</p>', "</p>\n\n", $this->written_141( 'privacy' ) ) . "\n<!-- /wp:html -->" );
		update_option( 'murtadd_placeholder_copy_retired', 1 ); // A site that ran the 1.41.0 pass.

		$done   = murtadd_retire_placeholder_copy();
		$bodies = murtadd_seed_page_bodies();

		$this->assertSame( array( 'contact', 'privacy' ), $done['pages'] );
		$this->assertSame( $bodies['contact'], get_page_by_path( 'contact' )->post_content );
		$this->assertSame( $bodies['privacy'], get_page_by_path( 'privacy' )->post_content );
		$this->assertSame( murtadd_placeholder_retire_version(), get_option( 'murtadd_placeholder_copy_retired' ) );
	}

	/** Any edit to that copy, even only removing the owner's note, keeps the page. */
	public function test_retirement_keeps_edited_141_copy() {
		$contact = str_replace( 'find a helpline near you.', 'call us on 0123.', $this->written_141( 'contact' ) );
		$privacy = (string) preg_replace( '/<!--.*?-->\n?/s', '', $this->written_141( 'privacy' ) );
		$this->legacy_post( 'page', 'contact', $contact );
		$this->legacy_post( 'page', 'privacy', $privacy );
		update_option( 'murtadd_placeholder_copy_retired', 1 );

		$done = murtadd_retire_placeholder_copy();

		$this->assertSame( array(), $done['pages'] );
		$this->assertSame( $contact, get_page_by_path( 'contact' )->post_content, 'An edited Contact page was overwritten.' );
		$this->assertSame( $privacy, get_page_by_path( 'privacy' )->post_content, 'An edited Privacy page was overwritten.' );
	}

	/** The new pass runs once on a site already marked done by 1.41.0, then stops. */
	public function test_141_pass_is_idempotent() {
		$this->legacy_post( 'page', 'contact', $this->written_141( 'contact' ) );
		update_option( 'murtadd_placeholder_copy_retired', 1 );
		$this->assertSame( array( 'contact' ), murtadd_retire_placeholder_copy()['pages'] );

		$this->assertSame( array(), murtadd_retire_placeholder_copy()['pages'] );
		update_option( 'murtadd_placeholder_copy_retired', 0 );
		$this->assertSame( array(), murtadd_retire_placeholder_copy()['pages'], 'The current copy was treated as old.' );
	}

	/** The recorded hashes are of the copy as it shipped. */
	public function test_141_hashes_match_the_shipped_copy() {
		$written = murtadd_legacy_placeholder_copy()['written'];
		foreach ( array( 'contact', 'privacy' ) as $slug ) {
			$this->assertContains( md5( murtadd_normalise_markup( $this->written_141( $slug ) ) ), $written[ $slug ], "$slug hash does not match the 1.41.0 copy." );
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

	/** Malay installed on the site, with the placeholder translated. */
	private function install_malay() {
		$GLOBALS['murtadd_l10n']['ms_MY'] = array(
			'Placeholder. Replace this text.' => 'Pemegang tempat. Gantikan teks ini.',
			'Placeholder. This page carries the second route from Start Here: what irtidad actually is, and what does not take a person out of Islam. Replace this text.' => 'Pemegang tempat. Halaman ini membawa laluan kedua dari Mula Di Sini: apa sebenarnya irtidad, dan apa yang tidak mengeluarkan seseorang daripada Islam. Gantikan teks ini.',
		);
	}

	protected function tearDown(): void {
		unset( $GLOBALS['murtadd_l10n'], $GLOBALS['murtadd_locale'], $GLOBALS['murtadd_locale_stack'] );
		parent::tearDown();
	}

	/** A placeholder stored under an installed locale other than the current one is retired. */
	public function test_placeholder_in_installed_locale_is_retired() {
		$this->install_malay();
		$this->legacy_post( 'page', 'about', 'Pemegang tempat. Gantikan teks ini.' );
		$this->legacy_post( 'page', 'irtidad', '<!-- wp:paragraph --><p>Pemegang tempat. Halaman ini membawa laluan kedua dari Mula Di Sini: apa sebenarnya irtidad, dan apa yang tidak mengeluarkan seseorang daripada Islam. Gantikan teks ini.</p><!-- /wp:paragraph -->' );

		$done   = murtadd_retire_placeholder_copy();
		$bodies = murtadd_seed_page_bodies();

		$this->assertSame( array( 'irtidad', 'about' ), $done['pages'] );
		$this->assertSame( $bodies['about'], get_page_by_path( 'about' )->post_content );
		$this->assertSame( 'en_US', $GLOBALS['murtadd_locale'] ?? 'en_US', 'The locale was not restored.' );
	}

	/** Text that is no translation of the placeholder is kept, however short. */
	public function test_short_page_that_is_no_translation_is_kept() {
		$this->install_malay();
		$this->legacy_post( 'page', 'contact', '<p>Write to us by post.</p>' );
		$this->legacy_post( 'page', 'privacy', 'Pemegang tempat lain.' );
		$done = murtadd_retire_placeholder_copy();
		$this->assertSame( array(), $done['pages'] );
		$this->assertSame( '<p>Write to us by post.</p>', get_page_by_path( 'contact' )->post_content );
	}

	/** A translated placeholder is kept when that language is no longer installed. */
	public function test_translation_of_uninstalled_locale_is_kept() {
		$this->legacy_post( 'page', 'about', 'Pemegang tempat. Gantikan teks ini.' );
		$done = murtadd_retire_placeholder_copy();
		$this->assertSame( array(), $done['pages'] );
	}

	/** A translated placeholder with a link added is an edit, and is kept. */
	public function test_translated_placeholder_with_markup_is_kept() {
		$this->install_malay();
		$this->legacy_post( 'page', 'faq', 'Pemegang tempat. <a href="https://example.org/">Gantikan teks ini.</a>' );
		$done = murtadd_retire_placeholder_copy();
		$this->assertSame( array(), $done['pages'] );
	}

	/** English and translated placeholders retire together, and a second pass changes nothing. */
	public function test_locale_path_is_idempotent() {
		$this->install_malay();
		$this->legacy_post( 'page', 'about', 'Placeholder. Replace this text.' );
		$this->legacy_post( 'page', 'faq', 'Pemegang tempat. Gantikan teks ini.' );

		$first = murtadd_retire_placeholder_copy();
		$this->assertSame( array( 'about', 'faq' ), $first['pages'] );

		update_option( 'murtadd_placeholder_copy_retired', 0 );
		$second = murtadd_retire_placeholder_copy();
		$this->assertSame( array(), $second['pages'], 'Written copy was rewritten on a second pass.' );
	}
}
