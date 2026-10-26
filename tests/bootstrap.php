<?php
/**
 * PHPUnit bootstrap.
 *
 * Lightweight WordPress stubs, so the modules under inc/ can be loaded and
 * tested in isolation without a WordPress installation. Modelled on Book-WP's
 * test bootstrap.
 *
 * This exists because of what actually went wrong on this theme. A function
 * redeclaration took the whole site down with a fatal, and it passed `php -l`
 * on every file individually, because each file was valid alone. A doubt
 * pointed at a rebuttal that did not exist, and an entire doubt category
 * shipped empty, so one of the four doors on the homepage read "0 responses".
 * None of those are syntax errors. All of them are testable.
 *
 * @package Murtadd\Tests
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'DAY_IN_SECONDS', 86400 );

$GLOBALS['murtadd_hooks']   = array();
$GLOBALS['murtadd_options'] = array();

function add_action( $hook, $cb, $priority = 10, $args = 1 ) {
	$GLOBALS['murtadd_hooks'][] = array( $hook, $cb );
}
function add_filter( $hook, $cb, $priority = 10, $args = 1 ) {
	$GLOBALS['murtadd_hooks'][] = array( $hook, $cb );
}
function apply_filters( $hook, $value ) {
	return $value;
}
function get_stylesheet_directory() {
	return dirname( __DIR__ );
}
function get_stylesheet_directory_uri() {
	return 'https://murtadd.org/wp-content/themes/murtadd';
}
function get_template_directory() {
	return dirname( __DIR__ );
}
function home_url( $path = '/' ) {
	return 'https://murtadd.org' . $path;
}
function get_option( $name, $default = false ) {
	return $GLOBALS['murtadd_options'][ $name ] ?? $default;
}
function update_option( $name, $value ) {
	$GLOBALS['murtadd_options'][ $name ] = $value;
	return true;
}
function __( $s, $d = null ) {
	return $s;
}
function _x( $s, $c, $d = null ) {
	return $s;
}
function _n( $s, $p, $n, $d = null ) {
	return 1 === $n ? $s : $p;
}
function esc_html__( $s, $d = null ) {
	return $s;
}
function esc_attr__( $s, $d = null ) {
	return $s;
}
function esc_html( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' );
}
function esc_attr( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' );
}
function esc_url( $s ) {
	return $s;
}
function esc_url_raw( $s ) {
	return $s;
}
function esc_sql( $s ) {
	return $s;
}
function wp_strip_all_tags( $s ) {
	return trim( wp_kses_no_tags( $s ) );
}
function wp_kses_no_tags( $s ) {
	return strip_tags( (string) $s );
}
function sanitize_text_field( $s ) {
	return trim( strip_tags( (string) $s ) );
}
function sanitize_title( $s ) {
	return strtolower( preg_replace( '/[^a-z0-9]+/i', '-', (string) $s ) );
}
function wp_json_encode( $d, $f = 0 ) {
	return json_encode( $d, $f );
}
function is_admin() {
	return false;
}
function load_theme_textdomain() {}
function add_theme_support() {}
function register_nav_menus() {}
function add_image_size() {}
function get_the_ID() {
	return 0;
}
function mb_strtoupper_shim( $s ) {
	return function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $s ) : strtoupper( $s );
}


// ---- Fake post store, so inc/seed-content.php can run under test. ----
$GLOBALS['murtadd_posts'] = array(); // type => slug => post object
$GLOBALS['murtadd_next_id'] = 1;

function murtadd_test_reset_posts() {
	$GLOBALS['murtadd_posts']   = array();
	$GLOBALS['murtadd_options'] = array();
	$GLOBALS['murtadd_next_id'] = 1;
}
function get_page_by_path( $slug, $output = OBJECT, $type = 'page' ) {
	return $GLOBALS['murtadd_posts'][ $type ][ $slug ] ?? null;
}
function wp_insert_post( $args ) {
	$id = $GLOBALS['murtadd_next_id']++;
	$post = (object) array_merge( array( 'ID' => $id ), $args );
	$GLOBALS['murtadd_posts'][ $args['post_type'] ][ $args['post_name'] ] = $post;
	return $id;
}
function murtadd_test_delete_post( $type, $slug ) {
	unset( $GLOBALS['murtadd_posts'][ $type ][ $slug ] );
}
function is_wp_error( $x ) {
	return false;
}
function update_post_meta( $id, $k, $v ) {
	return true;
}
function wp_set_object_terms( $id, $terms, $tax ) {
	return true;
}
function term_exists( $slug, $tax = '' ) {
	return true; // Terms are not under test here.
}
function wp_insert_term( $name, $tax, $args = array() ) {
	return array( 'term_id' => 1 );
}
if ( ! defined( 'OBJECT' ) ) {
	define( 'OBJECT', 'OBJECT' );
}

require_once __DIR__ . '/../inc/seed-content.php';


// ---- Stubs for the docs viewer. ----
function wp_kses( $html, $allowed ) {
	// Not WordPress's kses, but enough to prove the viewer's contract under
	// test: strip script/style blocks and event handlers, keep the rest.
	$html = preg_replace( '#<(script|style)\b.*?</\1>#is', '', (string) $html );
	$html = preg_replace( '#\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html );
	$html = preg_replace( '#(href|src)\s*=\s*("|\')\s*javascript:[^"\']*\2#i', '$1="#"', $html );
	return $html;
}
function add_query_arg( $args, $url = '' ) {
	return $url . '?' . http_build_query( $args );
}
function admin_url( $path = '' ) {
	return 'https://murtadd.org/wp-admin/' . $path;
}
function esc_attr_e( $s, $d = null ) {
	echo esc_attr( $s );
}
function esc_html_e( $s, $d = null ) {
	echo esc_html( $s );
}
if ( ! defined( 'MURTADD_VERSION' ) ) {
	define( 'MURTADD_VERSION', trim( (string) preg_replace( '/.*Version:\s*([0-9.]+).*/s', '$1', file_get_contents( dirname( __DIR__ ) . '/style.css' ) ) ) );
}

require_once __DIR__ . '/../inc/docs.php';


// ---- Legacy redirect map (data-level tests only; stubs above suffice). ----
require_once __DIR__ . '/../inc/legacy-redirects.php';

require_once __DIR__ . '/../inc/social.php';
function get_bloginfo( $k = '' ) { return 'Murtadd'; }
function get_site_icon_url( $s = 512 ) { return ''; }

require_once __DIR__ . '/../inc/schema.php';

require_once __DIR__ . '/../inc/seed-data.php';
require_once __DIR__ . '/../inc/analytics.php';


// ---- Contextual links. The engine is pure; these stubs serve its guards. ----
$GLOBALS['murtadd_test_terms'] = array(); // post ID => doubt category slugs.
$GLOBALS['murtadd_test_types'] = array(); // post ID => post type.
$GLOBALS['murtadd_test_slugs'] = array(); // post ID => post_name.
function wp_parse_url( $url, $component = -1 ) {
	return parse_url( $url, $component );
}
function absint( $n ) {
	return abs( (int) $n );
}
function has_term( $terms, $taxonomy = '', $post_id = 0 ) {
	return (bool) array_intersect( (array) $terms, $GLOBALS['murtadd_test_terms'][ $post_id ] ?? array() );
}
function get_post_type( $post_id = 0 ) {
	return $GLOBALS['murtadd_test_types'][ $post_id ] ?? 'post';
}
function get_post_field( $field, $post_id = 0 ) {
	return 'post_name' === $field ? ( $GLOBALS['murtadd_test_slugs'][ $post_id ] ?? '' ) : '';
}
function get_post( $post_id = 0 ) {
	return (object) array( 'ID' => $post_id );
}

require_once __DIR__ . '/../inc/template-tags.php';
require_once __DIR__ . '/../inc/contextual-links.php';


// ---- Security: hardening and the private login address. ----
$GLOBALS['murtadd_test_caps']       = array(); // Capabilities the current user has.
$GLOBALS['murtadd_settings_errors'] = array(); // Codes passed to add_settings_error().
function current_user_can( $cap ) {
	return in_array( $cap, $GLOBALS['murtadd_test_caps'], true );
}
function wp_doing_ajax() {
	return false;
}
function remove_action( $hook, $cb, $priority = 10 ) {
	return true;
}
function trailingslashit( $s ) {
	return rtrim( (string) $s, '/\\' ) . '/';
}
function untrailingslashit( $s ) {
	return rtrim( (string) $s, '/\\' );
}
function add_settings_error( $setting, $code, $message, $type = 'error' ) {
	$GLOBALS['murtadd_settings_errors'][] = $code;
}
function sanitize_textarea_field( $s ) {
	return trim( strip_tags( (string) $s ) );
}

require_once __DIR__ . '/../inc/hardening.php';
require_once __DIR__ . '/../inc/hide-login.php';
require_once __DIR__ . '/../inc/settings-page.php';
