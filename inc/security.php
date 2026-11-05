<?php
/**
 * Remote-publishing lockdown, spam gate, clean-up tools and 410 Gone for
 * removed spam: Appearance, Theme Options, Security.
 *
 * Ported from the sister theme Abrahamic (2.87.0), written after an attack on
 * abrahamic-religions.com in October 2026: password guessing through
 * xmlrpc.php from 701 rotating addresses under forged "Jetpack by
 * WordPress.com" user agents, a correct password found, then a casino post
 * every 18 to 20 minutes through wp.newPost under the owner's own account.
 *
 * The settings live in the murtadd_security option beside the existing
 * hardening switches (inc/hardening.php), and are read with murtadd_sec_on().
 * The XML-RPC switch is the one hardening.php already had (xmlrpc_off); this
 * file adds the 403 answer, the counters and the .htaccess block to it.
 *
 * Emergency switch: define( 'MURTADD_SECURITY_LOCK', false ); in wp-config.php
 * turns the XML-RPC, application password and REST locks off without touching
 * the options.
 *
 * Starter content (anything inc/seed-content.php, inc/seed-guides.php or
 * inc/activation.php creates, marked with the _murtadd_seeded meta) is never
 * held, listed or trashed. A title or slug alone never earns the exemption.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/security-detector.php';

define( 'MURTADD_SEC_LOG', 'murtadd_security_log' );
define( 'MURTADD_SEC_GONE', 'murtadd_security_gone' );
define( 'MURTADD_SEC_COUNTS', 'murtadd_security_counts' );
define( 'MURTADD_SEC_FAILED', 'murtadd_security_failed' );
define( 'MURTADD_SEC_LOG_MAX', 200 );
define( 'MURTADD_SEC_GONE_MAX', 5000 );
if ( ! defined( 'MURTADD_SEED_META' ) ) {
	define( 'MURTADD_SEED_META', '_murtadd_seeded' );
}

/**
 * Whether a Security switch is on. MURTADD_SECURITY_LOCK set to false in
 * wp-config.php turns the XML-RPC, application password and REST locks off
 * whatever the options say, for an owner locked out of a remote tool.
 *
 * @param string $key Setting key in murtadd_security.
 * @return bool
 */
function murtadd_sec_on( $key ) {
	$locks = array( 'xmlrpc_off', 'app_passwords_off', 'rest_lock' );
	if ( in_array( $key, $locks, true ) && defined( 'MURTADD_SECURITY_LOCK' ) && ! MURTADD_SECURITY_LOCK ) {
		return false;
	}
	return (bool) murtadd_security( $key );
}

/**
 * The Security tab's address.
 *
 * @param array $args Extra query arguments.
 * @return string
 */
function murtadd_sec_url( $args = array() ) {
	return add_query_arg( array_merge( array( 'page' => 'murtadd', 'tab' => 'security' ), $args ), admin_url( 'themes.php' ) );
}

/* -------------------------------------------------------------------------
 * 1. Remote-publishing lockdown
 * ---------------------------------------------------------------------- */

/**
 * With XML-RPC off, xmlrpc.php answers 403 before WordPress parses the call.
 * The filters in inc/hardening.php still empty the method list for any code
 * path that gets past this.
 */
function murtadd_sec_xmlrpc_gate() {
	if ( ! murtadd_sec_on( 'xmlrpc_off' ) || ! ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) ) {
		return;
	}
	// Counted, not logged one by one: the October 2026 attack sent over
	// 17,000 requests in twelve days from 701 addresses.
	murtadd_sec_count( 'xmlrpc' );
	if ( murtadd_sec_fake_jetpack() ) {
		murtadd_sec_count( 'fake-jetpack' );
	}
	status_header( 403 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	echo 'XML-RPC is disabled on this site.';
	exit;
}
add_action( 'after_setup_theme', 'murtadd_sec_xmlrpc_gate', 1 );

/**
 * Lines that deny xmlrpc.php at the web server, for Apache 2.4 and 2.2. The
 * marker name is shared by the sister themes, so switching between them never
 * writes the block twice.
 *
 * @return string[]
 */
function murtadd_sec_htaccess_lines() {
	return array(
		'<Files xmlrpc.php>',
		'<IfModule mod_authz_core.c>',
		'Require all denied',
		'</IfModule>',
		'<IfModule !mod_authz_core.c>',
		'Order Deny,Allow',
		'Deny from all',
		'</IfModule>',
		'</Files>',
	);
}

/** The .htaccess marker of the block that denies WordPress's own files. */
defined( 'MURTADD_SEC_CORE_MARKER' ) || define( 'MURTADD_SEC_CORE_MARKER', 'Murtadd: deny direct core files' );

/** The .htaccess marker of the block that allows only WordPress's own root PHP files. */
defined( 'MURTADD_SEC_ROOT_MARKER' ) || define( 'MURTADD_SEC_ROOT_MARKER', 'Murtadd: only WordPress root PHP' );

/**
 * Lines that deny WordPress's own files that are never meant to be asked for
 * by address: the PHP files in wp-includes (bots ask for blocks/rss.php and
 * the like, which then fail with a fatal error and fill the error log),
 * wp-settings.php, wp-config-sample.php, readme.html (which names the
 * version) and wp-admin/install.php. The editor's TinyMCE loader
 * (wp-includes/js/tinymce/) and ms-files.php, which a network's rewrite
 * rules hand uploads to, stay reachable. The paths are relative to the
 * folder that holds .htaccess, with WordPress's folder in front when it is
 * installed in its own subfolder.
 *
 * @param string $prefix WordPress's folder relative to the .htaccess folder, '' or "wp/".
 * @return string[]
 */
function murtadd_sec_core_lines( $prefix = '' ) {
	$p = str_replace( ' ', '\\ ', preg_quote( $prefix, '#' ) );
	return array(
		'<IfModule mod_rewrite.c>',
		'RewriteEngine On',
		'RewriteRule ^' . $p . 'wp-includes/(?!js/tinymce/|ms-files\\.php$).+\\.php$ - [F,L]',
		'RewriteRule ^' . $p . '(wp-settings|wp-config-sample)\\.php$ - [F,L]',
		'RewriteRule ^' . $p . 'readme\\.html$ - [F,L]',
		'RewriteRule ^' . $p . 'wp-admin/install\\.php$ - [F,L]',
		'</IfModule>',
	);
}

/**
 * The PHP files WordPress keeps at the top of its folder that are asked for
 * by address, without ".php". wp-config.php, wp-settings.php and
 * wp-config-sample.php are not among them: nothing outside WordPress loads
 * them by address. A plugin that puts its own file there by design adds its
 * name with the murtadd_root_php_allow filter.
 *
 * @return string[]
 */
function murtadd_sec_root_php_names() {
	$names = array( 'index', 'wp-activate', 'wp-blog-header', 'wp-comments-post', 'wp-cron', 'wp-links-opml', 'wp-load', 'wp-login', 'wp-mail', 'wp-signup', 'wp-trackback', 'xmlrpc' );
	/**
	 * Root PHP files the web server may run, without ".php".
	 *
	 * @param string[] $names File names.
	 */
	$names = (array) apply_filters( 'murtadd_root_php_allow', $names );
	$names = array_filter( array_map( static fn( $n ) => strtolower( (string) $n ), $names ), static fn( $n ) => (bool) preg_match( '/^[a-z0-9][a-z0-9._-]*$/', $n ) );
	// index.php is always allowed: without it no page of the site would load.
	return array_values( array_unique( array_merge( array( 'index' ), $names ) ) );
}

/**
 * Lines that let the web server run only WordPress's own PHP files at the
 * top of the site: murtadd.org was compromised, and random files such as
 * ox-*.php were left at the root and were live. Any other name answers 403,
 * whether or not the file exists, so a file dropped there later cannot run
 * either. A request for a root PHP file that does not exist still reaches
 * WordPress first (its own block hands it to index.php), so /goods.php and
 * the like get the theme's 410. With WordPress in its own subfolder, its
 * folder gets the same rule.
 *
 * @param string $prefix WordPress's folder relative to the .htaccess folder, '' or "wp/".
 * @return string[]
 */
function murtadd_sec_root_lines( $prefix = '' ) {
	$names = implode( '|', array_map( static fn( $n ) => preg_quote( $n, '#' ), murtadd_sec_root_php_names() ) );
	$lines = array(
		'<IfModule mod_rewrite.c>',
		'RewriteEngine On',
		'RewriteRule ^(?!(?:' . $names . ')\\.php$)[^/]+\\.php$ - [F,L,NC]',
	);
	if ( '' !== (string) $prefix ) {
		$lines[] = 'RewriteRule ^' . str_replace( ' ', '\\ ', preg_quote( $prefix, '#' ) ) . '(?!(?:' . $names . ')\\.php$)[^/]+\\.php$ - [F,L,NC]';
	}
	$lines[] = '</IfModule>';
	return $lines;
}

/**
 * WordPress's folder relative to the folder that holds .htaccess: '' when
 * they are the same, "wp/" when WordPress sits in its own subfolder, null
 * when WordPress is outside it (the blocks are then not written).
 *
 * @param string $home The folder that holds .htaccess.
 * @param string $abs  ABSPATH.
 * @return string|null
 */
function murtadd_sec_core_prefix( $home, $abs ) {
	$home = wp_normalize_path( trailingslashit( $home ) );
	$abs  = wp_normalize_path( trailingslashit( $abs ) );
	return 0 === strpos( $abs, $home ) ? (string) substr( $abs, strlen( $home ) ) : null;
}

/**
 * The blocks this site should carry, by marker. Each is empty when
 * WordPress sits outside the .htaccess folder, or when its constant in
 * wp-config.php turns it off: MURTADD_ALLOW_CORE_FILES for a plugin or host
 * that needs one of the core files by address, MURTADD_ALLOW_ROOT_PHP for a
 * site that runs other PHP files at its root.
 *
 * @param string|null $prefix WordPress's folder relative to the .htaccess folder.
 * @return array<string,string[]>
 */
function murtadd_sec_server_blocks( $prefix ) {
	$core = null !== $prefix && ! ( defined( 'MURTADD_ALLOW_CORE_FILES' ) && MURTADD_ALLOW_CORE_FILES );
	$root = null !== $prefix && ! ( defined( 'MURTADD_ALLOW_ROOT_PHP' ) && MURTADD_ALLOW_ROOT_PHP );
	return array(
		MURTADD_SEC_CORE_MARKER => $core ? murtadd_sec_core_lines( $prefix ) : array(),
		MURTADD_SEC_ROOT_MARKER => $root ? murtadd_sec_root_lines( $prefix ) : array(),
	);
}

/**
 * Root PHP files on disk that the root block refuses: names other than
 * WordPress's own, such as the ox-*.php files the compromise left. Shown on
 * the Security tab, so the owner can delete them by FTP.
 *
 * @param string $dir The folder to read; ABSPATH when empty.
 * @return string[] File names.
 */
function murtadd_sec_unknown_root_php( $dir = '' ) {
	$dir   = '' === $dir ? ABSPATH : trailingslashit( $dir );
	$known = array_merge( murtadd_sec_root_php_names(), array( 'wp-config', 'wp-settings', 'wp-config-sample' ) );
	$found = array();
	foreach ( (array) glob( $dir . '*.[pP][hH][pP]' ) as $file ) {
		$name = basename( (string) $file );
		if ( ! in_array( strtolower( substr( $name, 0, -4 ) ), $known, true ) ) {
			$found[] = $name;
		}
	}
	sort( $found );
	return $found;
}

/**
 * Path of the site's .htaccess, or '' when the server is not Apache or the
 * file cannot be written.
 *
 * @return string
 */
function murtadd_sec_htaccess_file() {
	if ( ! function_exists( 'get_home_path' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	if ( ! function_exists( 'insert_with_markers' ) ) {
		require_once ABSPATH . 'wp-admin/includes/misc.php';
	}
	global $is_apache;
	if ( empty( $is_apache ) ) {
		return '';
	}
	$file = get_home_path() . '.htaccess';
	if ( file_exists( $file ) ? ! wp_is_writable( $file ) : ! wp_is_writable( dirname( $file ) ) ) {
		return '';
	}
	return $file;
}

/**
 * Keep the server-level block in step with the XML-RPC switch: written while
 * it is on, removed when it is off. Runs in the admin at most once an hour, so
 * a lost or overwritten .htaccess is repaired without any action.
 *
 * @param bool $force Skip the hourly throttle.
 */
function murtadd_sec_sync_htaccess( $force = false ) {
	if ( ! $force && get_transient( 'murtadd_sec_htaccess_checked' ) ) {
		return;
	}
	set_transient( 'murtadd_sec_htaccess_checked', 1, HOUR_IN_SECONDS );
	$file = murtadd_sec_htaccess_file();
	if ( '' === $file ) {
		update_option( 'murtadd_security_htaccess', 'unavailable', false );
		return;
	}
	$want    = murtadd_sec_on( 'xmlrpc_off' );
	$current = function_exists( 'extract_from_markers' ) ? extract_from_markers( $file, 'Block xmlrpc.php' ) : array();
	$state   = $want ? 'written' : 'off';
	if ( $want && $current !== murtadd_sec_htaccess_lines() ) {
		// Record "written" only when the block really went in.
		if ( true !== insert_with_markers( $file, 'Block xmlrpc.php', murtadd_sec_htaccess_lines() ) ) {
			$state = 'unavailable';
		}
	} elseif ( ! $want && $current ) {
		insert_with_markers( $file, 'Block xmlrpc.php', array() );
	}
	// The core-file and root PHP blocks are kept while the theme runs,
	// whatever the XML-RPC switch.
	foreach ( murtadd_sec_server_blocks( murtadd_sec_core_prefix( get_home_path(), ABSPATH ) ) as $marker => $lines ) {
		if ( ( function_exists( 'extract_from_markers' ) ? extract_from_markers( $file, $marker ) : array() ) !== $lines ) {
			insert_with_markers( $file, $marker, $lines );
		}
	}
	update_option( 'murtadd_security_htaccess', $state, false );
}
add_action( 'admin_init', 'murtadd_sec_sync_htaccess' );

/**
 * Sync at once, without the throttle.
 */
function murtadd_sec_sync_htaccess_now() {
	murtadd_sec_sync_htaccess( true );
}
add_action( 'after_switch_theme', 'murtadd_sec_sync_htaccess_now' );
add_action( 'update_option_murtadd_security', 'murtadd_sec_sync_htaccess_now' );
add_action( 'add_option_murtadd_security', 'murtadd_sec_sync_htaccess_now' );

/**
 * Take the block out when the site moves to another theme. A sister theme
 * writes the same block back on its first admin load.
 */
function murtadd_sec_remove_htaccess() {
	$file = murtadd_sec_htaccess_file();
	if ( '' !== $file ) {
		insert_with_markers( $file, 'Block xmlrpc.php', array() );
		insert_with_markers( $file, MURTADD_SEC_CORE_MARKER, array() );
		insert_with_markers( $file, MURTADD_SEC_ROOT_MARKER, array() );
	}
	delete_transient( 'murtadd_sec_htaccess_checked' );
}
add_action( 'switch_theme', 'murtadd_sec_remove_htaccess' );

/**
 * Application passwords off.
 *
 * @param bool $available Core value.
 * @return bool
 */
function murtadd_sec_app_passwords( $available ) {
	return murtadd_sec_on( 'app_passwords_off' ) ? false : $available;
}
add_filter( 'wp_is_application_passwords_available', 'murtadd_sec_app_passwords', 99 );

/**
 * Whether this REST request comes from a logged-in browser session with a
 * valid wp_rest nonce. Core sets $wp_rest_auth_cookie when the logged-in
 * cookie validates, and rest_cookie_check_errors() has already logged the
 * user out if the cookie came without a valid nonce.
 *
 * @param WP_REST_Request $request Request.
 * @return bool
 */
function murtadd_sec_cookie_session( $request ) {
	if ( true !== ( $GLOBALS['wp_rest_auth_cookie'] ?? null ) ) {
		return false;
	}
	if ( ! empty( $GLOBALS['wp_rest_application_password_uuid'] ) ) {
		return false;
	}
	$nonce = $request->get_header( 'X-WP-Nonce' );
	if ( ! $nonce && isset( $_REQUEST['_wpnonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- the nonce is checked here.
		$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	return $nonce && false !== wp_verify_nonce( $nonce, 'wp_rest' );
}

/**
 * REST lock: a request that writes (POST, PUT, PATCH, DELETE) as a logged-in
 * user must come from a cookie session with a valid nonce. Basic auth,
 * application passwords and any other header login are refused for writes in
 * every namespace. Reads and anonymous requests are not touched, so public
 * output and anything a visitor sends behave exactly as before. The theme
 * registers no REST route of its own, and its contact form posts to the page
 * itself, not to the REST API. The block editor, Rank Math and the media
 * modal send X-WP-Nonce with cookies and pass.
 *
 * @param mixed           $result  Earlier result.
 * @param WP_REST_Server  $server  Server.
 * @param WP_REST_Request $request Request.
 * @return mixed
 */
function murtadd_sec_rest_guard( $result, $server, $request ) {
	if ( null !== $result || ! murtadd_sec_on( 'rest_lock' ) || ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $result;
	}
	if ( in_array( strtoupper( (string) $request->get_method() ), array( 'GET', 'HEAD', 'OPTIONS' ), true ) ) {
		return $result;
	}
	if ( ! is_user_logged_in() ) {
		return $result; // Anonymous: unchanged. Core decides as it always has.
	}
	$route = (string) $request->get_route();
	/**
	 * Route prefixes that may write with non-cookie authentication, for
	 * example '/jetpack/'. Empty by default.
	 *
	 * @param string[] $prefixes Route prefixes.
	 */
	foreach ( (array) apply_filters( 'murtadd_sec_rest_allow', array() ) as $prefix ) {
		if ( $prefix && 0 === strpos( $route, $prefix ) ) {
			return $result;
		}
	}
	if ( murtadd_sec_cookie_session( $request ) ) {
		return $result;
	}
	murtadd_sec_log(
		array(
			'event' => 'rest-blocked',
			'title' => $request->get_method() . ' ' . $route,
		)
	);
	return new WP_Error( 'murtadd_rest_locked', __( 'Changes through the REST API need a logged-in browser session on this site.', 'murtadd' ), array( 'status' => 403 ) );
}
add_filter( 'rest_pre_dispatch', 'murtadd_sec_rest_guard', 5, 3 );

/* -------------------------------------------------------------------------
 * 2. Signals from the October 2026 attack
 * ---------------------------------------------------------------------- */

/**
 * Add one to today's counter for an event. Each hit is one atomic upsert on a
 * small, non-autoloaded row for today (murtadd_security_tally_{key}_{Ymd}):
 * no option is read, unserialised or rewritten, so a flood of refused
 * XML-RPC requests costs one cheap write each. murtadd_sec_counts() folds
 * past days into the thirty-day counts.
 *
 * @param string $key Event key.
 */
function murtadd_sec_count( $key ) {
	global $wpdb;
	$name = 'murtadd_security_tally_' . sanitize_key( $key ) . '_' . gmdate( 'Ymd' );
	$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, '1', 'off') ON DUPLICATE KEY UPDATE option_value = option_value + 1", $name ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}

/**
 * Counts for display: MURTADD_SEC_COUNTS plus the tally rows. Rows from past
 * days are folded into MURTADD_SEC_COUNTS and deleted; today's stay in place.
 *
 * @return array date => array key => count, newest first.
 */
function murtadd_sec_counts() {
	global $wpdb;
	$counts = get_option( MURTADD_SEC_COUNTS, array() );
	$counts = is_array( $counts ) ? $counts : array();
	$rows   = $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'murtadd\\_security\\_tally\\_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$today  = gmdate( 'Ymd' );
	$live   = array();
	$done   = array();
	foreach ( (array) $rows as $row ) {
		if ( ! preg_match( '/^murtadd_security_tally_([a-z0-9_-]+)_(\d{8})$/', $row->option_name, $m ) ) {
			continue;
		}
		if ( $m[2] === $today ) {
			$live[ $m[1] ] = (int) $row->option_value;
			continue;
		}
		// Delete only the value just read. A hit that lands between the read
		// and the delete changes the value, the delete then matches nothing,
		// and the row is folded whole on a later call.
		$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s", $row->option_name, $row->option_value ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		if ( 1 !== (int) $deleted ) {
			continue;
		}
		$day                     = substr( $m[2], 0, 4 ) . '-' . substr( $m[2], 4, 2 ) . '-' . substr( $m[2], 6, 2 );
		$counts[ $day ][ $m[1] ] = (int) ( $counts[ $day ][ $m[1] ] ?? 0 ) + (int) $row->option_value;
		$done[]                  = $row->option_name;
	}
	// Thirty days including today: keep the 29 days before it.
	$oldest = gmdate( 'Y-m-d', time() - 29 * DAY_IN_SECONDS );
	$day    = gmdate( 'Y-m-d' );
	foreach ( array_keys( $counts ) as $key ) {
		if ( (string) $key < $oldest ) {
			unset( $counts[ $key ] );
			$done[] = true;
		}
	}
	krsort( $counts );
	if ( $done ) {
		update_option( MURTADD_SEC_COUNTS, $counts, false );
	}
	foreach ( $live as $key => $n ) {
		$counts[ $day ][ $key ] = (int) ( $counts[ $day ][ $key ] ?? 0 ) + $n;
	}
	krsort( $counts );
	return array_slice( $counts, 0, 30, true );
}

/**
 * Whether the request claims to come from Jetpack or WordPress.com while the
 * Jetpack plugin is not active. On this site the claim is then forged.
 *
 * @return bool
 */
function murtadd_sec_fake_jetpack() {
	$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	if ( ! preg_match( '/jetpack|wordpress\.com/i', $agent ) ) {
		return false;
	}
	return ! class_exists( 'Jetpack' ) && ! defined( 'JETPACK__VERSION' );
}

/**
 * Whether this request is a remote, non-browser call: XML-RPC, REST with an
 * application password or other header login, or WP-CLI. All three publish
 * from outside the editor.
 *
 * @return bool
 */
function murtadd_sec_is_remote() {
	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
		return true;
	}
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return true;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST && is_user_logged_in() ) {
		return true !== ( $GLOBALS['wp_rest_auth_cookie'] ?? null );
	}
	return false;
}

/**
 * Password logins through XML-RPC are refused even when XML-RPC is switched
 * back on (for Jetpack, say), as long as either lock is on. Jetpack signs its
 * own requests and needs no password, so it keeps working.
 *
 * @param WP_User|WP_Error|null $user Result so far.
 * @return WP_User|WP_Error|null
 */
function murtadd_sec_refuse_remote_password( $user ) {
	if ( ! ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) || ( ! murtadd_sec_on( 'xmlrpc_off' ) && ! murtadd_sec_on( 'rest_lock' ) ) ) {
		return $user;
	}
	if ( $user instanceof WP_User ) {
		murtadd_sec_count( 'remote-password' );
		murtadd_sec_log(
			array(
				'event' => 'remote-password-refused',
				'title' => $user->user_login . ': ' . __( 'the correct password was sent through XML-RPC. Change it now.', 'murtadd' ),
				'user'  => $user->ID,
			)
		);
	}
	return new WP_Error( 'murtadd_remote_password', __( 'Password logins through XML-RPC are not accepted on this site.', 'murtadd' ) );
}
add_filter( 'authenticate', 'murtadd_sec_refuse_remote_password', 100 );

/**
 * Count failed logins per account name over a rolling day. The attack came
 * from a different address every time, so counting by address shows nothing;
 * counting by account name shows which account is under attack.
 *
 * @param string $login Account name or email tried.
 */
function murtadd_sec_failed_login( $login ) {
	murtadd_sec_count( 'failed-login' );
	$login  = sanitize_user( (string) $login, true );
	$failed = get_option( MURTADD_SEC_FAILED, array() );
	$failed = is_array( $failed ) ? $failed : array();
	$now    = time();
	foreach ( $failed as $name => $row ) {
		if ( $now - (int) ( $row['last'] ?? 0 ) > DAY_IN_SECONDS ) {
			unset( $failed[ $name ] );
		}
	}
	if ( '' === $login ) {
		$login = '(empty)';
	}
	if ( ! isset( $failed[ $login ] ) && count( $failed ) >= 50 ) {
		return;
	}
	$row = $failed[ $login ] ?? null;
	if ( ! $row || $now - (int) ( $row['first'] ?? 0 ) > DAY_IN_SECONDS ) {
		// A new window: failures from more than a day ago no longer count.
		$row = array( 'n' => 0, 'first' => $now, 'last' => $now, 'ips' => array() );
	}
	++$row['n'];
	$row['last'] = $now;
	$ip          = murtadd_sec_ip();
	if ( $ip && ! in_array( $ip, $row['ips'], true ) && count( $row['ips'] ) < 1000 ) {
		$row['ips'][] = $ip;
	}
	$failed[ $login ] = $row;
	update_option( MURTADD_SEC_FAILED, $failed, false );
}
add_action( 'wp_login_failed', 'murtadd_sec_failed_login' );

/**
 * Failed-login rows from the last 24 hours only. Rows whose last failure is
 * older are ignored on reading, whether or not a later failure has pruned them.
 *
 * @return array account name => row.
 */
function murtadd_sec_failed_recent() {
	$failed = get_option( MURTADD_SEC_FAILED, array() );
	$failed = is_array( $failed ) ? $failed : array();
	$now    = time();
	return array_filter(
		$failed,
		static function ( $row ) use ( $now ) {
			return is_array( $row ) && $now - (int) ( $row['last'] ?? 0 ) <= DAY_IN_SECONDS && $now - (int) ( $row['first'] ?? 0 ) <= DAY_IN_SECONDS;
		}
	);
}

/**
 * Admin notice when an existing account is being guessed at (ten or more
 * failures in a day) or a correct password was refused on XML-RPC today.
 */
function murtadd_sec_attack_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$hit = array();
	foreach ( murtadd_sec_failed_recent() as $name => $row ) {
		if ( (int) ( $row['n'] ?? 0 ) >= 10 && ( username_exists( $name ) || email_exists( $name ) ) ) {
			$hit[] = sprintf( '%s (%d, %d)', $name, (int) $row['n'], count( (array) $row['ips'] ) );
		}
	}
	$counts = murtadd_sec_counts();
	$today  = (int) ( $counts[ gmdate( 'Y-m-d' ) ]['remote-password'] ?? 0 );
	if ( ! $hit && ! $today ) {
		return;
	}
	echo '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html__( 'Security:', 'murtadd' ) . '</strong> ';
	if ( $today ) {
		esc_html_e( 'Someone sent a correct password through XML-RPC today. The login was refused, but the password is known: change it now.', 'murtadd' );
		echo ' ';
	}
	if ( $hit ) {
		/* translators: %s: account names with failure and address counts. */
		echo esc_html( sprintf( __( 'Passwords are being guessed for these accounts in the last day (failures, addresses): %s.', 'murtadd' ), implode( ', ', $hit ) ) ) . ' ';
	}
	echo '<a href="' . esc_url( murtadd_sec_url() ) . '">' . esc_html__( 'Open the Security tab', 'murtadd' ) . '</a></p></div>';
}
add_action( 'admin_notices', 'murtadd_sec_attack_notice' );

/* -------------------------------------------------------------------------
 * 3. Spam gate
 * ---------------------------------------------------------------------- */

/**
 * How this request reached WordPress.
 *
 * @return string
 */
function murtadd_sec_request_type() {
	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
		return 'XML-RPC';
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return ! empty( $GLOBALS['wp_rest_application_password_uuid'] ) ? 'REST (application password)' : 'REST';
	}
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return 'WP-CLI';
	}
	if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
		return 'cron';
	}
	if ( wp_doing_ajax() ) {
		return 'admin AJAX';
	}
	return is_admin() ? 'admin' : 'front end';
}

/**
 * Visitor IP address, as the web server saw it.
 *
 * @return string
 */
function murtadd_sec_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
}

/**
 * Add a line to the capped log.
 *
 * @param array $entry Fields: event, title, post_id, reasons, user.
 */
function murtadd_sec_log( $entry ) {
	$log   = get_option( MURTADD_SEC_LOG, array() );
	$log   = is_array( $log ) ? $log : array();
	$log[] = array_merge(
		array(
			'time'    => time(),
			'event'   => '',
			'title'   => '',
			'post_id' => 0,
			'reasons' => array(),
			'user'    => function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0,
			'type'    => murtadd_sec_request_type(),
			'ip'      => murtadd_sec_ip(),
		),
		$entry
	);
	update_option( MURTADD_SEC_LOG, array_slice( $log, -MURTADD_SEC_LOG_MAX ), false );
}

/**
 * Post types the gate and the scan look at: posts, pages and the theme's own
 * public types, every one of which can be published through XML-RPC or REST.
 * Menus, attachments, templates and blocks are never touched.
 *
 * @return string[]
 */
function murtadd_sec_types() {
	$types = array( 'post', 'page', 'murtadd_doubt', 'murtadd_rebuttal', 'murtadd_fatwa', 'murtadd_letter', 'murtadd_guide' );
	/**
	 * Post types the spam gate and scan read.
	 *
	 * @param string[] $types Post types.
	 */
	return array_values( array_unique( array_map( 'strval', (array) apply_filters( 'murtadd_sec_types', $types ) ) ) );
}

/**
 * Whether the theme's own seeder is running in this request. The seeders in
 * inc/seed-content.php, inc/seed-guides.php and inc/activation.php set the
 * flag around their inserts; nothing a remote request sends can set it.
 *
 * @return bool
 */
function murtadd_sec_seeding_run() {
	return ! empty( $GLOBALS['murtadd_seeding'] );
}

/**
 * Whether a stored post carries the seed marker meta (MURTADD_SEED_META),
 * which only the seeder writes. A slug alone never counts: a seeded post
 * that was deleted leaves its slug in the record, and a new post at that
 * slug is not starter content.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function murtadd_sec_post_is_seeded( $post_id ) {
	$post_id = (int) $post_id;
	return $post_id > 0 && (bool) get_post_meta( $post_id, MURTADD_SEED_META, true );
}

/**
 * Whether the gate lets an insert or update through as starter content: the
 * theme's own seeding run inserting a post that carries the seed marker, or
 * an existing post that already carries it. A submitted title or slug never
 * exempts anything, from any request.
 *
 * @param array $postarr Raw post data.
 * @return bool
 */
function murtadd_sec_gate_exempt( $postarr ) {
	$postarr = (array) $postarr;
	if ( murtadd_sec_post_is_seeded( $postarr['ID'] ?? 0 ) ) {
		return true;
	}
	return murtadd_sec_seeding_run() && ! empty( $postarr['meta_input'][ MURTADD_SEED_META ] );
}

/**
 * Mark the starter content seeded before 1.45.1 once, so it keeps its
 * exemption from the scan: a post counts when its slug is in the seeder's
 * record (or is an activation page) and its title is the seed entry's.
 */
function murtadd_sec_backfill_seed_marker() {
	if ( get_option( 'murtadd_sec_seed_marked' ) ) {
		return;
	}
	$sets = array(
		'murtadd_doubt'    => function_exists( 'murtadd_seed_doubts' ) ? murtadd_seed_doubts() : array(),
		'murtadd_rebuttal' => function_exists( 'murtadd_seed_rebuttals' ) ? murtadd_seed_rebuttals() : array(),
		'murtadd_guide'    => function_exists( 'murtadd_seed_guides' ) ? murtadd_seed_guides() : array(),
		'page'             => array(),
	);
	if ( function_exists( 'murtadd_required_pages' ) ) {
		foreach ( murtadd_required_pages() as $slug => $page ) {
			$sets['page'][] = array( 'slug' => $slug, 'title' => $page['title'] );
		}
	}
	foreach ( $sets as $type => $items ) {
		foreach ( (array) $items as $item ) {
			$recorded = 'page' === $type || ( function_exists( 'murtadd_slug_was_seeded' ) && murtadd_slug_was_seeded( $type, $item['slug'] ) );
			$post     = $recorded ? get_page_by_path( $item['slug'], OBJECT, $type ) : null;
			if ( $post && $post->post_title === $item['title'] ) {
				update_post_meta( $post->ID, MURTADD_SEED_META, 1 );
			}
		}
	}
	update_option( 'murtadd_sec_seed_marked', 1, false );
}
add_action( 'admin_init', 'murtadd_sec_backfill_seed_marker' );

/**
 * Hold suspected spam: anything going public whose title, slug or content
 * scores as gambling spam, or that is published from outside the editor, is
 * saved as Pending instead.
 *
 * @param array $data    Sanitised post data.
 * @param array $postarr Raw post data.
 * @return array
 */
function murtadd_sec_gate( $data, $postarr ) {
	if ( ! murtadd_sec_on( 'spam_gate' ) || 'trash' === ( $data['post_status'] ?? '' ) || ! in_array( $data['post_type'] ?? '', murtadd_sec_types(), true ) ) {
		return $data;
	}
	// Private posts are never public: they are logged when flagged, not held.
	$public = in_array( $data['post_status'] ?? '', array( 'publish', 'future' ), true );
	$title  = wp_unslash( (string) ( $data['post_title'] ?? '' ) );
	$slug   = (string) ( $data['post_name'] ?? '' );
	if ( murtadd_sec_gate_exempt( $postarr ) ) {
		return $data;
	}
	$verdict = murtadd_spam_score( $title, $slug, wp_unslash( (string) ( $data['post_content'] ?? '' ) ) );
	if ( ! $verdict['spam'] && $public && murtadd_sec_is_remote() ) {
		// The spam arrived through wp.newPost: anything published from
		// outside the editor waits for review, whatever it says.
		$verdict = array( 'spam' => true, 'reasons' => array( __( 'published from outside the editor', 'murtadd' ) . ' (' . murtadd_sec_request_type() . ')' ) );
	}
	if ( ! $verdict['spam'] ) {
		return $data;
	}
	if ( $public ) {
		$data['post_status'] = 'pending';
	}
	$GLOBALS['murtadd_sec_held'] = array(
		'event'   => $public ? 'held' : 'flagged-' . sanitize_key( (string) ( $data['post_status'] ?? 'draft' ) ),
		'title'   => $title,
		'reasons' => $verdict['reasons'],
	);
	return $data;
}
add_filter( 'wp_insert_post_data', 'murtadd_sec_gate', 99, 2 );

/**
 * Log what the gate held, now that the post has an ID.
 *
 * @param int $post_id Post ID.
 */
function murtadd_sec_gate_log( $post_id ) {
	if ( empty( $GLOBALS['murtadd_sec_held'] ) ) {
		return;
	}
	$entry                       = $GLOBALS['murtadd_sec_held'];
	$GLOBALS['murtadd_sec_held'] = null;
	$entry['post_id']            = (int) $post_id;
	update_post_meta( $post_id, '_murtadd_spam_held', time() );
	murtadd_sec_log( $entry );
}
add_action( 'wp_insert_post', 'murtadd_sec_gate_log', 1 );

/* -------------------------------------------------------------------------
 * 4. 410 Gone for removed spam (answered by inc/gone.php)
 * ---------------------------------------------------------------------- */

/**
 * Path of a URL, without slashes at either end, lower case.
 *
 * @param string $url URL or path.
 * @return string
 */
function murtadd_sec_path( $url ) {
	$path = (string) wp_parse_url( (string) $url, PHP_URL_PATH );
	return trim( strtolower( rawurldecode( $path ) ), '/' );
}

/**
 * Remember the address of a post moved to the trash as spam.
 *
 * @param WP_Post $post Post.
 */
function murtadd_sec_remember_gone( $post ) {
	$gone = get_option( MURTADD_SEC_GONE, array() );
	$gone = is_array( $gone ) ? $gone : array();
	$path = murtadd_sec_path( get_permalink( $post ) );
	if ( '' !== $path ) {
		$gone[ $path ] = (int) $post->ID;
	}
	if ( $post->post_name ) {
		$gone[ strtolower( rawurldecode( $post->post_name ) ) ] = (int) $post->ID;
	}
	// The query permalink, /?p=ID, reaches the same post under any structure.
	$gone[ '?p=' . (int) $post->ID ] = (int) $post->ID;
	update_option( MURTADD_SEC_GONE, array_slice( $gone, -MURTADD_SEC_GONE_MAX, null, true ), false );
}

/**
 * Whether a 404 path is the address of removed spam. Hooked into
 * murtadd_send_gone() (inc/gone.php), which only runs on a 404, after the
 * legacy redirects, and serves the branded 410 page. A post restored from the
 * trash and published again is served as normal, since it is then no 404.
 *
 * @param bool   $hit  Whether the Upkeep list already matched.
 * @param string $path Request path.
 * @return bool
 */
function murtadd_sec_gone_match( $hit, $path ) {
	if ( $hit || ! murtadd_sec_on( 'spam_gone' ) ) {
		return (bool) $hit;
	}
	$gone = get_option( MURTADD_SEC_GONE, array() );
	if ( ! $gone || ! is_array( $gone ) ) {
		return false;
	}
	$path = murtadd_sec_path( $path );
	if ( '' !== $path && isset( $gone[ $path ] ) ) {
		return true;
	}
	$id = isset( $_GET['p'] ) ? absint( wp_unslash( $_GET['p'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
	return $id > 0 && isset( $gone[ '?p=' . $id ] );
}
add_filter( 'murtadd_gone_match', 'murtadd_sec_gone_match', 10, 2 );

/* -------------------------------------------------------------------------
 * 5. Login record for the report
 * ---------------------------------------------------------------------- */

/**
 * Last login time and IP, kept from 1.45.0 on.
 *
 * @param string  $login Login name.
 * @param WP_User $user  User.
 */
function murtadd_sec_record_login( $login, $user ) {
	update_user_meta( $user->ID, '_murtadd_last_login', array( 'time' => time(), 'ip' => murtadd_sec_ip() ) );
}
add_action( 'wp_login', 'murtadd_sec_record_login', 10, 2 );

/* -------------------------------------------------------------------------
 * 6. Admin actions
 * ---------------------------------------------------------------------- */

/**
 * Stop unless the user may manage options and the nonce is valid.
 *
 * @param string $action Nonce action.
 */
function murtadd_sec_check( $action ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to do that.', 'murtadd' ), 403 );
	}
	check_admin_referer( $action );
}

/**
 * Back to the Security tab with a notice.
 *
 * @param string $notice Notice code.
 * @param int    $count  Count.
 */
function murtadd_sec_back( $notice, $count = 0 ) {
	wp_safe_redirect( murtadd_sec_url( array( 'murtadd_sec' => $notice, 'murtadd_count' => (int) $count ) ) );
	exit;
}

/**
 * Revoke every application password of every user.
 */
function murtadd_sec_handle_revoke() {
	murtadd_sec_check( 'murtadd_sec_revoke' );
	$count = 0;
	if ( class_exists( 'WP_Application_Passwords' ) ) {
		foreach ( get_users( array( 'fields' => 'ID' ) ) as $user_id ) {
			$deleted = WP_Application_Passwords::delete_all_application_passwords( (int) $user_id );
			if ( ! is_wp_error( $deleted ) ) {
				$count += (int) $deleted;
			}
		}
	}
	murtadd_sec_log( array( 'event' => 'app-passwords-revoked', 'title' => (string) $count ) );
	murtadd_sec_back( 'revoked', $count );
}
add_action( 'admin_post_murtadd_sec_revoke', 'murtadd_sec_handle_revoke' );

/**
 * End every login session of every user, this one included.
 */
function murtadd_sec_handle_logout() {
	murtadd_sec_check( 'murtadd_sec_logout' );
	murtadd_sec_log( array( 'event' => 'sessions-ended', 'title' => '' ) );
	foreach ( get_users( array( 'fields' => 'ID' ) ) as $user_id ) {
		WP_Session_Tokens::get_instance( (int) $user_id )->destroy_all();
	}
	wp_clear_auth_cookie();
	wp_safe_redirect( wp_login_url( murtadd_sec_url() ) );
	exit;
}
add_action( 'admin_post_murtadd_sec_logout', 'murtadd_sec_handle_logout' );

/**
 * Categories spam created: a spam-scoring name or one of the names seen in the
 * incident, never the default category, and deletable only when nothing but
 * trashed posts uses it. The theme seeds no categories (its topics are the
 * murtadd_topic taxonomy, which is never touched here).
 *
 * @return WP_Term[]
 */
function murtadd_sec_spam_categories() {
	$known   = array( 'casino', 'casinos', 'review', 'reviews', 'betting', 'gambling', 'bonus', 'slots', 'sportsbook', 'kasyno', 'kazino' );
	$default = (int) get_option( 'default_category' );
	$out     = array();
	$terms   = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) );
	foreach ( is_array( $terms ) ? $terms : array() as $term ) {
		if ( (int) $term->term_id === $default || 'uncategorized' === $term->slug ) {
			continue;
		}
		if ( ! in_array( $term->slug, $known, true ) && ! murtadd_spam_score( $term->name, $term->slug, '' )['spam'] ) {
			continue;
		}
		$used = get_posts(
			array(
				'post_type'      => 'any',
				'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private' ),
				'category'       => $term->term_id,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		$term->murtadd_in_use = ! empty( $used );
		$out[]                = $term;
	}
	return $out;
}

/**
 * Delete spam categories that are empty. On request only.
 */
function murtadd_sec_handle_delete_categories() {
	murtadd_sec_check( 'murtadd_sec_categories' );
	$count = 0;
	foreach ( murtadd_sec_spam_categories() as $term ) {
		if ( ! $term->murtadd_in_use && ! is_wp_error( wp_delete_term( $term->term_id, 'category' ) ) ) {
			++$count;
		}
	}
	murtadd_sec_log( array( 'event' => 'categories-deleted', 'title' => (string) $count ) );
	murtadd_sec_back( 'categories', $count );
}
add_action( 'admin_post_murtadd_sec_categories', 'murtadd_sec_handle_delete_categories' );

/**
 * Clear the security log.
 */
function murtadd_sec_handle_clear_log() {
	murtadd_sec_check( 'murtadd_sec_clear_log' );
	delete_option( MURTADD_SEC_LOG );
	murtadd_sec_back( 'log-cleared' );
}
add_action( 'admin_post_murtadd_sec_clear_log', 'murtadd_sec_handle_clear_log' );

/**
 * AJAX: one batch of the scan.
 */
function murtadd_sec_ajax_scan() {
	check_ajax_referer( 'murtadd_sec_ajax', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to do that.', 'murtadd' ) ), 403 );
	}
	$offset = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
	$batch  = 100;
	$query  = new WP_Query(
		array(
			'post_type'              => murtadd_sec_types(),
			'post_status'            => array( 'publish', 'pending', 'draft', 'future', 'private' ),
			'posts_per_page'         => $batch,
			'offset'                 => $offset,
			'orderby'                => 'ID',
			'order'                  => 'DESC',
			'no_found_rows'          => false,
			'update_post_term_cache' => false,
			'suppress_filters'       => true,
		)
	);
	$rows = array();
	foreach ( $query->posts as $post ) {
		if ( murtadd_sec_post_is_seeded( $post->ID ) ) {
			continue;
		}
		$verdict = murtadd_spam_score( $post->post_title, $post->post_name, $post->post_content );
		if ( ! $verdict['spam'] ) {
			continue;
		}
		$author = get_userdata( (int) $post->post_author );
		$rows[] = array(
			'id'      => (int) $post->ID,
			'title'   => '' !== $post->post_title ? wp_strip_all_tags( $post->post_title ) : __( '(no title)', 'murtadd' ),
			'date'    => get_the_date( 'Y-m-d H:i', $post ),
			'status'  => $post->post_status,
			'type'    => $post->post_type,
			'author'  => $author ? $author->user_login : '',
			'reasons' => $verdict['reasons'],
			'edit'    => (string) get_edit_post_link( $post->ID, 'raw' ),
			'view'    => 'publish' === $post->post_status ? (string) get_permalink( $post ) : '',
		);
	}
	wp_send_json_success(
		array(
			'rows'  => $rows,
			'next'  => $offset + count( $query->posts ),
			'total' => (int) $query->found_posts,
			'done'  => count( $query->posts ) < $batch,
		)
	);
}
add_action( 'wp_ajax_murtadd_sec_scan', 'murtadd_sec_ajax_scan' );

/**
 * AJAX: move a batch of posts to the trash, never deleting. Each one is scored
 * again here, so only posts that still read as spam and are not starter
 * content go.
 */
function murtadd_sec_ajax_trash() {
	check_ajax_referer( 'murtadd_sec_ajax', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to do that.', 'murtadd' ) ), 403 );
	}
	$ids     = isset( $_POST['ids'] ) ? array_slice( array_map( 'absint', (array) wp_unslash( $_POST['ids'] ) ), 0, 100 ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- absint on each.
	$trashed = array();
	$skipped = array();
	foreach ( array_filter( $ids ) as $id ) {
		$post = get_post( $id );
		if ( ! $post || ! in_array( $post->post_type, murtadd_sec_types(), true ) || 'trash' === $post->post_status || ! current_user_can( 'delete_post', $id ) || murtadd_sec_post_is_seeded( $post->ID ) ) {
			$skipped[] = $id;
			continue;
		}
		if ( ! murtadd_spam_score( $post->post_title, $post->post_name, $post->post_content )['spam'] ) {
			$skipped[] = $id;
			continue;
		}
		if ( 'publish' === $post->post_status ) {
			murtadd_sec_remember_gone( $post );
		}
		if ( wp_trash_post( $id ) ) {
			$trashed[] = $id;
		} else {
			$skipped[] = $id;
		}
	}
	if ( $trashed ) {
		murtadd_sec_log( array( 'event' => 'trashed', 'title' => (string) count( $trashed ) ) );
	}
	wp_send_json_success( array( 'trashed' => $trashed, 'skipped' => $skipped ) );
}
add_action( 'wp_ajax_murtadd_sec_trash', 'murtadd_sec_ajax_trash' );

/* -------------------------------------------------------------------------
 * 7. Screen: the tools and reports under the Security tab's form
 * ---------------------------------------------------------------------- */

/**
 * Assets for the Security tab.
 *
 * @param string $hook Screen hook.
 */
function murtadd_sec_assets( $hook ) {
	// Every tab is on the one screen now, so the tools load wherever it opens.
	if ( murtadd_settings_hook() !== $hook || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$base = get_stylesheet_directory_uri() . '/assets';
	wp_enqueue_style( 'murtadd-security', $base . '/css/security' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-admin' ), MURTADD_VERSION );
	wp_enqueue_script( 'murtadd-security', $base . '/js/security' . MURTADD_ASSET_SUFFIX . '.js', array(), MURTADD_VERSION, true );
	wp_localize_script(
		'murtadd-security',
		'murtaddSecurity',
		array(
			'ajax'  => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'murtadd_sec_ajax' ),
			'i18n'  => array(
				/* translators: 1: checked, 2: total, 3: found. */
				'scanning'     => __( 'Scanning: %1$d of %2$d checked, %3$d found.', 'murtadd' ),
				/* translators: 1: checked, 2: found. */
				'done'         => __( 'Scan finished: %1$d checked, %2$d found.', 'murtadd' ),
				'none'         => __( 'No suspected spam found.', 'murtadd' ),
				/* translators: 1: done, 2: total. */
				'trashing'     => __( 'Moving to the trash: %1$d of %2$d.', 'murtadd' ),
				/* translators: 1: trashed, 2: skipped. */
				'trashed'      => __( '%1$d moved to the trash, %2$d skipped. The Trash screen of each type can restore them.', 'murtadd' ),
				'confirmTrash' => __( 'Move the selected items to the trash? They can be restored from the Trash.', 'murtadd' ),
				'error'        => __( 'The request failed. Reload the page and try again.', 'murtadd' ),
				'edit'         => __( 'Edit', 'murtadd' ),
				'view'         => __( 'View', 'murtadd' ),
				'select'       => __( 'Select', 'murtadd' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'murtadd_sec_assets', 20 );

/**
 * Notices after Security actions.
 */
function murtadd_sec_notice() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	$code = isset( $_GET['murtadd_sec'] ) ? sanitize_key( wp_unslash( $_GET['murtadd_sec'] ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	$n   = isset( $_GET['murtadd_count'] ) ? absint( $_GET['murtadd_count'] ) : 0;
	$map = array(
		/* translators: %d: number of application passwords. */
		'revoked'     => sprintf( __( 'Application passwords revoked: %d.', 'murtadd' ), $n ),
		/* translators: %d: number of categories. */
		'categories'  => sprintf( __( 'Empty spam categories deleted: %d.', 'murtadd' ), $n ),
		'log-cleared' => __( 'Security log cleared.', 'murtadd' ),
	);
	if ( isset( $map[ $code ] ) ) {
		printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $map[ $code ] ) );
	}
}

/**
 * Small action form posting to admin-post.php.
 *
 * @param string $action  Action and nonce name.
 * @param string $label   Button label.
 * @param string $confirm Confirmation text.
 * @param string $class   Button class.
 */
function murtadd_sec_button( $action, $label, $confirm, $class = 'secondary' ) {
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="murtadd-sec-form" data-murtadd-confirm="<?php echo esc_attr( $confirm ); ?>">
		<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
		<?php wp_nonce_field( $action ); ?>
		<?php submit_button( $label, $class, 'submit', false ); ?>
	</form>
	<?php
}

/**
 * Administrators with last login, sessions and application passwords.
 *
 * @return array
 */
function murtadd_sec_admin_rows() {
	$rows = array();
	foreach ( get_users( array( 'role' => 'administrator' ) ) as $user ) {
		$last     = get_user_meta( $user->ID, '_murtadd_last_login', true );
		$sessions = get_user_meta( $user->ID, 'session_tokens', true );
		$time     = is_array( $last ) && ! empty( $last['time'] ) ? (int) $last['time'] : 0;
		$ip       = is_array( $last ) && ! empty( $last['ip'] ) ? $last['ip'] : '';
		if ( is_array( $sessions ) ) {
			foreach ( $sessions as $session ) {
				if ( ! empty( $session['login'] ) && (int) $session['login'] > $time ) {
					$time = (int) $session['login'];
					$ip   = $session['ip'] ?? $ip;
				}
			}
		}
		$rows[] = array(
			'user'     => $user,
			'login'    => $time,
			'ip'       => $ip,
			'sessions' => is_array( $sessions ) ? count( $sessions ) : 0,
			'apps'     => class_exists( 'WP_Application_Passwords' ) ? WP_Application_Passwords::get_user_application_passwords( $user->ID ) : array(),
		);
	}
	return $rows;
}

/**
 * Items per day and author over the last 30 days, and hours with bursts.
 *
 * @return array{days:array,bursts:array}
 */
function murtadd_sec_activity() {
	global $wpdb;
	$types = murtadd_sec_types();
	$since = gmdate( 'Y-m-d H:i:s', time() - 30 * DAY_IN_SECONDS );
	$in    = implode( ',', array_fill( 0, count( $types ), '%s' ) );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQLPlaceholders -- report query, read only; placeholders built above.
	$rows   = $wpdb->get_results( $wpdb->prepare( "SELECT post_author, DATE(post_date) AS d, HOUR(post_date) AS h, COUNT(*) AS n FROM {$wpdb->posts} WHERE post_type IN ($in) AND post_status NOT IN ('auto-draft','inherit') AND post_date_gmt >= %s GROUP BY post_author, d, h", array_merge( $types, array( $since ) ) ) );
	$days   = array();
	$bursts = array();
	foreach ( (array) $rows as $row ) {
		$user  = get_userdata( (int) $row->post_author );
		$login = $user ? $user->user_login : '#' . (int) $row->post_author;
		$days[ $row->d ][ $login ] = ( $days[ $row->d ][ $login ] ?? 0 ) + (int) $row->n;
		if ( (int) $row->n > 2 ) {
			$bursts[] = array( 'login' => $login, 'when' => sprintf( '%s %02d:00', $row->d, (int) $row->h ), 'n' => (int) $row->n );
		}
	}
	krsort( $days );
	return array( 'days' => $days, 'bursts' => $bursts );
}

/**
 * Starter items whose content now carries gambling links.
 *
 * @return WP_Post[]
 */
function murtadd_sec_tainted_seeds() {
	$by_type = function_exists( 'murtadd_seeded_slugs' ) ? murtadd_seeded_slugs() : array();
	if ( function_exists( 'murtadd_required_pages' ) ) {
		$by_type['page'] = array_keys( murtadd_required_pages() );
	}
	$out = array();
	foreach ( $by_type as $type => $slugs ) {
		$slugs = array_values( array_filter( array_map( 'strval', (array) $slugs ) ) );
		if ( ! $slugs ) {
			continue;
		}
		$posts = get_posts(
			array(
				'post_type'      => $type,
				'post_status'    => 'any',
				'post_name__in'  => $slugs,
				'posts_per_page' => 500,
				'no_found_rows'  => true,
			)
		);
		foreach ( $posts as $post ) {
			$hosts = murtadd_spam_links( $post->post_content );
			if ( $hosts ) {
				$post->murtadd_hosts = $hosts;
				$out[]               = $post;
			}
		}
	}
	return $out;
}

/**
 * Whether Rank Math Instant Indexing is on.
 *
 * @return bool
 */
function murtadd_sec_instant_indexing() {
	return class_exists( '\RankMath\Helper' ) && method_exists( '\RankMath\Helper', 'is_module_active' ) && \RankMath\Helper::is_module_active( 'instant-indexing' );
}

/**
 * Tools and reports, rendered under the settings form on the Security tab.
 */
function murtadd_sec_render_tools() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
	$admins = murtadd_sec_admin_rows();
	$total  = 0;
	foreach ( $admins as $a ) {
		$total += count( $a['apps'] );
	}
	?>
	<div class="murtadd-sec" id="murtadd-security-tools">
		<?php murtadd_sec_notice(); ?>

		<?php $murtadd_unknown_php = murtadd_sec_unknown_root_php(); ?>
		<section class="murtadd-sec-card">
			<h2><?php esc_html_e( 'Files at the top of the site', 'murtadd' ); ?></h2>
			<p class="description"><?php esc_html_e( 'On Apache the theme keeps two sections in .htaccess. "Murtadd: only WordPress root PHP" lets the web server run only WordPress\'s own PHP files at the top of the site (index.php, wp-login.php, wp-cron.php and the like), so a file someone left there, such as ox-*.php, answers 403. "Murtadd: deny direct core files" answers 403 for the PHP files in wp-includes (the editor\'s TinyMCE loader and ms-files.php excepted), wp-settings.php, wp-config-sample.php, readme.html and wp-admin/install.php. A site that runs other PHP files at its root adds define( \'MURTADD_ALLOW_ROOT_PHP\', true ); to wp-config.php; define( \'MURTADD_ALLOW_CORE_FILES\', true ); takes the second section out.', 'murtadd' ); ?></p>
			<?php if ( $murtadd_unknown_php ) : ?>
				<p><strong><?php esc_html_e( 'These PHP files at the top of the site are not part of WordPress. Check each one and delete it by FTP unless you put it there:', 'murtadd' ); ?></strong></p>
				<ul class="ul-disc">
					<?php foreach ( $murtadd_unknown_php as $murtadd_name ) : ?>
						<li><code><?php echo esc_html( $murtadd_name ); ?></code></li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p><?php esc_html_e( 'No PHP file at the top of the site is foreign to WordPress.', 'murtadd' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="murtadd-sec-card">
			<h2><?php esc_html_e( 'Clean up spam', 'murtadd' ); ?></h2>
			<p class="description"><?php esc_html_e( 'The scan reads every post, page, doubt, rebuttal, fatwa, letter and guide that is published, pending, scheduled, private or a draft, 100 at a time, and lists those that read as gambling spam. Starter content is never listed. Nothing changes until you move items to the trash, and the trash can restore them. Nothing is ever deleted for good here.', 'murtadd' ); ?></p>
			<div class="murtadd-sec-bar">
				<button type="button" class="button button-primary" id="murtadd-sec-scan"><?php esc_html_e( 'Scan for spam', 'murtadd' ); ?></button>
				<button type="button" class="button" id="murtadd-sec-trash" disabled><?php esc_html_e( 'Move selected to Trash', 'murtadd' ); ?></button>
				<span class="murtadd-sec-status" id="murtadd-sec-status" role="status" aria-live="polite"></span>
			</div>
			<div class="murtadd-sec-scroll" id="murtadd-sec-results-wrap" hidden>
				<table class="widefat striped murtadd-admin-table murtadd-sec-table" id="murtadd-sec-results">
					<thead>
						<tr>
							<td class="check-column"><input type="checkbox" id="murtadd-sec-all" aria-label="<?php esc_attr_e( 'Select all', 'murtadd' ); ?>"></td>
							<th scope="col"><?php esc_html_e( 'Title', 'murtadd' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Date', 'murtadd' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Type and status', 'murtadd' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Author', 'murtadd' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Reasons', 'murtadd' ); ?></th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
			<p class="description">
				<?php
				esc_html_e( 'A trashed item answers 404, or 410 Gone when that switch is on. Resubmit the sitemap in Google Search Console afterwards.', 'murtadd' );
				echo ' ';
				echo murtadd_sec_instant_indexing()
					? esc_html__( 'Rank Math Instant Indexing is on. Held spam is never published, so it is never submitted.', 'murtadd' )
					: esc_html__( 'Rank Math Instant Indexing is off.', 'murtadd' );
				?>
			</p>
		</section>

		<section class="murtadd-sec-card">
			<h2><?php esc_html_e( 'Spam categories', 'murtadd' ); ?></h2>
			<?php $cats = murtadd_sec_spam_categories(); ?>
			<?php if ( $cats ) : ?>
				<ul class="murtadd-sec-list">
					<?php foreach ( $cats as $term ) : ?>
						<li>
							<?php
							echo esc_html( $term->name . ' (' . $term->slug . ') ' );
							echo $term->murtadd_in_use ? esc_html__( 'still used by items outside the trash, kept', 'murtadd' ) : esc_html__( 'empty, can be deleted', 'murtadd' );
							?>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php murtadd_sec_button( 'murtadd_sec_categories', __( 'Delete empty spam categories', 'murtadd' ), __( 'Delete the empty spam categories listed? Topics, schools and the default category are never touched.', 'murtadd' ) ); ?>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'No spam categories found.', 'murtadd' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="murtadd-sec-card">
			<h2><?php esc_html_e( 'Passwords and sessions', 'murtadd' ); ?></h2>
			<p class="description">
				<?php
				/* translators: %d: number of application passwords. */
				echo esc_html( sprintf( __( 'Application passwords stored on administrator accounts: %d.', 'murtadd' ), $total ) );
				?>
			</p>
			<div class="murtadd-sec-bar">
				<?php
				murtadd_sec_button( 'murtadd_sec_revoke', __( 'Revoke all application passwords', 'murtadd' ), __( 'Revoke every application password of every user? Tools that use them stop working.', 'murtadd' ), 'delete' );
				murtadd_sec_button( 'murtadd_sec_logout', __( 'Log out all sessions', 'murtadd' ), __( 'End every login session of every user, yours included? You will need to log in again.', 'murtadd' ), 'delete' );
				?>
			</div>
			<h3><?php esc_html_e( 'Administrator accounts', 'murtadd' ); ?></h3>
			<div class="murtadd-sec-scroll">
				<table class="widefat striped murtadd-admin-table murtadd-sec-table">
					<thead><tr><th scope="col"><?php esc_html_e( 'Account', 'murtadd' ); ?></th><th scope="col"><?php esc_html_e( 'Last login', 'murtadd' ); ?></th><th scope="col"><?php esc_html_e( 'Sessions', 'murtadd' ); ?></th><th scope="col"><?php esc_html_e( 'Application passwords', 'murtadd' ); ?></th></tr></thead>
					<tbody>
						<?php foreach ( $admins as $a ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( get_edit_user_link( $a['user']->ID ) ); ?>"><?php echo esc_html( $a['user']->user_login ); ?></a><br><span class="murtadd-sec-muted"><?php echo esc_html( $a['user']->user_email ); ?></span></td>
								<td><?php echo $a['login'] ? esc_html( wp_date( $format, $a['login'] ) . ( $a['ip'] ? ' (' . $a['ip'] . ')' : '' ) ) : esc_html__( 'Not recorded', 'murtadd' ); ?></td>
								<td><?php echo esc_html( (string) $a['sessions'] ); ?></td>
								<td>
									<?php
									echo esc_html( (string) count( $a['apps'] ) );
									foreach ( $a['apps'] as $app ) {
										echo '<br><span class="murtadd-sec-muted">' . esc_html( $app['name'] . ': ' . ( $app['last_used'] ? wp_date( $format, (int) $app['last_used'] ) . ( $app['last_ip'] ? ' (' . $app['last_ip'] . ')' : '' ) : __( 'never used', 'murtadd' ) ) ) . '</span>';
									}
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</section>

		<section class="murtadd-sec-card">
			<h2><?php esc_html_e( 'Attacks seen', 'murtadd' ); ?></h2>
			<?php
			$counts = murtadd_sec_counts();
			$labels = array(
				'xmlrpc'          => __( 'XML-RPC requests refused', 'murtadd' ),
				'fake-jetpack'    => __( 'of them posing as Jetpack', 'murtadd' ),
				'remote-password' => __( 'correct passwords refused on XML-RPC', 'murtadd' ),
				'failed-login'    => __( 'failed logins', 'murtadd' ),
			);
			?>
			<p class="description"><?php esc_html_e( 'Counted per day, in UTC, for the last 30 days. A correct password refused on XML-RPC means someone else knows the password: change it.', 'murtadd' ); ?></p>
			<?php if ( $counts ) : ?>
				<div class="murtadd-sec-scroll">
					<table class="widefat striped murtadd-admin-table murtadd-sec-table">
						<thead><tr><th scope="col"><?php esc_html_e( 'Day', 'murtadd' ); ?></th><?php foreach ( $labels as $label ) : ?><th scope="col"><?php echo esc_html( $label ); ?></th><?php endforeach; ?></tr></thead>
						<tbody>
							<?php foreach ( $counts as $day => $row ) : ?>
								<tr><td><?php echo esc_html( $day ); ?></td><?php foreach ( array_keys( $labels ) as $key ) : ?><td><?php echo esc_html( (string) (int) ( $row[ $key ] ?? 0 ) ); ?></td><?php endforeach; ?></tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'Nothing counted yet.', 'murtadd' ); ?></p>
			<?php endif; ?>
			<h3><?php esc_html_e( 'Accounts being guessed, last 24 hours', 'murtadd' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Attackers change address on every try, so failures are counted by account name. An account of yours in this list should get a new, long password, and ideally a new name that is not the site name.', 'murtadd' ); ?></p>
			<?php
			$failed = murtadd_sec_failed_recent();
			uasort(
				$failed,
				static function ( $x, $y ) {
					return (int) $y['n'] - (int) $x['n'];
				}
			);
			?>
			<?php if ( $failed ) : ?>
				<ul class="murtadd-sec-list">
					<?php foreach ( array_slice( $failed, 0, 20, true ) as $name => $row ) : ?>
						<li>
							<?php
							$real = username_exists( $name ) || email_exists( $name );
							/* translators: 1: account name, 2: failures, 3: addresses, 4: whether the account exists. */
							echo esc_html( sprintf( __( '%1$s: %2$d failures from %3$d addresses%4$s', 'murtadd' ), $name, (int) $row['n'], count( (array) $row['ips'] ), $real ? __( ', a real account on this site', 'murtadd' ) : '' ) );
							?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'None.', 'murtadd' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="murtadd-sec-card">
			<h2><?php esc_html_e( 'Activity', 'murtadd' ); ?></h2>
			<?php $activity = murtadd_sec_activity(); ?>
			<h3><?php esc_html_e( 'Items added per day, last 30 days', 'murtadd' ); ?></h3>
			<?php if ( $activity['days'] ) : ?>
				<div class="murtadd-sec-scroll">
					<table class="widefat striped murtadd-admin-table murtadd-sec-table">
						<thead><tr><th scope="col"><?php esc_html_e( 'Day', 'murtadd' ); ?></th><th scope="col"><?php esc_html_e( 'Account and count', 'murtadd' ); ?></th></tr></thead>
						<tbody>
							<?php foreach ( $activity['days'] as $day => $per ) : ?>
								<tr>
									<td><?php echo esc_html( $day ); ?></td>
									<td>
										<?php
										$parts = array();
										foreach ( $per as $login => $n ) {
											$parts[] = $login . ': ' . $n;
										}
										echo esc_html( implode( ', ', $parts ) );
										?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'Nothing added in the last 30 days.', 'murtadd' ); ?></p>
			<?php endif; ?>
			<h3><?php esc_html_e( 'Burst publishing', 'murtadd' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Hours in which one account added more than two items. A report only: nothing is held or trashed for this.', 'murtadd' ); ?></p>
			<?php if ( $activity['bursts'] ) : ?>
				<ul class="murtadd-sec-list">
					<?php foreach ( array_slice( $activity['bursts'], 0, 50 ) as $b ) : ?>
						<li><?php echo esc_html( sprintf( '%s, %s: %d', $b['login'], $b['when'], $b['n'] ) ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'None.', 'murtadd' ); ?></p>
			<?php endif; ?>
			<h3><?php esc_html_e( 'Starter content with gambling links', 'murtadd' ); ?></h3>
			<?php $tainted = murtadd_sec_tainted_seeds(); ?>
			<?php if ( $tainted ) : ?>
				<p class="description"><?php esc_html_e( 'These starter items link to gambling sites. Nothing is changed here: open each one and remove the links by hand.', 'murtadd' ); ?></p>
				<ul class="murtadd-sec-list">
					<?php foreach ( $tainted as $post ) : ?>
						<li><a href="<?php echo esc_url( (string) get_edit_post_link( $post->ID ) ); ?>"><?php echo esc_html( $post->post_title ); ?></a> <span class="murtadd-sec-muted"><?php echo esc_html( implode( ', ', $post->murtadd_hosts ) ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'None: no starter item links to a gambling site.', 'murtadd' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="murtadd-sec-card">
			<h2><?php esc_html_e( 'Security log', 'murtadd' ); ?></h2>
			<?php
			$log = get_option( MURTADD_SEC_LOG, array() );
			$log = is_array( $log ) ? array_reverse( $log ) : array();
			?>
			<?php if ( $log ) : ?>
				<div class="murtadd-sec-scroll murtadd-sec-scroll--tall">
					<table class="widefat striped murtadd-admin-table murtadd-sec-table">
						<thead><tr><th scope="col"><?php esc_html_e( 'Time', 'murtadd' ); ?></th><th scope="col"><?php esc_html_e( 'Event', 'murtadd' ); ?></th><th scope="col"><?php esc_html_e( 'Detail', 'murtadd' ); ?></th><th scope="col"><?php esc_html_e( 'Account', 'murtadd' ); ?></th><th scope="col"><?php esc_html_e( 'Route', 'murtadd' ); ?></th><th scope="col"><?php esc_html_e( 'IP', 'murtadd' ); ?></th></tr></thead>
						<tbody>
							<?php foreach ( $log as $e ) : ?>
								<?php $u = ! empty( $e['user'] ) ? get_userdata( (int) $e['user'] ) : false; ?>
								<tr>
									<td><?php echo esc_html( wp_date( $format, (int) $e['time'] ) ); ?></td>
									<td><?php echo esc_html( (string) $e['event'] ); ?></td>
									<td>
										<?php
										if ( ! empty( $e['post_id'] ) && get_post( (int) $e['post_id'] ) ) {
											echo '<a href="' . esc_url( (string) get_edit_post_link( (int) $e['post_id'] ) ) . '">' . esc_html( (string) $e['title'] ) . '</a>';
										} else {
											echo esc_html( (string) $e['title'] );
										}
										if ( ! empty( $e['reasons'] ) ) {
											echo '<br><span class="murtadd-sec-muted">' . esc_html( implode( '; ', (array) $e['reasons'] ) ) . '</span>';
										}
										?>
									</td>
									<td><?php echo esc_html( $u ? $u->user_login : '' ); ?></td>
									<td><?php echo esc_html( (string) $e['type'] ); ?></td>
									<td><?php echo esc_html( (string) $e['ip'] ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php murtadd_sec_button( 'murtadd_sec_clear_log', __( 'Clear the log', 'murtadd' ), __( 'Clear the security log?', 'murtadd' ) ); ?>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'Nothing logged yet.', 'murtadd' ); ?></p>
			<?php endif; ?>
		</section>
	</div>
	<?php
}
