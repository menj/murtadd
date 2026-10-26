<?php
/**
 * A private login address (Appearance → Theme Options → Security).
 *
 * The login screen moves from wp-login.php to an address of the owner's
 * choosing; wp-login.php, wp-register.php and wp-admin then answer visitors
 * who are not logged in with the site's page not found. A native port of the
 * WPS Hide Login request handling, as paulus/inc/hide-login.php has it.
 *
 * OFF by default: nothing changes until an address is saved on the tab.
 *
 * Recovery. If the address is lost, add this line to wp-config.php:
 *
 *     define( 'MURTADD_HIDE_LOGIN', false );
 *
 * The module then stands down entirely and wp-login.php works again; log in,
 * read or clear the address on the Security tab, and remove the line. The
 * module also stands down while the WPS Hide Login plugin is active, so the
 * two never fight over the same request.
 *
 * A theme loads after plugins_loaded, where the plugin does its first step.
 * That step only recognises the request and adjusts $pagenow and the request
 * address, which WordPress does not read again until later, so here it runs
 * on after_setup_theme, the earliest point a theme has.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Addresses a login slug may never take.
 *
 * @return string[]
 */
function murtadd_login_reserved() {
	// The pages murtadd_required_pages() creates are reserved too: setup can
	// create one after a slug is saved, and the login screen would shadow it.
	return array( 'wp-login', 'wp-login-php', 'wp-admin', 'admin', 'login', 'dashboard', 'wp-content', 'wp-includes', 'wp-json', 'feed', 'doubts', 'rebuttals', 'fatwa', 'letters', 'blog', '404', 'home', 'start-here', 'irtidad', 'about', 'faq', 'contact', 'privacy' );
}

/**
 * Whether a slug is acceptable as the private login address: lower-case
 * letters, digits and hyphens, 4 to 50 of them, not reserved, and not
 * containing "wp-login".
 *
 * @param string $slug Candidate, already sanitised.
 * @return bool
 */
function murtadd_login_slug_valid( $slug ) {
	$slug = (string) $slug;
	return (bool) preg_match( '/^[a-z0-9](?:[a-z0-9-]{2,48})[a-z0-9]$/', $slug )
		&& ! in_array( $slug, murtadd_login_reserved(), true )
		&& false === strpos( $slug, 'wp-login' );
}

/**
 * Whether the module is allowed to run at all.
 *
 * @return bool
 */
function murtadd_hide_login_available() {
	if ( defined( 'MURTADD_HIDE_LOGIN' ) && ! MURTADD_HIDE_LOGIN ) {
		return false;
	}
	return ! ( defined( 'WPS_HIDE_LOGIN_BASENAME' ) || class_exists( 'WPS\\WPS_Hide_Login\\Plugin' ) );
}

/**
 * The private login slug, or '' when the module is off.
 *
 * @return string
 */
function murtadd_login_slug() {
	if ( ! murtadd_hide_login_available() ) {
		return '';
	}
	$slug = (string) murtadd_security( 'login_slug' );
	return murtadd_login_slug_valid( $slug ) ? $slug : '';
}

/**
 * With or without the closing slash, as the permalink structure has it.
 *
 * @param string $url Address.
 * @return string
 */
function murtadd_login_slashed( $url ) {
	return '/' === substr( (string) get_option( 'permalink_structure' ), -1 ) ? trailingslashit( $url ) : untrailingslashit( $url );
}

/**
 * The private login address.
 *
 * @param string|null $scheme Scheme.
 * @return string
 */
function murtadd_login_url( $scheme = null ) {
	$home = home_url( '/', $scheme );
	return get_option( 'permalink_structure' ) ? murtadd_login_slashed( $home . murtadd_login_slug() ) : $home . '?' . murtadd_login_slug();
}

/**
 * Where blocked requests go: the site's page not found.
 *
 * @param string|null $scheme Scheme.
 * @return string
 */
function murtadd_login_blocked_url( $scheme = null ) {
	return murtadd_login_slashed( home_url( '/404', $scheme ) );
}

if ( '' !== murtadd_login_slug() ) {

	/**
	 * Recognise the request: wp-login.php and wp-register.php are disguised so
	 * they resolve to a page not found; the private address becomes the login
	 * screen.
	 */
	add_action(
		'after_setup_theme',
		static function () {
			global $pagenow;
			$uri     = rawurldecode( (string) ( $_SERVER['REQUEST_URI'] ?? '' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$request = wp_parse_url( $uri );
			$path    = isset( $request['path'] ) ? untrailingslashit( $request['path'] ) : '';
			$slug    = murtadd_login_slug();
			if ( ( false !== stripos( $uri, 'wp-login.php' ) || site_url( 'wp-login', 'relative' ) === $path ) && ! is_admin() ) {
				$GLOBALS['murtadd_login_blocked'] = true;
				$_SERVER['REQUEST_URI']           = murtadd_login_slashed( '/' . str_repeat( '-/', 10 ) );
				$pagenow                          = 'index.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			} elseif ( home_url( $slug, 'relative' ) === $path || ( ! get_option( 'permalink_structure' ) && isset( $_GET[ $slug ] ) && empty( $_GET[ $slug ] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$_SERVER['SCRIPT_NAME'] = $slug;
				$pagenow                = 'wp-login.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			} elseif ( ( false !== stripos( $uri, 'wp-register.php' ) || site_url( 'wp-register', 'relative' ) === $path ) && ! is_admin() ) {
				$GLOBALS['murtadd_login_blocked'] = true;
				$_SERVER['REQUEST_URI']           = murtadd_login_slashed( '/' . str_repeat( '-/', 10 ) );
				$pagenow                          = 'index.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			}
		},
		1
	);

	/**
	 * Act on the request.
	 */
	add_action(
		'wp_loaded',
		static function () {
			global $pagenow;
			$request = wp_parse_url( rawurldecode( (string) ( $_SERVER['REQUEST_URI'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$path    = $request['path'] ?? '';
			// A password for a protected post goes through untouched. The posted
			// action, when there is one, must be postpass too, or a POST carrying
			// action=login would reach the login handler at the public address.
			if ( isset( $_GET['action'], $_POST['post_password'] ) && 'postpass' === $_GET['action'] && 'postpass' === wp_unslash( $_POST['action'] ?? 'postpass' ) ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- compared to a literal exactly; sanitize_key() would lowercase "POSTPASS" and let it through to the login handler.
				return;
			}
			$options_path = wp_parse_url( admin_url( 'options.php' ), PHP_URL_PATH );
			if ( is_admin() && ! is_user_logged_in() && ! defined( 'WP_CLI' ) && ! wp_doing_ajax() && ! wp_doing_cron() && 'admin-post.php' !== $pagenow && $path !== $options_path ) {
				wp_safe_redirect( murtadd_login_blocked_url() );
				exit;
			}
			if ( ! is_user_logged_in() && $path === $options_path ) {
				wp_safe_redirect( murtadd_login_blocked_url() );
				exit;
			}
			if ( 'wp-login.php' === $pagenow && '' !== $path && murtadd_login_slashed( $path ) !== $path && get_option( 'permalink_structure' ) ) {
				wp_safe_redirect( murtadd_login_slashed( murtadd_login_url() ) . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . wp_unslash( $_SERVER['QUERY_STRING'] ) : '' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				exit;
			}
			if ( ! empty( $GLOBALS['murtadd_login_blocked'] ) ) {
				$pagenow = 'index.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
				if ( ! defined( 'WP_USE_THEMES' ) ) {
					define( 'WP_USE_THEMES', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- core's own constant, read by template-loader.php.
				}
				wp();
				require_once ABSPATH . WPINC . '/template-loader.php';
				exit;
			}
			if ( 'wp-login.php' === $pagenow ) {
				global $error, $interim_login, $action, $user_login;
				if ( is_user_logged_in() && ! isset( $_REQUEST['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					wp_safe_redirect( admin_url() );
					exit;
				}
				require_once ABSPATH . 'wp-login.php';
				exit;
			}
		}
	);

	/**
	 * Every address WordPress builds for wp-login.php points to the private
	 * address instead: links, redirects, emails.
	 *
	 * @param string      $url    Address.
	 * @param string|null $scheme Scheme.
	 * @return string
	 */
	function murtadd_login_filter_url( $url, $scheme = null ) {
		static $from_login = null;
		if ( null === $from_login ) {
			$from_login = false !== strpos( (string) wp_get_referer(), 'wp-login.php' );
		}
		if ( false !== strpos( $url, 'wp-login.php?action=postpass' ) || false === strpos( $url, 'wp-login.php' ) || $from_login ) {
			return $url;
		}
		if ( is_ssl() ) {
			$scheme = 'https';
		}
		$parts = explode( '?', $url );
		if ( isset( $parts[1] ) ) {
			parse_str( $parts[1], $args );
			if ( isset( $args['login'] ) ) {
				$args['login'] = rawurlencode( $args['login'] );
			}
			return add_query_arg( $args, murtadd_login_url( $scheme ) );
		}
		return murtadd_login_url( $scheme );
	}
	add_filter(
		'site_url',
		static function ( $url, $path, $scheme ) {
			return murtadd_login_filter_url( $url, $scheme );
		},
		10,
		3
	);
	add_filter(
		'network_site_url',
		static function ( $url, $path, $scheme ) {
			return murtadd_login_filter_url( $url, $scheme );
		},
		10,
		3
	);
	add_filter(
		'wp_redirect',
		static function ( $location ) {
			return murtadd_login_filter_url( $location );
		}
	);
	// A page not found never reveals the address.
	add_filter(
		'login_url',
		static function ( $login_url ) {
			return is_404() ? '#' : $login_url;
		}
	);
	// Personal-data confirmation emails carry a working address.
	add_filter(
		'user_request_action_email_content',
		static function ( $text, $data ) {
			return str_replace( '###CONFIRM_URL###', esc_url_raw( str_replace( murtadd_login_slug() . '/', 'wp-login.php', $data['confirm_url'] ) ), $text );
		},
		999,
		2
	);
	add_action(
		'template_redirect',
		static function () {
			if ( isset( $_GET['action'], $_GET['request_id'], $_GET['confirm_key'] ) && 'confirmaction' === $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				$id  = (int) $_GET['request_id']; // phpcs:ignore WordPress.Security.NonceVerification
				$key = sanitize_text_field( wp_unslash( $_GET['confirm_key'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				if ( ! is_wp_error( wp_validate_user_request_key( $id, $key ) ) ) {
					wp_safe_redirect( add_query_arg( array( 'action' => 'confirmaction', 'request_id' => $id, 'confirm_key' => $key ), murtadd_login_url() ) );
					exit;
				}
			}
		}
	);
}
