<?php
/**
 * Security hardening and the private login address.
 *
 * The site at this domain was compromised in 2024. The switches here are the
 * response, so each one is pinned: it closes what it says, it stays out of the
 * way when switched off, and the one that can lock an owner out (the private
 * login address) is off until an address is deliberately saved and refuses
 * an address that would collide with anything.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class HardeningTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['murtadd_options']         = array();
		$GLOBALS['murtadd_test_caps']       = array();
		$GLOBALS['murtadd_settings_errors'] = array();
		$GLOBALS['murtadd_posts']           = array();
	}

	private function routes() {
		return array(
			'/wp/v2/posts'               => array(),
			'/wp/v2/users'               => array(),
			'/wp/v2/users/(?P<id>[\d]+)' => array(),
			'/wp/v2/users/me'            => array(),
		);
	}

	public function test_defaults_harden_everything_except_the_login_address() {
		$this->assertSame( 1, murtadd_security( 'hide_authors' ) );
		$this->assertSame( 1, murtadd_security( 'xmlrpc_off' ) );
		$this->assertSame( 1, murtadd_security( 'headers' ) );
		$this->assertSame( '', murtadd_security( 'login_slug' ), 'The private login address must be off until an owner sets it.' );
		$this->assertSame( '', murtadd_login_slug() );
	}

	public function test_rest_user_routes_are_removed_for_visitors() {
		$routes = murtadd_hardening_rest_endpoints( $this->routes() );
		$this->assertSame( array( '/wp/v2/posts' ), array_keys( $routes ) );
	}

	public function test_rest_user_routes_stay_for_those_who_may_list_users() {
		$GLOBALS['murtadd_test_caps'] = array( 'list_users' );
		$this->assertSame( array_keys( $this->routes() ), array_keys( murtadd_hardening_rest_endpoints( $this->routes() ) ) );
	}

	public function test_rest_user_routes_stay_when_switched_off() {
		$GLOBALS['murtadd_options']['murtadd_security'] = array( 'hide_authors' => 0 );
		$this->assertCount( 4, murtadd_hardening_rest_endpoints( $this->routes() ) );
	}

	public function test_users_sitemap_is_dropped_and_others_kept() {
		$this->assertFalse( murtadd_hardening_sitemap( 'provider', 'users' ) );
		$this->assertSame( 'provider', murtadd_hardening_sitemap( 'provider', 'posts' ) );
	}

	public function test_public_author_is_never_the_account_name() {
		$this->assertSame( 'Murtadd', murtadd_hardening_author_name( 'admin_login' ), 'Without an author name, the site is named.' );
		$GLOBALS['murtadd_options']['murtadd_author_name'] = 'The Editor';
		$this->assertSame( 'The Editor', murtadd_hardening_author_name( 'admin_login' ) );
		$embed = murtadd_hardening_oembed( array( 'author_name' => 'admin_login', 'author_url' => 'https://murtadd.org/author/admin_login/' ) );
		$this->assertSame( 'The Editor', $embed['author_name'] );
		$this->assertSame( 'https://murtadd.org/', $embed['author_url'] );
	}

	public function test_xmlrpc_off_answers_nothing_and_advertises_nothing() {
		$this->assertFalse( murtadd_hardening_xmlrpc( true ) );
		$this->assertSame( array(), murtadd_hardening_xmlrpc_methods( array( 'pingback.ping' => 'x', 'wp.getUsers' => 'y' ) ) );
		$this->assertArrayNotHasKey( 'X-Pingback', murtadd_hardening_pingback_header( array( 'X-Pingback' => 'https://murtadd.org/xmlrpc.php', 'Vary' => 'Accept' ) ) );

		$GLOBALS['murtadd_options']['murtadd_security'] = array( 'xmlrpc_off' => 0 );
		$this->assertTrue( murtadd_hardening_xmlrpc( true ) );
		$this->assertCount( 1, murtadd_hardening_xmlrpc_methods( array( 'pingback.ping' => 'x' ) ) );
	}

	public function test_security_headers_full_set() {
		$headers = murtadd_security_headers( array() );
		$this->assertSame( 'nosniff', $headers['X-Content-Type-Options'] );
		$this->assertSame( 'strict-origin-when-cross-origin', $headers['Referrer-Policy'] );
		$this->assertSame( 'SAMEORIGIN', $headers['X-Frame-Options'] );
		$this->assertArrayHasKey( 'Permissions-Policy', $headers );
	}

	public function test_a_header_already_sent_is_not_sent_twice_whatever_its_case() {
		$headers = murtadd_security_headers( array( 'x-frame-options: DENY', 'Content-Type: text/html' ) );
		$this->assertArrayNotHasKey( 'X-Frame-Options', $headers, 'A stricter value set by the server must not be overridden.' );
		$this->assertArrayHasKey( 'Referrer-Policy', $headers );
	}

	#[\PHPUnit\Framework\Attributes\DataProvider( 'slugs' )]
	public function test_login_slug_validity( $slug, $valid ) {
		$this->assertSame( $valid, murtadd_login_slug_valid( $slug ), $slug );
	}

	public static function slugs() {
		return array(
			array( 'masuk-sini', true ),
			array( 'door7', true ),
			array( 'wp-admin', false ),
			array( 'login', false ),
			array( 'my-wp-login', false ),
			array( 'doubts', false ),
			array( 'ab', false ),
			array( '-edge', false ),
			array( 'Has Caps', false ),
			array( '', false ),
		);
	}

	public function test_a_saved_valid_slug_turns_the_module_on() {
		$GLOBALS['murtadd_options']['murtadd_security'] = array( 'login_slug' => 'masuk-sini' );
		$this->assertSame( 'masuk-sini', murtadd_login_slug() );
	}

	public function test_a_stored_bad_slug_never_activates() {
		$GLOBALS['murtadd_options']['murtadd_security'] = array( 'login_slug' => 'wp-admin' );
		$this->assertSame( '', murtadd_login_slug() );
	}

	public function test_saving_a_bad_slug_keeps_the_previous_one() {
		$GLOBALS['murtadd_options']['murtadd_security'] = array( 'login_slug' => 'masuk-sini' );
		$out = murtadd_sanitize_security( array( 'login_slug' => 'wp-admin' ) );
		$this->assertSame( 'masuk-sini', $out['login_slug'] );
		$this->assertContains( 'murtadd-login-slug', $GLOBALS['murtadd_settings_errors'] );
	}

	public function test_saving_a_slug_that_is_a_page_is_refused() {
		$GLOBALS['murtadd_posts']['page']['about-us'] = (object) array( 'ID' => 9 );
		$out = murtadd_sanitize_security( array( 'login_slug' => 'about-us' ) );
		$this->assertSame( '', $out['login_slug'] );
		$this->assertContains( 'murtadd-login-slug', $GLOBALS['murtadd_settings_errors'] );
	}

	public function test_clearing_the_slug_switches_the_module_off() {
		$GLOBALS['murtadd_options']['murtadd_security'] = array( 'login_slug' => 'masuk-sini' );
		$out = murtadd_sanitize_security( array( 'login_slug' => '' ) );
		$this->assertSame( '', $out['login_slug'] );
		$this->assertSame( array(), $GLOBALS['murtadd_settings_errors'] );
	}

	public function test_sanitiser_casts_switches_and_cleans_the_message() {
		$out = murtadd_sanitize_security( array( 'hide_authors' => 'on', 'login_message' => '<script>x</script>Welcome back.' . str_repeat( 'a', 600 ) ) );
		$this->assertSame( 1, $out['hide_authors'] );
		$this->assertSame( 0, $out['xmlrpc_off'], 'An unticked box saves as off.' );
		$this->assertStringNotContainsString( '<script>', $out['login_message'] );
		$this->assertLessThanOrEqual( 500, mb_strlen( $out['login_message'] ) );
	}
}
