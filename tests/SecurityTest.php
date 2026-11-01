<?php
/**
 * Security module: the spam gate, the REST lock and the .htaccess block.
 *
 * Ported from the sister theme Abrahamic after the October 2026 attack:
 * password guessing through xmlrpc.php, then a casino post every 18 to 20
 * minutes through wp.newPost. Each test pins one door that attack used, and
 * one that must stay open (seeded content, visitors, the contact form).
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

if ( ! defined( 'REST_REQUEST' ) ) {
	// Only inc/security.php reads it. The guard checks the method, the user
	// and the cookie session, which each test sets.
	define( 'REST_REQUEST', true );
}
if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		public $code;
		public $message;
		public $data;
		public function __construct( $code = '', $message = '', $data = '' ) {
			$this->code    = $code;
			$this->message = $message;
			$this->data    = $data;
		}
		public function get_error_code() {
			return $this->code;
		}
	}
}
if ( ! function_exists( 'get_current_user_id' ) ) {
	function get_current_user_id() {
		return (int) ( $GLOBALS['murtadd_test_user'] ?? 0 );
	}
}
if ( ! function_exists( 'wp_doing_cron' ) ) {
	function wp_doing_cron() {
		return false;
	}
}
if ( ! function_exists( 'delete_transient' ) ) {
	function delete_transient( $name ) {
		unset( $GLOBALS['murtadd_transients'][ $name ] );
		return true;
	}
}
if ( ! function_exists( 'get_home_path' ) ) {
	function get_home_path() {
		return $GLOBALS['murtadd_test_home'];
	}
}
if ( ! function_exists( 'wp_is_writable' ) ) {
	function wp_is_writable( $path ) {
		return is_writable( $path );
	}
}
if ( ! function_exists( 'extract_from_markers' ) ) {
	function extract_from_markers( $file, $marker ) {
		if ( ! file_exists( $file ) ) {
			return array();
		}
		$out  = array();
		$in   = false;
		foreach ( explode( "\n", (string) file_get_contents( $file ) ) as $line ) {
			if ( false !== strpos( $line, '# END ' . $marker ) ) {
				$in = false;
			}
			if ( $in && '' !== $line && 0 !== strpos( $line, '#' ) ) {
				$out[] = $line;
			}
			if ( false !== strpos( $line, '# BEGIN ' . $marker ) ) {
				$in = true;
			}
		}
		return $out;
	}
}
if ( ! function_exists( 'insert_with_markers' ) ) {
	function insert_with_markers( $file, $marker, $lines ) {
		$text = file_exists( $file ) ? (string) file_get_contents( $file ) : '';
		$text = (string) preg_replace( '/\n?# BEGIN ' . preg_quote( $marker, '/' ) . '.*?# END ' . preg_quote( $marker, '/' ) . '\n?/s', "\n", $text );
		$text = trim( $text ) . "\n# BEGIN " . $marker . "\n" . implode( "\n", (array) $lines ) . "\n# END " . $marker . "\n";
		$GLOBALS['murtadd_test_marker_writes'][] = $marker;
		return false !== file_put_contents( $file, $text );
	}
}

require_once __DIR__ . '/../inc/security-detector.php';
require_once __DIR__ . '/../inc/security.php';

/** Minimal REST request double. */
final class Murtadd_Test_Rest_Request {
	private $method;
	private $route;
	private $headers;
	public function __construct( $method, $route, $headers = array() ) {
		$this->method  = $method;
		$this->route   = $route;
		$this->headers = $headers;
	}
	public function get_method() {
		return $this->method;
	}
	public function get_route() {
		return $this->route;
	}
	public function get_header( $name ) {
		return $this->headers[ strtolower( $name ) ] ?? null;
	}
}

final class SecurityTest extends TestCase {

	private $home = '';

	protected function setUp(): void {
		$GLOBALS['murtadd_options']             = array();
		$GLOBALS['murtadd_transients']          = array();
		$GLOBALS['murtadd_test_user']           = 0;
		$GLOBALS['murtadd_sec_held']            = null;
		$GLOBALS['murtadd_test_marker_writes']  = array();
		unset( $GLOBALS['wp_rest_auth_cookie'], $GLOBALS['wp_rest_application_password_uuid'] );
		$this->home                   = sys_get_temp_dir() . '/murtadd-sec-' . uniqid() . '/';
		mkdir( $this->home );
		$GLOBALS['murtadd_test_home'] = $this->home;
		$GLOBALS['is_apache']         = true;
	}

	protected function tearDown(): void {
		if ( file_exists( $this->home . '.htaccess' ) ) {
			unlink( $this->home . '.htaccess' );
		}
		if ( is_dir( $this->home ) ) {
			rmdir( $this->home );
		}
		$GLOBALS['is_apache'] = false;
	}

	private function post( $title, $slug, $content, $status = 'publish', $type = 'post' ) {
		return array(
			'post_type'    => $type,
			'post_status'  => $status,
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => $content,
		);
	}

	/* ---------- Defaults ---------- */

	public function test_every_lock_is_on_by_default() {
		foreach ( array( 'xmlrpc_off', 'app_passwords_off', 'rest_lock', 'spam_gate', 'spam_gone' ) as $key ) {
			$this->assertTrue( murtadd_sec_on( $key ), $key );
		}
		$this->assertFalse( murtadd_sec_app_passwords( true ) );
	}

	public function test_a_switch_turned_off_stays_off() {
		$GLOBALS['murtadd_options']['murtadd_security'] = array( 'app_passwords_off' => 0 );
		$this->assertTrue( murtadd_sec_app_passwords( true ) );
	}

	public function test_sanitiser_keeps_the_new_switches() {
		$out = murtadd_sanitize_security( array( 'rest_lock' => '1', 'spam_gate' => '1' ) );
		$this->assertSame( 1, $out['rest_lock'] );
		$this->assertSame( 1, $out['spam_gate'] );
		$this->assertSame( 0, $out['app_passwords_off'] );
		$this->assertSame( 0, $out['spam_gone'] );
		$this->assertSame( 0, $out['xmlrpc_off'] );
	}

	public function test_there_is_one_xmlrpc_switch() {
		$php = (string) file_get_contents( dirname( __DIR__ ) . '/inc/settings-page.php' );
		$this->assertSame( 1, substr_count( $php, 'name="murtadd_security[xmlrpc_off]"' ) );
		$this->assertStringContainsString( 'blocks xmlrpc.php in .htaccess', $php );
	}

	/* ---------- Spam gate ---------- */

	public function test_casino_post_is_held_as_pending() {
		$out = murtadd_sec_gate( $this->post( 'Exclusive Coolbet Bonus Code Unlocks Big Wins', 'coolbet-bonus-code', 'Claim free spins at the online casino today.' ), array() );
		$this->assertSame( 'pending', $out['post_status'] );
		$this->assertSame( 'held', $GLOBALS['murtadd_sec_held']['event'] );
	}

	public function test_gate_covers_the_theme_post_types() {
		foreach ( array( 'page', 'murtadd_doubt', 'murtadd_rebuttal', 'murtadd_guide', 'murtadd_fatwa', 'murtadd_letter' ) as $type ) {
			$out = murtadd_sec_gate( $this->post( 'Mostbet giriş bonus', 'mostbet-giris', 'Online casino free spins.', 'publish', $type ), array() );
			$this->assertSame( 'pending', $out['post_status'], $type );
		}
	}

	public function test_menus_and_attachments_are_never_touched() {
		$out = murtadd_sec_gate( $this->post( 'Mostbet giriş bonus', 'mostbet-giris', 'Online casino free spins.', 'publish', 'nav_menu_item' ), array() );
		$this->assertSame( 'publish', $out['post_status'] );
	}

	public function test_ordinary_prose_passes() {
		$out = murtadd_sec_gate( $this->post( 'What is at stake when a child leaves Islam', 'what-is-at-stake', 'The odds are against an honest review of the testimony, so we bet on patience.' ), array() );
		$this->assertSame( 'publish', $out['post_status'] );
		$this->assertNull( $GLOBALS['murtadd_sec_held'] );
	}

	public function test_seeded_content_is_exempt() {
		$GLOBALS['murtadd_options']['murtadd_seeded_slugs'] = array( 'post' => array( 'casino-night' ) );
		$out = murtadd_sec_gate( $this->post( 'Casino night', 'casino-night', 'Online casino free spins.' ), array() );
		$this->assertSame( 'publish', $out['post_status'] );
	}

	public function test_seed_files_entry_is_exempt_by_title_and_slug() {
		$doubts = murtadd_seed_doubts();
		$this->assertNotEmpty( $doubts );
		$d   = $doubts[0];
		$out = murtadd_sec_gate( $this->post( $d['title'], $d['slug'], 'free spins online casino', 'publish', 'murtadd_doubt' ), array() );
		$this->assertSame( 'publish', $out['post_status'] );
	}

	public function test_remote_publish_is_held_whatever_it_says() {
		$GLOBALS['murtadd_test_user'] = 1; // Logged in over REST with no cookie session: a header login.
		$out = murtadd_sec_gate( $this->post( 'A quiet note on patience', 'a-quiet-note', 'Plain text.' ), array() );
		$this->assertSame( 'pending', $out['post_status'] );
	}

	public function test_browser_session_publish_is_not_held() {
		$GLOBALS['murtadd_test_user']    = 1;
		$GLOBALS['wp_rest_auth_cookie'] = true;
		$out = murtadd_sec_gate( $this->post( 'A quiet note on patience', 'a-quiet-note', 'Plain text.' ), array() );
		$this->assertSame( 'publish', $out['post_status'] );
	}

	public function test_gate_off_lets_everything_through() {
		$GLOBALS['murtadd_options']['murtadd_security'] = array( 'spam_gate' => 0 );
		$out = murtadd_sec_gate( $this->post( 'Coolbet bonus', 'coolbet', 'online casino' ), array() );
		$this->assertSame( 'publish', $out['post_status'] );
	}

	/* ---------- REST lock ---------- */

	public function test_header_login_write_is_refused() {
		$GLOBALS['murtadd_test_user'] = 1;
		$out = murtadd_sec_rest_guard( null, null, new Murtadd_Test_Rest_Request( 'POST', '/wp/v2/posts' ) );
		$this->assertInstanceOf( 'WP_Error', $out );
		$this->assertSame( 'murtadd_rest_locked', $out->get_error_code() );
	}

	public function test_cookie_session_with_nonce_may_write() {
		$GLOBALS['murtadd_test_user']    = 1;
		$GLOBALS['wp_rest_auth_cookie'] = true;
		$out = murtadd_sec_rest_guard( null, null, new Murtadd_Test_Rest_Request( 'POST', '/wp/v2/posts', array( 'x-wp-nonce' => 'nonce-wp_rest' ) ) );
		$this->assertNull( $out );
		$bad = murtadd_sec_rest_guard( null, null, new Murtadd_Test_Rest_Request( 'POST', '/wp/v2/posts', array( 'x-wp-nonce' => 'forged' ) ) );
		$this->assertInstanceOf( 'WP_Error', $bad, 'A cookie without a valid nonce is refused.' );
	}

	public function test_reads_and_visitors_are_unchanged() {
		$GLOBALS['murtadd_test_user'] = 1;
		$this->assertNull( murtadd_sec_rest_guard( null, null, new Murtadd_Test_Rest_Request( 'GET', '/wp/v2/posts' ) ) );
		$GLOBALS['murtadd_test_user'] = 0;
		$this->assertNull( murtadd_sec_rest_guard( null, null, new Murtadd_Test_Rest_Request( 'POST', '/wp/v2/comments' ) ) );
	}

	public function test_emergency_constant_is_honoured_in_code() {
		$php = (string) file_get_contents( dirname( __DIR__ ) . '/inc/security.php' );
		$this->assertStringContainsString( "defined( 'MURTADD_SECURITY_LOCK' ) && ! MURTADD_SECURITY_LOCK", $php );
	}

	/* ---------- .htaccess ---------- */

	public function test_htaccess_block_is_written_when_xmlrpc_is_off() {
		murtadd_sec_sync_htaccess( true );
		$this->assertSame( murtadd_sec_htaccess_lines(), extract_from_markers( $this->home . '.htaccess', 'Block xmlrpc.php' ) );
		$this->assertSame( 'written', get_option( 'murtadd_security_htaccess' ) );
		$this->assertSame( array( 'Block xmlrpc.php' ), $GLOBALS['murtadd_test_marker_writes'] );
	}

	public function test_htaccess_check_is_throttled_and_repairs() {
		murtadd_sec_sync_htaccess( true );
		file_put_contents( $this->home . '.htaccess', "# BEGIN WordPress\n# END WordPress\n" );
		murtadd_sec_sync_htaccess();
		$this->assertSame( array(), extract_from_markers( $this->home . '.htaccess', 'Block xmlrpc.php' ), 'Within the hour, no check.' );
		unset( $GLOBALS['murtadd_transients']['murtadd_sec_htaccess_checked'] );
		murtadd_sec_sync_htaccess();
		$this->assertSame( murtadd_sec_htaccess_lines(), extract_from_markers( $this->home . '.htaccess', 'Block xmlrpc.php' ) );
	}

	public function test_htaccess_block_is_removed_when_xmlrpc_is_on() {
		murtadd_sec_sync_htaccess( true );
		$GLOBALS['murtadd_options']['murtadd_security'] = array( 'xmlrpc_off' => 0 );
		murtadd_sec_sync_htaccess( true );
		$this->assertSame( array(), extract_from_markers( $this->home . '.htaccess', 'Block xmlrpc.php' ) );
		$this->assertSame( 'off', get_option( 'murtadd_security_htaccess' ) );
	}

	public function test_htaccess_unavailable_off_apache() {
		$GLOBALS['is_apache'] = false;
		murtadd_sec_sync_htaccess( true );
		$this->assertSame( 'unavailable', get_option( 'murtadd_security_htaccess' ) );
		$this->assertFileDoesNotExist( $this->home . '.htaccess' );
	}

	public function test_switch_theme_removes_the_block() {
		murtadd_sec_sync_htaccess( true );
		murtadd_sec_remove_htaccess();
		$this->assertSame( array(), extract_from_markers( $this->home . '.htaccess', 'Block xmlrpc.php' ) );
	}

	public function test_htaccess_hooks_are_registered() {
		$this->assertNotNull( murtadd_test_hook_priority( 'admin_init', 'murtadd_sec_sync_htaccess' ) );
		$this->assertNotNull( murtadd_test_hook_priority( 'update_option_murtadd_security', 'murtadd_sec_sync_htaccess_now' ) );
		$this->assertNotNull( murtadd_test_hook_priority( 'after_switch_theme', 'murtadd_sec_sync_htaccess_now' ) );
		$this->assertNotNull( murtadd_test_hook_priority( 'switch_theme', 'murtadd_sec_remove_htaccess' ) );
	}

	/* ---------- 410 ---------- */

	public function test_trashed_spam_answers_gone() {
		$GLOBALS['murtadd_options'][ MURTADD_SEC_GONE ] = array( 'coolbet-bonus' => 9 );
		$this->assertTrue( murtadd_sec_gone_match( false, '/coolbet-bonus/' ) );
		$this->assertFalse( murtadd_sec_gone_match( false, '/about/' ) );
	}
}
