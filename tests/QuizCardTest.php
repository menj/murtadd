<?php
/**
 * The "Not sure where to start?" card and its quiz address.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class QuizCardTest extends TestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['murtadd_options']['murtadd_cross_links'] );
	}

	private function set( array $o ) {
		$GLOBALS['murtadd_options']['murtadd_cross_links'] = $o;
	}

	private function render() {
		ob_start();
		include dirname( __DIR__ ) . '/template-parts/cards/card-quiz.php';
		return ob_get_clean();
	}

	public function test_url_is_base_plus_quiz_and_from_only() {
		$this->set( array( 'ce_base_url' => 'https://compelling-evidence.com' ) );
		$this->assertSame( 'https://compelling-evidence.com/quiz/?from=murtadd', murtadd_quiz_url() );
		$this->set( array( 'ce_base_url' => 'https://ce.test/' ) );
		$this->assertSame( 'https://ce.test/quiz/?from=murtadd', murtadd_quiz_url() );
		$this->set( array( 'ce_base_url' => 'https://ce.test/?utm=x#top' ) );
		$this->assertSame( 'https://ce.test/quiz/?from=murtadd', murtadd_quiz_url() );
	}

	public function test_never_saved_option_uses_the_default_address() {
		$this->set( array() );
		$this->assertSame( 'https://compelling-evidence.com/quiz/?from=murtadd', murtadd_quiz_url() );
	}

	public function test_off_switch_and_cleared_base_hide_the_card() {
		$this->set( array( 'ce_base_url' => 'https://ce.test', 'show_quiz_card' => '0' ) );
		$this->assertSame( '', murtadd_quiz_url() );
		$this->assertSame( '', trim( $this->render() ) );
		$this->set( array( 'ce_base_url' => '' ) );
		$this->assertSame( '', murtadd_quiz_url() );
		$this->set( array( 'ce_base_url' => 'https://ce.test', 'show_quiz_card' => '1' ) );
		$this->assertNotSame( '', murtadd_quiz_url() );
	}

	public function test_card_renders_same_tab_link_with_label() {
		$this->set( array( 'ce_base_url' => 'https://ce.test' ) );
		$html = $this->render();
		$this->assertStringContainsString( 'href="https://ce.test/quiz/?from=murtadd"', $html );
		$this->assertStringContainsString( 'Not sure where to start?', $html );
		$this->assertStringContainsString( 'Find your starting point', $html );
		$this->assertStringContainsString( 'aria-label="', $html );
		$this->assertStringNotContainsString( 'target=', $html );
		$this->assertStringNotContainsString( 'rel=', $html );
		$this->assertStringNotContainsString( '<style', $html );
	}

	public function test_both_templates_include_the_card() {
		foreach ( array( 'front-page.php', 'templates/page-start-here.php' ) as $tpl ) {
			$this->assertStringContainsString( "get_template_part( 'template-parts/cards/card', 'quiz' )", file_get_contents( dirname( __DIR__ ) . '/' . $tpl ), $tpl );
		}
	}

	public function test_switch_survives_sanitising() {
		if ( ! function_exists( 'murtadd_sanitize_cross_links' ) ) {
			$this->markTestSkipped( 'Settings page not loaded in the test bootstrap.' );
		}
		$this->assertSame( '0', murtadd_sanitize_cross_links( array( 'ce_base_url' => 'https://x.test' ) )['show_quiz_card'] );
		$this->assertSame( '1', murtadd_sanitize_cross_links( array( 'show_quiz_card' => '1' ) )['show_quiz_card'] );
	}
}
