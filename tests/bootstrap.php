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
	$GLOBALS['murtadd_hooks'][] = array( $hook, $cb, $priority );
}
function add_filter( $hook, $cb, $priority = 10, $args = 1 ) {
	$GLOBALS['murtadd_hooks'][] = array( $hook, $cb, $priority );
}
/** The priority a named callback was hooked at, or null. */
function murtadd_test_hook_priority( $hook, $cb ) {
	foreach ( $GLOBALS['murtadd_hooks'] as $h ) {
		if ( $h[0] === $hook && $h[1] === $cb ) {
			return $h[2];
		}
	}
	return null;
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
	$locale = $GLOBALS['murtadd_locale'] ?? 'en_US';
	return $GLOBALS['murtadd_l10n'][ $locale ][ $s ] ?? $s;
}
function get_available_languages() {
	return array_keys( $GLOBALS['murtadd_l10n'] ?? array() );
}
function switch_to_locale( $locale ) {
	$GLOBALS['murtadd_locale_stack'][] = $GLOBALS['murtadd_locale'] ?? 'en_US';
	$GLOBALS['murtadd_locale']         = $locale;
	return true;
}
function restore_previous_locale() {
	$GLOBALS['murtadd_locale'] = array_pop( $GLOBALS['murtadd_locale_stack'] ) ?? 'en_US';
	return $GLOBALS['murtadd_locale'];
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
function wp_check_invalid_utf8( $s ) {
	// As core without $strip: invalid UTF-8 gives an empty string.
	$s = (string) $s;
	return ( '' === $s || preg_match( '//u', $s ) ) ? $s : '';
}
function sanitize_text_field( $s ) {
	// As core's _sanitize_text_fields(): tags out, whitespace collapsed, and
	// every percent-encoded octet removed.
	$s = trim( (string) preg_replace( '/[\r\n\t ]+/', ' ', strip_tags( (string) $s ) ) );
	while ( preg_match( '/%[a-f0-9]{2}/i', $s, $m ) ) {
		$s = str_replace( $m[0], '', $s );
	}
	return trim( (string) preg_replace( '/ +/', ' ', $s ) );
}
function sanitize_title( $s ) {
	return strtolower( preg_replace( '/[^a-z0-9]+/i', '-', (string) $s ) );
}
function wp_json_encode( $d, $f = 0 ) {
	return json_encode( $d, $f );
}
function is_admin() {
	return ! empty( $GLOBALS['murtadd_test_is_admin'] );
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
require_once __DIR__ . '/../inc/gone.php';

require_once __DIR__ . '/../inc/social.php';
function get_bloginfo( $k = '' ) { return 'Murtadd'; }
function get_site_icon_url( $s = 512 ) { return $GLOBALS['murtadd_test_site_icon'] ?? ''; }

require_once __DIR__ . '/../inc/schema.php';
require_once __DIR__ . '/../inc/rank-math.php';

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
	// The fixtures are doubt categories; any other taxonomy holds none of them.
	if ( 'murtadd_doubt_category' !== $taxonomy ) {
		return false;
	}
	return (bool) array_intersect( (array) $terms, $GLOBALS['murtadd_test_terms'][ $post_id ] ?? array() );
}
function get_post_type( $post_id = 0 ) {
	return $GLOBALS['murtadd_test_types'][ $post_id ] ?? 'post';
}
function get_post_field( $field, $post_id = 0 ) {
	if ( isset( $GLOBALS['murtadd_test_fields'][ $post_id ][ $field ] ) ) {
		return $GLOBALS['murtadd_test_fields'][ $post_id ][ $field ];
	}
	return 'post_name' === $field ? ( $GLOBALS['murtadd_test_slugs'][ $post_id ] ?? '' ) : '';
}
function get_post( $post_id = 0 ) {
	return (object) array( 'ID' => $post_id );
}

require_once __DIR__ . '/../inc/template-tags.php';
require_once __DIR__ . '/../inc/contextual-links.php';
require_once __DIR__ . '/../inc/quran-text.php';

// The request a page render sees: a single entry's own view unless a test says otherwise.
function murtadd_test_request( $key, $default ) {
	return $GLOBALS['murtadd_test_request'][ $key ] ?? $default;
}
function is_feed() {
	return murtadd_test_request( 'feed', false );
}
function is_singular( $types = '' ) {
	return murtadd_test_request( 'singular', true );
}
function is_main_query() {
	return murtadd_test_request( 'main', true );
}
function is_front_page() {
	return murtadd_test_request( 'front', false );
}
function doing_filter( $hook = null ) {
	return murtadd_test_request( 'excerpt', false ) && 'get_the_excerpt' === $hook;
}
function get_queried_object_id() {
	return murtadd_test_request( 'queried', 0 );
}
function get_post_meta( $post_id, $key = '', $single = false ) {
	return $GLOBALS['murtadd_test_meta'][ $post_id ][ $key ] ?? '';
}
function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false ) {
	$GLOBALS['murtadd_test_enqueued'][] = 'style:' . $handle;
}
function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $footer = false ) {
	$GLOBALS['murtadd_test_enqueued'][] = 'script:' . $handle;
}
require_once __DIR__ . '/../inc/glossary.php';


// ---- Security: hardening and the private login address. ----
$GLOBALS['murtadd_test_caps']       = array(); // Capabilities the current user has.
$GLOBALS['murtadd_settings_errors'] = array(); // Codes passed to add_settings_error().
function current_user_can( $cap ) {
	return in_array( $cap, $GLOBALS['murtadd_test_caps'], true );
}
$GLOBALS['murtadd_test_user'] = 0; // ID of the logged-in user; 0 is a visitor.
function is_user_logged_in() {
	return ! empty( $GLOBALS['murtadd_test_user'] );
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


// ---- Upkeep: maintenance mode and the error-page drop-ins. ----
define( 'WP_CONTENT_DIR', sys_get_temp_dir() . '/murtadd-test-content-' . getmypid() );
if ( ! defined( 'MURTADD_ASSET_SUFFIX' ) ) {
	define( 'MURTADD_ASSET_SUFFIX', '.min' );
}
function content_url( $path = '' ) {
	return 'https://murtadd.org/wp-content' . $path;
}
function wp_specialchars_decode( $s, $q = ENT_NOQUOTES ) {
	return htmlspecialchars_decode( (string) $s, $q );
}
function is_multisite() {
	return false;
}
function is_main_site() {
	return true;
}
function delete_option( $name ) {
	unset( $GLOBALS['murtadd_options'][ $name ] );
	return true;
}

require_once __DIR__ . '/../inc/maintenance.php';


// ---- The login screen. ----
$GLOBALS['murtadd_test_theme_mods'] = array();
function get_theme_mod( $name, $default = false ) {
	return $GLOBALS['murtadd_test_theme_mods'][ $name ] ?? $default;
}
function wp_get_attachment_image_url( $id, $size = 'thumbnail' ) {
	return $id ? 'https://murtadd.org/wp-content/uploads/logo-' . (int) $id . '.png' : false;
}
function sanitize_hex_color( $color ) {
	return preg_match( '/^#([A-Fa-f0-9]{3}){1,2}$/', (string) $color ) ? $color : null;
}
function sanitize_key( $key ) {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
}
function wp_unslash( $value ) {
	return is_string( $value ) ? stripslashes( $value ) : $value;
}

require_once __DIR__ . '/../inc/login.php';
