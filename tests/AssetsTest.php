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

	/** Theme Options follows the admin design system: cards, toggles, sliders, selector cards, colour grid, ARIA tabs. */
	public function test_theme_options_uses_the_design_system() {
		$root = dirname( __DIR__ );
		$php  = (string) file_get_contents( $root . '/inc/settings-page.php' );
		$this->assertStringNotContainsString( 'class="form-table', $php );
		foreach ( array( 'role="switch"', 'type="range"', 'role="tablist"', 'role="tabpanel"', 'murtadd-choice__input', 'murtadd-color-field', '<output' ) as $needle ) {
			$this->assertStringContainsString( $needle, $php, $needle );
		}
		foreach ( array( 'admin.css', 'admin.min.css' ) as $file ) {
			$css = (string) file_get_contents( $root . '/assets/css/' . $file );
			foreach ( array( 'murtadd-card', 'murtadd-field', 'murtadd-toggle__track', 'murtadd-range__value', 'murtadd-choice__body', 'murtadd-color-grid', 'murtadd-admin-tab' ) as $class ) {
				$this->assertStringContainsString( '.' . $class, $css, $class . ' missing from ' . $file );
			}
			$this->assertStringContainsString( '782px', $css, $file );
		}
		foreach ( array( 'settings-tabs.js', 'settings-tabs.min.js' ) as $file ) {
			$js = (string) file_get_contents( $root . '/assets/js/' . $file );
			$this->assertStringContainsString( 'ArrowRight', $js, $file );
			$this->assertStringContainsString( 'wpColorPicker', $js, $file );
		}
	}

	/** The options screen's assets load on its hook suffix only. */
	public function test_options_assets_follow_the_hook_suffix() {
		$php = (string) file_get_contents( dirname( __DIR__ ) . '/functions.php' );
		$this->assertStringContainsString( 'murtadd_settings_hook() === $hook', $php );
		$this->assertStringNotContainsString( "'appearance_page_murtadd' === \$hook", $php );
	}

	/** JSON-LD cannot be closed early by "</script>" in a title. */
	public function test_jsonld_escapes_tags() {
		ob_start();
		murtadd_print_jsonld( array( 'name' => '</script><script>alert(1)</script>' ) );
		$out = (string) ob_get_clean();
		$this->assertSame( 1, substr_count( $out, '</script>' ) );
	}

	/** Settings, Reading "latest posts" wins over front-page.php. */
	public function test_front_page_respects_reading() {
		$php = (string) file_get_contents( dirname( __DIR__ ) . '/functions.php' );
		$this->assertMatchesRegularExpression( "/add_filter\\(\\s*'frontpage_template',\\s*'murtadd_front_page_template'/", $php );
		$this->assertStringContainsString( "'posts' === get_option( 'show_on_front' ) ? '' : \$template", $php );
	}

	/** Only the active panel renders on load; the rest load once, over admin-ajax, behind a nonce. */
	public function test_inactive_panels_load_lazily() {
		$php = (string) file_get_contents( dirname( __DIR__ ) . '/inc/settings-page.php' );
		$this->assertStringContainsString( 'data-murtadd-lazy', $php );
		$this->assertStringContainsString( "add_action( 'wp_ajax_murtadd_settings_panel', 'murtadd_ajax_settings_panel' )", $php );
		$this->assertStringContainsString( "check_ajax_referer( 'murtadd_settings_panel', 'nonce' )", $php );
		foreach ( array( 'settings-tabs.js', 'settings-tabs.min.js' ) as $file ) {
			$js = (string) file_get_contents( dirname( __DIR__ ) . '/assets/js/' . $file );
			$this->assertStringContainsString( 'murtadd_settings_panel', $js, $file );
			$this->assertDoesNotMatchRegularExpression( '/\\[\\s*[\'"]doc[\'"]\\s*,\\s*[\'"]settings-updated/', $js, $file . ' must not drop ?doc=.' );
		}
	}

	/** The footer sits outside <main>, in one contentinfo, on every view. */
	public function test_footer_is_outside_main() {
		$root   = dirname( __DIR__ );
		$header = (string) file_get_contents( $root . '/header.php' );
		$footer = (string) file_get_contents( $root . '/footer.php' );
		$front  = (string) file_get_contents( $root . '/front-page.php' );
		$this->assertStringNotContainsString( '</main>', $footer );
		$this->assertStringContainsString( 'murtadd_open_site_footer()', $footer );
		$this->assertLessThan( strpos( $front, 'murtadd-footer-columns' ), strpos( $front, 'murtadd_open_site_footer()' ) );
		$this->assertStringNotContainsString( '<footer', $front );
		$this->assertStringContainsString( '<main id="murtadd-content"', $header );
		ob_start();
		murtadd_open_site_footer();
		murtadd_open_site_footer();
		$out = (string) ob_get_clean();
		$this->assertSame( 1, substr_count( $out, '</main>' ) );
		$this->assertSame( 1, substr_count( $out, '<footer class="murtadd-site-footer">' ) );
		$this->assertStringContainsString( '! ( is_front_page() && ! is_home() )', $footer );
	}

	/** Every complementary landmark has its own name, and the progress bar sits inside <main> (axe: landmark-unique, region). */
	public function test_landmarks_are_named_and_bar_sits_in_main() {
		$root = dirname( __DIR__ );
		$this->assertMatchesRegularExpression( '/<aside class="murtadd-sidebar" aria-label="/', (string) file_get_contents( $root . '/sidebar.php' ) );
		$this->assertStringContainsString( '<aside class="murtadd-quiz-card" aria-labelledby="murtadd-quiz-card-title">', (string) file_get_contents( $root . '/template-parts/cards/card-quiz.php' ) );
		$this->assertStringContainsString( 'id="murtadd-quiz-card-title"', (string) file_get_contents( $root . '/template-parts/cards/card-quiz.php' ) );
		foreach ( array( 'progress.js', 'progress.min.js' ) as $file ) {
			$js = (string) file_get_contents( $root . '/assets/js/' . $file );
			$this->assertStringContainsString( "host.insertBefore( bar, host.firstChild )", $js, $file );
			$this->assertStringNotContainsString( 'document.body.appendChild( bar )', $js, $file );
		}
		foreach ( array( 'progress.css', 'progress.min.css' ) as $file ) {
			$this->assertMatchesRegularExpression( '/\.murtadd-content\s*>\s*\.murtadd-progress\s*\{[^}]*max-width:\s*none/', (string) file_get_contents( $root . '/assets/css/' . $file ), $file );
		}
	}
}
