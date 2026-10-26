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
		foreach ( glob( $this->dir . '/*' ) as $file ) {
			unlink( $file );
		}
	}

	protected function tearDown(): void {
		foreach ( glob( $this->dir . '/*' ) as $file ) {
			unlink( $file );
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
			$this->assertStringContainsString( '<title>Back shortly | Murtadd</title>', $text );
		}
		$this->assertTrue( murtadd_dropin_is_ours( $this->dir . '/murtadd-error-page.css' ), 'The stylesheet carries the marker too, so it can be removed.' );
	}

	public function test_each_dropin_runs_alone_and_prints_its_page() {
		murtadd_install_dropins( $this->dir );
		foreach ( array( 'maintenance.php' => 'Back shortly', 'db-error.php' => 'cannot reach its database', 'php-error.php' => 'This page could not load' ) as $name => $expect ) {
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
}
