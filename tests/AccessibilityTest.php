<?php
/**
 * Accessibility guards (WCAG 2.2 AA). Static checks on the templates and scripts
 * that earlier audits fixed, so the defects cannot return unnoticed. The full
 * browser audit is bin/axe-audit.js.
 *
 * @package Murtadd\Tests
 */

// Run from the command line only (phpdbg included): on a web server this file does nothing.
if ( ! in_array( PHP_SAPI, array( 'cli', 'phpdbg' ), true ) ) {
	exit;
}

use PHPUnit\Framework\TestCase;

final class AccessibilityTest extends TestCase {

	private function read( $path ) {
		return (string) file_get_contents( dirname( __DIR__ ) . '/' . $path );
	}

	public function test_search_label_points_at_the_input_id() {
		$src = $this->read( 'searchform.php' );
		$this->assertMatchesRegularExpression( '/<label[^>]*for="<\?php echo esc_attr\( \$murtadd_search_id \)/', $src );
		$this->assertMatchesRegularExpression( '/<input type="search" id="<\?php echo esc_attr\( \$murtadd_search_id \)/', $src );
	}

	public function test_doubts_row_is_not_an_anchor() {
		$src = $this->read( 'archive-murtadd_doubt.php' );
		$this->assertStringNotContainsString( '<a class="murtadd-doubts-row"', $src );
		$this->assertStringContainsString( 'murtadd-doubts-link', $src );
	}

	public function test_filters_never_submit_on_change() {
		// WCAG 3.2.2: a select change must not change context.
		$this->assertStringNotContainsString( 'form.submit', $this->read( 'assets/js/fatwa-filters.js' ) );
		$this->assertStringNotContainsString( 'display = \'none\'', $this->read( 'assets/js/fatwa-filters.js' ) );
	}

	public function test_base_styles_keep_focus_visible_and_clear_the_floating_button() {
		$css = $this->read( 'assets/css/main.css' );
		$this->assertStringContainsString( ':focus-visible { outline:', $css );
		$this->assertStringContainsString( 'scroll-padding-block', $css );
	}

	public function test_docs_grid_cannot_widen_the_page() {
		$this->assertStringContainsString( 'grid-template-columns: minmax(0, 1fr);', $this->read( 'assets/css/docs.css' ) );
		$this->assertMatchesRegularExpression( '/\.murtadd-docs-body \{[^}]*overflow-x: auto/', $this->read( 'assets/css/docs.css' ) );
	}

	public function test_docs_scroll_box_is_reachable_by_keyboard() {
		$this->assertMatchesRegularExpression( '/<article class="murtadd-docs-body" tabindex="0" role="region" aria-label=/', $this->read( 'inc/docs.php' ) );
		$this->assertStringContainsString( '.murtadd-docs-body:focus-visible', $this->read( 'assets/css/docs.css' ) );
		$this->assertStringContainsString( 'murtadd_docs_focusable_pre', $this->read( 'inc/docs.php' ) );
	}

	public function test_code_blocks_get_a_tabindex_unless_they_have_one() {
		$this->assertSame( '<pre tabindex="0"><code>x</code></pre>', murtadd_docs_focusable_pre( '<pre><code>x</code></pre>' ) );
		$this->assertSame( '<pre tabindex="0" class="language-tabindex"><code>x</code></pre>', murtadd_docs_focusable_pre( '<pre class="language-tabindex"><code>x</code></pre>' ) );
		$this->assertSame( '<pre class="a" tabindex="-1">x</pre>', murtadd_docs_focusable_pre( '<pre class="a" tabindex="-1">x</pre>' ) );
		$this->assertSame( '<pre tabindex="0" data-tabindex="1">x</pre>', murtadd_docs_focusable_pre( '<pre data-tabindex="1">x</pre>' ) );
		$this->assertSame( '<p>tabindex</p>', murtadd_docs_focusable_pre( '<p>tabindex</p>' ) );
	}

	public function test_no_outline_is_removed_without_a_replacement() {
		// The skip-link target (<main tabindex="-1">) and the tab panel (tabindex="-1") are the allowed exceptions.
		foreach ( glob( dirname( __DIR__ ) . '/assets/css/*.css' ) as $file ) {
			if ( '.min.css' === substr( $file, -8 ) ) {
				continue;
			}
			preg_match_all( '/([^{}]*:focus(?!-visible)[^{}]*)\{([^}]*outline:\s*(?:none|0)\s*;[^}]*)\}/', (string) file_get_contents( $file ), $m, PREG_SET_ORDER );
			foreach ( $m as $rule ) {
				if ( false !== strpos( $rule[1], '.murtadd-content' ) || false !== strpos( $rule[1], '-admin-panel' ) || preg_match( '/box-shadow|border|background/', $rule[2] ) ) {
					continue;
				}
				$this->fail( basename( $file ) . ': ' . trim( $rule[1] ) . ' removes the focus outline.' );
			}
		}
		$this->addToAssertionCount( 1 );
	}
}
