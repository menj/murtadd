<?php
/**
 * Maintenance mode and the branded error pages.
 *
 * The drop-ins are written into wp-content, a folder the theme does not own,
 * and are shown exactly when nothing else works. So: every placeholder must
 * be filled, each file must run on its own and print a page, nothing a person
 * put there may be overwritten or removed, and the pages may carry no inline
 * style or script (docs/ssot.md, rule 1).
 *
 * @package Murtadd\Tests
 */

// Run from the command line only: on a web server this file does nothing.
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

use PHPUnit\Framework\TestCase;

final class MaintenanceTest extends TestCase {

	private $dir;

	protected function setUp(): void {
		$GLOBALS['murtadd_options']   = array();
		$GLOBALS['murtadd_test_caps'] = array();
		$this->dir                    = WP_CONTENT_DIR;
		if ( ! is_dir( $this->dir ) ) {
			mkdir( $this->dir, 0777, true );
		}
		foreach ( array_merge( glob( $this->dir . '/' . MURTADD_STATIC_ERRORS . '/*' ), glob( $this->dir . '/*' ) ) as $file ) {
			is_dir( $file ) ? rmdir( $file ) : unlink( $file );
		}
	}

	protected function tearDown(): void {
		$GLOBALS['murtadd_test_is_admin'] = false;
		$GLOBALS['murtadd_test_subsite']  = false;
		unset( $GLOBALS['murtadd_test_subfolder'] );
		unset( $_GET['murtadd_dropins'] );
		foreach ( array_merge( glob( $this->dir . '/' . MURTADD_STATIC_ERRORS . '/*' ), glob( $this->dir . '/*' ) ) as $file ) {
			is_dir( $file ) ? rmdir( $file ) : unlink( $file );
		}
		rmdir( $this->dir );
	}

	public function test_every_file_is_written_with_its_placeholders_filled() {
		$result = murtadd_install_dropins( $this->dir );
		$this->assertSame( array_fill_keys( array_keys( murtadd_dropin_files() ), 'written' ), $result );

		foreach ( array( 'maintenance.php', 'db-error.php', 'php-error.php' ) as $name ) {
			$text = file_get_contents( $this->dir . '/' . $name );
			$this->assertStringNotContainsString( '{{', $text, $name . ' kept a placeholder.' );
			$this->assertStringContainsString( MURTADD_DROPIN_MARKER, $text );
			$this->assertStringContainsString( 'href="/wp-content/murtadd-error-page.css?ver=', $text );
			$this->assertStringContainsString( 'href="/wp-content/themes/murtadd/assets/css/tokens.min.css?ver=', $text );
		}
		$this->assertStringContainsString( '<title>Back shortly | Murtadd</title>', file_get_contents( $this->dir . '/maintenance.php' ) );
		$this->assertStringContainsString( '<title>We cannot reach our database | Murtadd</title>', file_get_contents( $this->dir . '/db-error.php' ) );
		$this->assertTrue( murtadd_dropin_is_ours( $this->dir . '/murtadd-error-page.css' ), 'The stylesheet carries the marker too, so it can be removed.' );
	}

	public function test_each_dropin_runs_alone_and_prints_its_page() {
		murtadd_install_dropins( $this->dir );
		foreach ( array( 'maintenance.php' => 'Back shortly', 'db-error.php' => 'We cannot reach our database', 'php-error.php' => 'Something went wrong | Murtadd' ) as $name => $expect ) {
			$out = shell_exec( escapeshellarg( PHP_BINARY ) . ' -d display_errors=1 -f ' . escapeshellarg( $this->dir . '/' . $name ) . ' 2>&1' );
			$this->assertStringContainsString( $expect, (string) $out, $name );
			$this->assertDoesNotMatchRegularExpression( '/(Warning|Notice|Fatal error|Deprecated):/', (string) $out, $name . ' must need nothing from WordPress.' );
		}
	}

	public function test_a_file_the_theme_did_not_write_is_left_alone() {
		file_put_contents( $this->dir . '/maintenance.php', "<?php // someone else's\n" );
		$result = murtadd_install_dropins( $this->dir );
		$this->assertSame( 'kept', $result['maintenance.php'] );
		$this->assertSame( "<?php // someone else's\n", file_get_contents( $this->dir . '/maintenance.php' ) );

		murtadd_remove_dropins( $this->dir );
		$this->assertFileExists( $this->dir . '/maintenance.php', 'Removal takes only the theme\'s own files.' );
		$this->assertFileDoesNotExist( $this->dir . '/db-error.php' );
		$this->assertFileDoesNotExist( $this->dir . '/murtadd-error-page.css' );
	}

	public function test_a_previous_copy_of_its_own_is_refreshed() {
		murtadd_install_dropins( $this->dir );
		file_put_contents( $this->dir . '/db-error.php', "<?php // " . MURTADD_DROPIN_MARKER . " 0.0.1\n" );
		$this->assertSame( 'written', murtadd_install_dropins( $this->dir )['db-error.php'] );
		$this->assertStringContainsString( 'cannot reach its database', file_get_contents( $this->dir . '/db-error.php' ) );
	}

	public function test_templates_carry_no_inline_style_or_script() {
		foreach ( array( 'maintenance', 'db-error', 'php-error' ) as $name ) {
			$text = file_get_contents( dirname( __DIR__ ) . '/dropins/' . $name . '.php' );
			$this->assertStringNotContainsString( '<style', $text, $name );
			$this->assertStringNotContainsString( 'style="', $text, $name );
			$this->assertStringNotContainsString( '<script', $text, $name );
		}
	}

	public function test_every_class_the_pages_use_has_a_rule() {
		$css = file_get_contents( dirname( __DIR__ ) . '/assets/css/error-page.css' );
		foreach ( array( 'maintenance', 'db-error', 'php-error' ) as $name ) {
			preg_match_all( '/class="([^"]+)"/', file_get_contents( dirname( __DIR__ ) . '/dropins/' . $name . '.php' ), $m );
			foreach ( array_unique( explode( ' ', implode( ' ', $m[1] ) ) ) as $class ) {
				$this->assertStringContainsString( '.' . $class, $css, $class . ' has no rule in error-page.css.' );
			}
		}
		$this->assertFileExists( dirname( __DIR__ ) . '/assets/css/error-page.min.css', 'Every source asset has a .min twin.' );
	}

	public function test_maintenance_document_uses_the_theme_copy_of_the_stylesheet() {
		$html = murtadd_maintenance_document();
		$this->assertStringStartsWith( '<!DOCTYPE html>', $html, 'The PHP header must not reach the page.' );
		$this->assertStringNotContainsString( '{{', $html );
		$this->assertStringContainsString( '/wp-content/themes/murtadd/assets/css/error-page.min.css?ver=', $html );
	}

	public function test_maintenance_holds_visitors_and_lets_the_owner_through() {
		$this->assertFalse( murtadd_maintenance_applies(), 'Off by default.' );
		$GLOBALS['murtadd_options']['murtadd_upkeep'] = array( 'maintenance' => 1 );
		$this->assertTrue( murtadd_maintenance_applies() );
		$GLOBALS['murtadd_test_caps'] = array( 'edit_theme_options' );
		$this->assertFalse( murtadd_maintenance_applies() );
	}

	public function test_error_pages_are_on_by_default() {
		$this->assertSame( 1, murtadd_upkeep( 'error_pages' ) );
	}

	public function test_switching_error_pages_off_after_a_partial_install_removes_what_was_written() {
		$GLOBALS['murtadd_test_is_admin'] = true;
		$GLOBALS['murtadd_test_caps']     = array( 'manage_options' );
		// A first install that wrote some files and failed on one: status saved, no stamp.
		murtadd_install_dropins( $this->dir );
		unlink( $this->dir . '/db-error.php' );
		update_option( 'murtadd_dropins_status', array( 'db-error.php' => 'failed' ) );
		$this->assertFalse( get_option( 'murtadd_dropins_stamp' ) );

		$GLOBALS['murtadd_options']['murtadd_upkeep'] = array( 'error_pages' => 0 );
		murtadd_maybe_install_dropins();

		$this->assertFileDoesNotExist( $this->dir . '/maintenance.php', 'A drop-in from the partial install stayed active.' );
		$this->assertFileDoesNotExist( $this->dir . '/php-error.php' );
		$this->assertFalse( get_option( 'murtadd_dropins_status' ) );
	}

	public function test_each_dropin_sends_its_own_status() {
		murtadd_install_dropins( $this->dir );
		$this->assertStringContainsString( "503 Service Unavailable', true, 503", file_get_contents( $this->dir . '/maintenance.php' ) );
		$this->assertStringContainsString( "503 Service Unavailable', true, 503", file_get_contents( $this->dir . '/db-error.php' ) );
		$this->assertStringContainsString( "500 Internal Server Error', true, 500", file_get_contents( $this->dir . '/php-error.php' ) );
		foreach ( array( 'maintenance.php', 'db-error.php' ) as $name ) {
			$this->assertStringContainsString( 'Retry-After', file_get_contents( $this->dir . '/' . $name ), $name );
		}
		$this->assertStringNotContainsString( 'Maintenance', file_get_contents( $this->dir . '/db-error.php' ) );
	}

	public function test_state_reads_missing_written_outdated_and_kept() {
		$this->assertSame( 'missing', murtadd_dropin_state( 'php-error.php', $this->dir ) );
		murtadd_install_dropins( $this->dir );
		$this->assertSame( 'written', murtadd_dropin_state( 'php-error.php', $this->dir ) );
		file_put_contents( $this->dir . '/php-error.php', file_get_contents( $this->dir . '/maintenance.php' ) );
		$this->assertSame( 'outdated', murtadd_dropin_state( 'php-error.php', $this->dir ), 'The maintenance page in php-error.php is not current.' );
		$this->assertSame( 'written', murtadd_install_dropins( $this->dir )['php-error.php'] );
		$this->assertSame( 'written', murtadd_dropin_state( 'php-error.php', $this->dir ) );
		file_put_contents( $this->dir . '/db-error.php', "<?php // someone else's\n" );
		$this->assertSame( 'kept', murtadd_dropin_state( 'db-error.php', $this->dir ) );
	}

	public function test_static_pages_are_current_and_carry_no_inline_style() {
		exec( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( dirname( __DIR__ ) . '/bin/build-error-pages.php' ) . ' --check 2>&1', $out, $code );
		$this->assertSame( 0, $code, implode( "\n", $out ) );
		foreach ( array( '403', '500', '502', '503' ) as $page ) {
			$html = file_get_contents( dirname( __DIR__ ) . '/errors/' . $page . '.html' );
			$this->assertStringContainsString( '<meta name="robots" content="noindex">', $html );
			$this->assertStringNotContainsString( '<style', $html );
			$this->assertStringNotContainsString( '<script', $html );
			$this->assertMatchesRegularExpression( '~<title>[^<|]+ \| Murtadd</title>~', $html );
		}
		$this->assertStringContainsString( 'ErrorDocument 503 /wp-content/murtadd-errors/503.html', murtadd_error_htaccess_snippet() );
	}

	public function test_the_static_copies_carry_the_addresses_of_a_subfolder_install() {
		murtadd_install_dropins( $this->dir );
		$root = file_get_contents( $this->dir . '/murtadd-errors/403.html' );
		$this->assertTrue( murtadd_dropin_is_ours( $this->dir . '/murtadd-errors/403.html' ) );
		$this->assertSame( file_get_contents( dirname( __DIR__ ) . '/errors/403.html' ), substr( $root, strpos( $root, "\n" ) + 1 ), 'A root install copies errors/ as it is.' );

		$GLOBALS['murtadd_test_subfolder'] = '/blog';
		$this->assertSame( 'outdated', murtadd_dropin_state( 'murtadd-errors/403.html', $this->dir ) );
		murtadd_install_dropins( $this->dir );
		$sub = file_get_contents( $this->dir . '/murtadd-errors/403.html' );
		$this->assertStringContainsString( 'href="/blog/"', $sub );
		$this->assertStringContainsString( 'href="/blog/wp-content/themes/murtadd/assets/css/tokens.min.css"', $sub );
		$this->assertStringNotContainsString( 'href="/"', $sub );
		$this->assertStringNotContainsString( 'href="/wp-content/', $sub );
		$this->assertStringContainsString( 'ErrorDocument 403 /blog/wp-content/murtadd-errors/403.html', murtadd_error_htaccess_snippet() );

		murtadd_remove_dropins( $this->dir );
		$this->assertDirectoryDoesNotExist( $this->dir . '/murtadd-errors' );
	}

	public function test_a_subsite_writes_only_while_the_main_site_runs_another_theme() {
		$GLOBALS['murtadd_test_subsite'] = true;
		$this->assertFalse( murtadd_dropins_writer() );
		$GLOBALS['murtadd_test_main_theme'] = 'twentytwentyfive';
		$this->assertTrue( murtadd_dropins_writer() );
		$GLOBALS['murtadd_test_blog_id'] = 3;
		$this->assertFalse( murtadd_dropins_writer(), 'A second subsite leaves the writing to the first.' );
		unset( $GLOBALS['murtadd_test_blog_id'] );
		unset( $GLOBALS['murtadd_test_subsite'], $GLOBALS['murtadd_test_main_theme'] );
		$this->assertTrue( murtadd_dropins_writer() );
	}

	public function test_a_subsite_shows_the_network_note_in_place_of_the_button() {
		$GLOBALS['murtadd_options']['murtadd_upkeep'] = array( 'error_pages' => 1 );
		$_GET['murtadd_dropins']                     = 1;
		ob_start();
		murtadd_render_dropins_status();
		$main = ob_get_clean();
		$this->assertStringContainsString( 'murtadd_write_dropins', $main );
		$this->assertStringNotContainsString( 'The whole network shares wp-content', $main );

		$GLOBALS['murtadd_test_subsite'] = true;
		ob_start();
		murtadd_render_dropins_status();
		$sub = ob_get_clean();
		$this->assertStringNotContainsString( 'murtadd_write_dropins', $sub );
		$this->assertStringNotContainsString( 'written again', $sub );
		$this->assertStringContainsString( 'The whole network shares wp-content', $sub );
	}

	public function test_a_failed_file_no_longer_blames_wp_content_alone() {
		update_option( 'murtadd_dropins_status', array( 'db-error.php' => 'failed' ) );
		ob_start();
		murtadd_render_dropins_status();
		$out = ob_get_clean();
		$this->assertStringContainsString( 'wp-content not writable or source file unreadable', $out );
	}
}
