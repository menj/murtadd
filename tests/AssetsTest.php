<?php
/**
 * Asset separation invariants.
 *
 * Presentation lives in assets/css and behaviour in assets/js. A style
 * attribute in admin markup cannot be themed, cannot be overridden by the
 * owner's accent and drifts from the stylesheet; every class the admin markup
 * relies on must therefore exist in admin.css and in its minified twin, which
 * is the file a live site serves.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class AssetsTest extends TestCase {

	/** Theme PHP that renders admin screens carries no inline style attribute. */
	public function test_admin_markup_has_no_inline_styles() {
		foreach ( array( 'inc/settings-page.php', 'inc/dashboard.php' ) as $file ) {
			$php = (string) file_get_contents( dirname( __DIR__ ) . '/' . $file );
			$this->assertDoesNotMatchRegularExpression( '/\sstyle=["\']/', $php, $file . ' has an inline style attribute.' );
		}
	}

	/** Each class the admin markup uses in place of an inline style is defined, minified too. */
	public function test_admin_classes_are_defined() {
		$css = (string) file_get_contents( dirname( __DIR__ ) . '/assets/css/admin.css' );
		$min = (string) file_get_contents( dirname( __DIR__ ) . '/assets/css/admin.min.css' );
		foreach ( array( 'murtadd-gaps-none', 'murtadd-gaps-label', 'murtadd-gaps-list', 'murtadd-admin-table', 'murtadd-status-missing', 'murtadd-admin-note', 'murtadd-admin-spaced' ) as $class ) {
			$this->assertStringContainsString( '.' . $class, $css, $class . ' missing from admin.css.' );
			$this->assertStringContainsString( '.' . $class, $min, $class . ' missing from admin.min.css.' );
		}
	}
}
