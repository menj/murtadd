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

	/** The long-form script's visible words come from PHP, so they are translatable, minified twin included. */
	public function test_longform_strings_are_localised() {
		$php = (string) file_get_contents( dirname( __DIR__ ) . '/functions.php' );
		$this->assertMatchesRegularExpression( "/wp_localize_script\\(\\s*'murtadd-longform',\\s*'murtaddLongform'/", $php );
		foreach ( array( 'progress', 'toc', 'top' ) as $key ) {
			$this->assertStringContainsString( "'" . $key . "'", $php );
		}
		foreach ( array( 'longform.js', 'longform.min.js' ) as $file ) {
			$js = (string) file_get_contents( dirname( __DIR__ ) . '/assets/js/' . $file );
			$this->assertStringContainsString( 'window.murtaddLongform', $js, $file );
			$this->assertDoesNotMatchRegularExpression( "/(setAttribute\\(\\s*'aria-label',\\s*'|textContent\\s*=\\s*')/", $js, $file . ' sets a hard-coded label.' );
		}
		$pot = (string) file_get_contents( dirname( __DIR__ ) . '/languages/murtadd.pot' );
		foreach ( array( 'Reading progress', 'On this page', 'Back to top' ) as $msg ) {
			$this->assertStringContainsString( 'msgid "' . $msg . '"', $pot );
		}
	}

	/** The editor canvas loads the front end's tokens and the editor type, both shipped minified. */
	public function test_editor_styles_are_registered() {
		$php = (string) file_get_contents( dirname( __DIR__ ) . '/functions.php' );
		$this->assertStringContainsString( "add_theme_support( 'editor-styles' )", $php );
		$this->assertMatchesRegularExpression( "#add_editor_style\\(\\s*array\\(\\s*'assets/css/tokens' \\. MURTADD_ASSET_SUFFIX \\. '\\.css',\\s*'assets/css/editor' \\. MURTADD_ASSET_SUFFIX \\. '\\.css'#", $php );
		foreach ( array( 'editor.css', 'editor.min.css' ) as $file ) {
			$css = (string) file_get_contents( dirname( __DIR__ ) . '/assets/css/' . $file );
			$this->assertStringContainsString( 'var(--murtadd-font-serif)', $css, $file );
			$this->assertStringNotContainsString( 'murtadd-font-quran', $css, $file . ' must not set the Qur\'anic face on unfitted verse text.' );
		}
	}
}
