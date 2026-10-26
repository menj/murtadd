<?php
/**
 * The login screen.
 *
 * It must look like the site, follow the Colours tab, never point at
 * WordPress.org, keep the owner's note plain text, and be styled through
 * WordPress's own login hooks, so it works whichever address serves the
 * screen, including the private login address.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class LoginTest extends TestCase {

	private $css;

	protected function setUp(): void {
		$GLOBALS['murtadd_options']         = array();
		$GLOBALS['murtadd_test_theme_mods'] = array();
		$GLOBALS['murtadd_test_site_icon']  = '';
		$_REQUEST                           = array();
		$this->css                          = file_get_contents( dirname( __DIR__ ) . '/assets/css/login.css' );
	}

	public function test_on_by_default_and_points_at_the_site() {
		$this->assertTrue( murtadd_login_design_on() );
		$this->assertSame( 'https://murtadd.org/', murtadd_login_header_url( 'https://wordpress.org/' ) );
		$this->assertSame( 'Murtadd', murtadd_login_header_text( 'Powered by WordPress' ) );
		$this->assertContains( 'murtadd-login', murtadd_login_body_class( array() ) );
		$this->assertSame( 'Log In ‹ Murtadd', murtadd_login_title( 'Log In ‹ Murtadd — WordPress', 'Log In' ) );
	}

	public function test_switched_off_it_is_wordpress_own_screen() {
		$GLOBALS['murtadd_options']['murtadd_security'] = array( 'login_design' => 0, 'login_message' => 'Hello' );
		$this->assertSame( 'https://wordpress.org/', murtadd_login_header_url( 'https://wordpress.org/' ) );
		$this->assertSame( 'Powered by WordPress', murtadd_login_header_text( 'Powered by WordPress' ) );
		$this->assertSame( array(), murtadd_login_body_class( array() ) );
		$this->assertSame( '', murtadd_login_message( '' ) );
	}

	public function test_mark_order_is_custom_logo_then_site_icon_then_theme_mark() {
		$this->assertSame( 'https://murtadd.org/wp-content/themes/murtadd/assets/icons/site-icon.svg', murtadd_login_logo_url() );
		$this->assertFileExists( dirname( __DIR__ ) . '/assets/icons/site-icon.svg' );
		$GLOBALS['murtadd_test_site_icon'] = 'https://murtadd.org/wp-content/uploads/icon-192.png';
		$this->assertSame( 'https://murtadd.org/wp-content/uploads/icon-192.png', murtadd_login_logo_url() );
		$GLOBALS['murtadd_test_theme_mods']['custom_logo'] = 12;
		$this->assertSame( 'https://murtadd.org/wp-content/uploads/logo-12.png', murtadd_login_logo_url() );
	}

	public function test_accents_follow_the_colours_tab_and_bad_values_are_dropped() {
		$GLOBALS['murtadd_options']['murtadd_colours'] = array( 'accent' => '#123456', 'accent_tint' => 'red;}body{display:none' );
		$css = murtadd_login_inline_css();
		$this->assertStringContainsString( '--murtadd-accent:#123456;', $css );
		$this->assertStringNotContainsString( 'display:none', $css );
		$this->assertStringContainsString( '--murtadd-login-logo:url("', $css );
	}

	public function test_note_is_plain_text_and_on_the_log_in_screen_only() {
		$GLOBALS['murtadd_options']['murtadd_security'] = array( 'login_message' => "Editors only.\n<b>Ask first</b>" );
		$out = murtadd_login_message( '' );
		$this->assertStringContainsString( 'class="message murtadd-login-message"', $out );
		$this->assertStringContainsString( '&lt;b&gt;Ask first&lt;/b&gt;', $out, 'The note is escaped, never rendered as markup.' );
		$this->assertStringContainsString( '<br', $out );

		$_REQUEST['action'] = 'lostpassword';
		$this->assertSame( '', murtadd_login_message( '' ) );
	}

	public function test_enqueued_only_on_the_login_screen() {
		$this->assertNotNull( murtadd_test_hook_priority( 'login_enqueue_scripts', 'murtadd_login_assets' ) );
		$this->assertNull( murtadd_test_hook_priority( 'wp_enqueue_scripts', 'murtadd_login_assets' ) );
		$this->assertFileExists( dirname( __DIR__ ) . '/assets/css/login.min.css', 'Every source asset has a .min twin.' );
	}

	public function test_stylesheet_is_scoped_and_reads_tokens() {
		$body = preg_replace( '#/\*.*?\*/#s', '', $this->css );
		preg_match_all( '/(?:^|})\s*([^{}@]+)\{/', $body, $m );
		foreach ( $m[1] as $selector_list ) {
			foreach ( explode( ',', $selector_list ) as $selector ) {
				$this->assertStringStartsWith( 'body.murtadd-login', trim( $selector ), 'Unscoped rule: ' . trim( $selector ) );
			}
		}
		$this->assertDoesNotMatchRegularExpression( '/#[0-9a-f]{3,6}\b/i', $body, 'Colours come from tokens.css; nothing hardcodes a hex.' );
	}

	public function test_builds_no_login_address_of_its_own() {
		$code = '';
		foreach ( token_get_all( file_get_contents( dirname( __DIR__ ) . '/inc/login.php' ) ) as $token ) {
			if ( ! is_array( $token ) || ! in_array( $token[0], array( T_COMMENT, T_DOC_COMMENT ), true ) ) {
				$code .= is_array( $token ) ? $token[1] : $token;
			}
		}
		$this->assertDoesNotMatchRegularExpression( "/['\"][^'\"]*wp-login\.php/", $code, 'A hard-coded wp-login.php link would break at the private login address.' );
	}

	public function test_brand_panel_escapes_the_line_and_falls_back_to_the_tagline() {
		$GLOBALS['murtadd_options']['murtadd_security'] = array( 'login_tagline' => '<script>x</script> Plain' );
		$out = murtadd_login_panel_html();
		$this->assertStringContainsString( 'class="murtadd-login-panel"', $out );
		$this->assertStringContainsString( '&lt;script&gt;', $out );
		$this->assertStringNotContainsString( '<script>', $out );
		$this->assertStringNotContainsString( '<a ', $out, 'The link home stays on core h1; the panel adds no second one.' );
		$GLOBALS['murtadd_options']['murtadd_security'] = array();
		$this->assertSame( 'Murtadd', murtadd_login_tagline() );
		$this->assertNotNull( murtadd_test_hook_priority( 'login_header', 'murtadd_login_panel' ) );
		$GLOBALS['murtadd_options']['murtadd_security'] = array( 'login_design' => 0 );
		$this->assertSame( '', murtadd_login_panel_html() );
	}

	public function test_privacy_note_on_every_form_and_switchable() {
		$this->assertStringContainsString( 'class="murtadd-login-privacy"', murtadd_login_privacy_html() );
		foreach ( array( 'login_form', 'lostpassword_form', 'register_form' ) as $hook ) {
			$this->assertNotNull( murtadd_test_hook_priority( $hook, 'murtadd_login_privacy' ), $hook );
		}
		$GLOBALS['murtadd_options']['murtadd_security'] = array( 'login_privacy' => 0 );
		$this->assertSame( '', murtadd_login_privacy_html() );
	}

	public function test_stylesheet_has_pills_focus_rings_and_stays_light() {
		$this->assertStringContainsString( 'border-radius: 999px', $this->css );
		$this->assertStringContainsString( ':focus-visible', $this->css );
		$this->assertStringContainsString( 'color-scheme: light', $this->css );
		$this->assertStringNotContainsString( 'prefers-color-scheme', $this->css, 'Light only (docs/ssot.md, Exclusions).' );
		$min = file_get_contents( dirname( __DIR__ ) . '/assets/css/login.min.css' );
		foreach ( array( 'murtadd-login-panel', 'murtadd-login-privacy', '#login_error' ) as $needle ) {
			$this->assertStringContainsString( $needle, $min, 'The .min twin is current.' );
		}
	}
}
