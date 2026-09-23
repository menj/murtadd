<?php
/**
 * URL routing.
 *
 * The category URLs are explicit rewrite rules sitting ahead of the CPT rules,
 * because a wildcard one segment after /doubts/ would shadow single doubts.
 * That arrangement is easy to break by accident and impossible to see by eye,
 * so it is asserted here.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class RoutingTest extends TestCase {

	/**
	 * Rules in the order WordPress consults them: 'top' rules first.
	 *
	 * @return array<string,string>
	 */
	private function rules(): array {
		return array(
			'cat-paged'   => '#^doubts/(intellectual|scriptural|emotional|identity)/page/([0-9]{1,})/?$#',
			'cat-term'    => '#^doubts/(intellectual|scriptural|emotional|identity)/?$#',
			'cpt-paged'   => '#^doubts/page/([0-9]{1,})/?$#',
			'cpt-archive' => '#^doubts/?$#',
			'cpt-single'  => '#^doubts/([^/]+)(?:/([0-9]+))?/?$#',
		);
	}

	/**
	 * @dataProvider urls
	 */
	public function test_url_routes_to_the_right_rule( string $url, ?string $expected ) {
		$hit = null;
		foreach ( $this->rules() as $name => $pattern ) {
			if ( preg_match( $pattern, $url ) ) {
				$hit = $name;
				break;
			}
		}
		$this->assertSame( $expected, $hit, "URL '{$url}' routed to '" . ( $hit ?? 'nothing' ) . "'" );
	}

	public static function urls(): array {
		return array(
			'archive'            => array( 'doubts/', 'cpt-archive' ),
			'archive pagination' => array( 'doubts/page/2/', 'cpt-paged' ),
			'term: intellectual' => array( 'doubts/intellectual/', 'cat-term' ),
			'term: scriptural'   => array( 'doubts/scriptural/', 'cat-term' ),
			'term: emotional'    => array( 'doubts/emotional/', 'cat-term' ),
			'term: identity'     => array( 'doubts/identity/', 'cat-term' ),
			'term pagination'    => array( 'doubts/identity/page/2/', 'cat-paged' ),
			'single doubt'       => array( 'doubts/married-off-without-consent/', 'cpt-single' ),
			'reserved collision' => array( 'doubts/emotional-2/', 'cpt-single' ),
			'retired /kind/ URL' => array( 'doubts/kind/emotional/', null ),
		);
	}
}
