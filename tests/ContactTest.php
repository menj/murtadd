<?php
/**
 * The contact form (inc/contact.php).
 *
 * The owner publishes no address, so this form is the only way a reader can
 * write, and the reader it is built for may be writing about something that
 * frightens them. The failures worth pinning: a form any site can post to, a
 * bot that gets through, one connection flooding the inbox, an IP address
 * written to the database, a header injected through the email field, and
 * an address published on the page.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

if ( ! function_exists( 'wp_nonce_field' ) ) {
	function wp_nonce_field( $action = -1, $name = '_wpnonce', $referer = true, $display = true ) {
		echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="nonce-' . esc_attr( $action ) . '">';
	}
}
if ( ! function_exists( 'esc_textarea' ) ) {
	function esc_textarea( $s ) {
		return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' );
	}
}

final class ContactTest extends TestCase {

	private const IP = '203.0.113.7';

	protected function setUp(): void {
		$GLOBALS['murtadd_options']    = array( 'admin_email' => 'admin@example.org' );
		$GLOBALS['murtadd_transients'] = array();
		$GLOBALS['murtadd_mail']       = array();
		$GLOBALS['murtadd_mail_fails'] = false;
		$_GET                          = array();
		$_POST                         = array();
	}

	/** A valid submission, with any field overridden. */
	private function post( $over = array() ) {
		return array_merge(
			array(
				'murtadd_contact_nonce'   => 'nonce-murtadd_contact',
				'murtadd_contact_name'    => 'A reader',
				'murtadd_contact_email'   => 'reader@example.com',
				'murtadd_contact_message' => 'The entry on witnesses cites the wrong verse.',
				'murtadd_contact_url'     => '',
				'murtadd_contact_submit'  => '1',
			),
			$over
		);
	}

	private function send( $over = array(), $ip = self::IP ) {
		return murtadd_contact_process( $this->post( $over ), murtadd_contact_ip_hash( $ip ) );
	}

	public function test_valid_message_is_mailed_to_the_admin_address() {
		$r = $this->send();
		$this->assertSame( 'sent', $r['status'] );
		$this->assertCount( 1, $GLOBALS['murtadd_mail'] );
		$mail = $GLOBALS['murtadd_mail'][0];
		$this->assertSame( 'admin@example.org', $mail['to'] );
		$this->assertStringContainsString( 'The entry on witnesses cites the wrong verse.', $mail['message'] );
		$this->assertStringContainsString( 'Name: A reader', $mail['message'] );
		$this->assertContains( 'Reply-To: reader@example.com', $mail['headers'] );
	}

	public function test_name_and_email_are_optional() {
		$r = $this->send(
			array(
				'murtadd_contact_name'  => '',
				'murtadd_contact_email' => '',
			)
		);
		$this->assertSame( 'sent', $r['status'] );
		$mail = $GLOBALS['murtadd_mail'][0];
		$this->assertStringContainsString( 'Name: not given', $mail['message'] );
		$this->assertStringContainsString( 'Email: not given', $mail['message'] );
		foreach ( $mail['headers'] as $h ) {
			$this->assertStringStartsNotWith( 'Reply-To', $h, 'A reply address was set with no email given.' );
		}
	}

	public function test_missing_or_wrong_nonce_sends_nothing() {
		foreach ( array( '', 'nonce-other_action', 'forged' ) as $nonce ) {
			$r = $this->send( array( 'murtadd_contact_nonce' => $nonce ) );
			$this->assertSame( 'nonce', $r['status'], "Nonce '$nonce' was accepted." );
		}
		$r = murtadd_contact_process( array_diff_key( $this->post(), array( 'murtadd_contact_nonce' => 1 ) ), murtadd_contact_ip_hash( self::IP ) );
		$this->assertSame( 'nonce', $r['status'] );
		$this->assertSame( array(), $GLOBALS['murtadd_mail'], 'A message without a valid nonce was mailed.' );
	}

	public function test_filled_honeypot_looks_sent_and_mails_nothing() {
		$r = $this->send( array( 'murtadd_contact_url' => 'https://spam.example/' ) );
		$this->assertSame( 'sent', $r['status'], 'A bot must not learn that it was caught.' );
		$this->assertSame( array(), $GLOBALS['murtadd_mail'], 'A filled honeypot was mailed.' );
	}

	public function test_rate_limit_per_address() {
		$limit = murtadd_contact_limits()['per_hour'];
		for ( $i = 0; $i < $limit; $i++ ) {
			$this->assertSame( 'sent', $this->send()['status'] );
		}
		$this->assertSame( 'limited', $this->send()['status'], 'The limit did not hold.' );
		$this->assertCount( $limit, $GLOBALS['murtadd_mail'] );

		// Another connection is not held back by the first.
		$this->assertSame( 'sent', $this->send( array(), '198.51.100.20' )['status'] );
	}

	public function test_honeypot_hits_count_against_the_limit() {
		$limit = murtadd_contact_limits()['per_hour'];
		for ( $i = 0; $i < $limit; $i++ ) {
			$this->send( array( 'murtadd_contact_url' => 'x' ) );
		}
		$this->assertSame( 'limited', $this->send()['status'] );
	}

	public function test_only_a_hash_of_the_address_is_stored() {
		$this->send();
		$hash = murtadd_contact_ip_hash( self::IP );
		$this->assertMatchesRegularExpression( '/^[0-9a-f]{64}$/', $hash );
		$this->assertSame( $hash, murtadd_contact_ip_hash( self::IP ), 'The hash is not stable, so the limit cannot work.' );
		$this->assertNotSame( $hash, murtadd_contact_ip_hash( '203.0.113.8' ) );
		$this->assertNotSame( hash( 'sha256', self::IP ), $hash, 'An unkeyed hash of an IPv4 address can be reversed by brute force.' );

		$stored = (string) wp_json_encode( $GLOBALS['murtadd_transients'] ) . (string) wp_json_encode( $GLOBALS['murtadd_options'] );
		$this->assertStringNotContainsString( self::IP, $stored, 'The IP address was written to storage.' );
		$this->assertArrayHasKey( murtadd_contact_rate_key( $hash ), $GLOBALS['murtadd_transients'] );
		$this->assertStringNotContainsString( self::IP, $GLOBALS['murtadd_mail'][0]['message'], 'The IP address was mailed.' );
	}

	public function test_fields_are_sanitised_and_capped() {
		$limits = murtadd_contact_limits();
		$f      = murtadd_contact_sanitise(
			array(
				'murtadd_contact_name'    => '<b>Ann</b><script>x</script> ' . str_repeat( 'n', 300 ),
				'murtadd_contact_email'   => ' reader@example.com ',
				'murtadd_contact_message' => "<p>Line one</p>\n<a href=\"https://x\">two</a> " . str_repeat( 'm', 9000 ),
			)
		);
		$this->assertStringNotContainsString( '<', $f['name'] );
		$this->assertLessThanOrEqual( $limits['name'], mb_strlen( $f['name'] ) );
		$this->assertSame( 'reader@example.com', $f['email'] );
		$this->assertStringNotContainsString( '<', $f['message'] );
		$this->assertStringContainsString( "Line one\n", $f['message'], 'Line breaks in a message are the reader\'s and must survive.' );
		$this->assertLessThanOrEqual( $limits['message'], mb_strlen( $f['message'] ) );
	}

	public function test_header_injection_through_the_email_field_fails() {
		$r = $this->send( array( 'murtadd_contact_email' => "reader@example.com\r\nBcc: victim@example.net" ) );
		$this->assertSame( 'invalid', $r['status'] );
		$this->assertSame( array( 'email' ), $r['errors'] );
		$this->assertSame( array(), $GLOBALS['murtadd_mail'] );
	}

	public function test_empty_message_is_refused_and_not_counted() {
		$r = $this->send( array( 'murtadd_contact_message' => "  <p></p>\n " ) );
		$this->assertSame( 'invalid', $r['status'] );
		$this->assertSame( array( 'message' ), $r['errors'] );
		$this->assertSame( array(), $GLOBALS['murtadd_mail'] );
		$this->assertSame( array(), $GLOBALS['murtadd_transients'], 'A refused message used up the allowance.' );
	}

	public function test_failed_mail_is_reported_and_keeps_the_words() {
		$GLOBALS['murtadd_mail_fails'] = true;
		$r                             = $this->send();
		$this->assertSame( 'failed', $r['status'] );
		$this->assertSame( 'The entry on witnesses cites the wrong verse.', $r['fields']['message'] );
	}

	public function test_recipient_override_and_fallback() {
		$GLOBALS['murtadd_options']['murtadd_content_display'] = array( 'contact_recipient' => 'editor@example.org' );
		$this->assertSame( 'editor@example.org', murtadd_contact_recipient() );
		$GLOBALS['murtadd_options']['murtadd_content_display'] = array( 'contact_recipient' => 'not an address' );
		$this->assertSame( 'admin@example.org', murtadd_contact_recipient() );
		$GLOBALS['murtadd_options']['murtadd_content_display'] = array();
		$this->assertSame( 'admin@example.org', murtadd_contact_recipient() );
	}

	public function test_recipient_setting_is_sanitised_on_save() {
		$this->assertSame( 'editor@example.org', murtadd_sanitize_content_display( array( 'contact_recipient' => ' editor@example.org ' ) )['contact_recipient'] );
		$this->assertSame( '', murtadd_sanitize_content_display( array( 'contact_recipient' => 'nobody' ) )['contact_recipient'] );
		$this->assertSame( '', murtadd_sanitize_content_display( array() )['contact_recipient'] );
	}

	public function test_form_markup() {
		$html = murtadd_contact_shortcode();
		$this->assertStringContainsString( 'name="murtadd_contact_nonce" value="nonce-murtadd_contact"', $html );
		$this->assertMatchesRegularExpression( '/<div class="murtadd-contact__trap" aria-hidden="true">.*name="murtadd_contact_url"[^>]*tabindex="-1"/s', $html );
		$this->assertMatchesRegularExpression( '/<textarea[^>]*name="murtadd_contact_message"[^>]*required/', $html );
		$this->assertDoesNotMatchRegularExpression( '/name="murtadd_contact_(name|email)"[^>]*required/', $html, 'Name and email must stay optional.' );
		$this->assertDoesNotMatchRegularExpression( '/\sstyle=["\']/', $html, 'The form carries an inline style.' );
		$this->assertStringNotContainsString( 'admin@example.org', $html, 'The recipient address was published.' );
		$this->assertStringNotContainsString( 'mailto:', $html );
	}

	public function test_notices() {
		$_GET['murtadd_contact'] = 'sent';
		$this->assertStringContainsString( 'murtadd-contact__notice--success', murtadd_contact_shortcode() );

		$_GET = array();
		murtadd_contact_result( $this->send( array( 'murtadd_contact_message' => '' ) ) );
		$html = murtadd_contact_shortcode();
		$this->assertStringContainsString( 'murtadd-contact__notice--error', $html );
		$this->assertStringContainsString( 'role="alert"', $html );
		$this->assertStringContainsString( 'value="A reader"', $html, 'The reader\'s words were lost on an error.' );
		murtadd_contact_result( array( 'status' => 'limited', 'errors' => array(), 'fields' => murtadd_contact_sanitise( array() ) ) );
		$this->assertStringContainsString( murtadd_contact_message( 'limited' ), murtadd_contact_shortcode() );
		murtadd_contact_result( array( 'status' => '', 'errors' => array(), 'fields' => array() ) );
	}

	public function test_page_carries_the_form_and_no_address() {
		$body = murtadd_seed_page_bodies()['contact'];
		$this->assertStringContainsString( '[murtadd_contact]', $body );
		$this->assertDoesNotMatchRegularExpression( '/@|mailto:/', $body );
		$this->assertMatchesRegularExpression( '/emergency services/i', $body, 'The page must send a reader in danger elsewhere first.' );
		$this->assertMatchesRegularExpression( '/not stored on the site/i', $body, 'The page must say what happens to a message.' );
	}

	public function test_styles_and_script_ship_with_min_twins_and_stay_light() {
		$root = dirname( __DIR__ );
		foreach ( array( 'assets/css/contact.css', 'assets/css/contact.min.css', 'assets/js/contact.js', 'assets/js/contact.min.js' ) as $file ) {
			$this->assertFileExists( "$root/$file" );
		}
		foreach ( array( 'contact.css', 'contact.min.css' ) as $file ) {
			$css = (string) file_get_contents( "$root/assets/css/$file" );
			$this->assertStringNotContainsString( 'prefers-color-scheme', $css, "$file adds a dark mode, which the owner excluded." );
			$this->assertMatchesRegularExpression( '/\.murtadd-contact__submit\{?[^}]*border-radius:\s*999px/', str_replace( array( "\n", "\t" ), '', $css ), "$file: the button is not a pill." );
			foreach ( array( 'murtadd-contact', 'murtadd-contact__field', 'murtadd-contact__trap', 'murtadd-contact__submit', 'murtadd-contact__notice--success', 'murtadd-contact__notice--error', 'murtadd-contact__hint', 'murtadd-contact__privacy' ) as $class ) {
				$this->assertStringContainsString( '.' . $class, $css, "$class missing from $file." );
			}
		}
		$this->assertLessThan( filesize( "$root/assets/css/contact.css" ), filesize( "$root/assets/css/contact.min.css" ) );
		$this->assertSame( 10, murtadd_test_hook_priority( 'template_redirect', 'murtadd_contact_handle_post' ) );
		$this->assertNotNull( murtadd_test_hook_priority( 'shortcode:murtadd_contact', 'murtadd_contact_shortcode' ) );
	}
}
