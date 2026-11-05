<?php
/**
 * Development files in the theme folder.
 *
 * The owner uploads the theme by FTP from a working copy, which puts the
 * repository (.git, .github), the contributor notes (.claude), the tests,
 * Composer's files and its development packages (vendor/phpunit and the
 * rest) on the live server, where the web server may serve or even run
 * them. The theme's .htaccess and the deny files in tests/, bin/, docs/,
 * config/ and vendor/ refuse them meanwhile; this module removes them on the
 * first request of any kind after the version changes. bin/ and docs/ stay:
 * every release ships them, and the Docs tab reads docs/*.md. In vendor/
 * only Parsedown stays, which the Docs tab loads.
 *
 * A local or development site keeps everything, so a theme run from a
 * working copy keeps its repository; so does
 * define( 'MURTADD_KEEP_DOTFILES', true ); in wp-config.php.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether this site keeps the development files.
 *
 * @return bool
 */
function murtadd_keeps_dev_files() {
	return ( defined( 'MURTADD_KEEP_DOTFILES' ) && MURTADD_KEEP_DOTFILES )
		|| in_array( wp_get_environment_type(), array( 'local', 'development' ), true );
}

/**
 * The development entries in the theme folder: every hidden file and folder
 * at the top except the server's own (.htaccess, .user.ini, .well-known),
 * the files and folders a release zip leaves out (tests, config, composer.json,
 * composer.lock, phpunit.xml.dist), and everything in vendor/ but Parsedown.
 * Empty on a site that keeps them.
 *
 * @param string $dir The theme folder; the stylesheet folder when empty.
 * @return string[] Absolute paths.
 */
function murtadd_dev_entries( $dir = '' ) {
	if ( murtadd_keeps_dev_files() ) {
		return array();
	}
	$dir  = '' === $dir ? get_stylesheet_directory() : $dir;
	$keep = array( '.', '..', '.htaccess', '.user.ini', '.well-known' );
	$list = array();
	foreach ( (array) @scandir( $dir ) as $name ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
		if ( is_string( $name ) && '.' === substr( $name, 0, 1 ) && ! in_array( $name, $keep, true ) ) {
			$list[] = $dir . '/' . $name;
		}
	}
	foreach ( array( 'tests', 'tools', 'config', 'composer.json', 'composer.lock', 'phpunit.xml.dist', 'phpunit.xml' ) as $name ) {
		if ( file_exists( $dir . '/' . $name ) || is_link( $dir . '/' . $name ) ) {
			$list[] = $dir . '/' . $name;
		}
	}
	// vendor/ ships with Parsedown alone; its deny file stays too. A vendor/
	// that is a link points outside the theme, and is never walked.
	$vendor = is_link( $dir . '/vendor' ) ? array() : (array) @scandir( $dir . '/vendor' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	foreach ( $vendor as $name ) {
		if ( is_string( $name ) && ! in_array( $name, array( '.', '..', '.htaccess', 'parsedown' ), true ) ) {
			$list[] = $dir . '/vendor/' . $name;
		}
	}
	return $list;
}

/**
 * Remove a file or a folder and everything in it. A link is removed itself,
 * never followed. Read-only files (git keeps its objects read-only) are made
 * writable first.
 *
 * @param string $path File or folder.
 * @return int Entries removed.
 */
function murtadd_remove_tree( $path ) {
	if ( is_link( $path ) || is_file( $path ) ) {
		do_action( 'murtadd_cleanup_tick' );
		// chmod() follows a link: only a real file is made writable.
		is_link( $path ) || @chmod( $path, 0644 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
		return @unlink( $path ) ? 1 : 0; // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
	}
	if ( ! is_dir( $path ) ) {
		return 0;
	}
	try {
		$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
	} catch ( UnexpectedValueException $e ) {
		return 0;
	}
	$gone = 0;
	try {
		foreach ( $it as $f ) {
			// A long walk keeps the clean-up's lease fresh (murtadd_renew_cleanup_lease()).
			do_action( 'murtadd_cleanup_tick' );
			if ( $f->isDir() && ! $f->isLink() ) {
				$gone += @rmdir( $f->getPathname() ) ? 1 : 0; // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
			} else {
				$f->isLink() || @chmod( $f->getPathname(), 0644 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
				$gone += @unlink( $f->getPathname() ) ? 1 : 0; // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
			}
		}
	} catch ( UnexpectedValueException $e ) {
		// A folder inside that cannot be read: what is left stays, and the
		// clean-up tries again later (murtadd_cleanup_on_version_change()).
		unset( $e );
	}
	$gone += @rmdir( $path ) ? 1 : 0; // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
	return $gone;
}

/**
 * Remove every development entry.
 *
 * @return int Entries removed.
 */
function murtadd_remove_dev_files() {
	$gone = 0;
	foreach ( murtadd_dev_entries() as $path ) {
		$gone += murtadd_remove_tree( $path );
	}
	return $gone;
}

/**
 * Once for each version, on the first request of any kind after the version
 * changes: an FTP upload puts .git and the tests on the server at once, and
 * the site may see no administrator for days.
 *
 * One request runs it, under a lease held in an option. add_option() takes
 * a free lease (it fails when the option exists); the running clean-up
 * renews it once a minute while it works (murtadd_renew_cleanup_lease(), on the
 * "murtadd_cleanup_tick" action fired for every entry it removes), and releases
 * it only while it still holds it (murtadd_release_lock()). A lease left
 * unrenewed for five minutes belongs to a request that died, and is taken
 * over with one compare-and-set UPDATE that only one request can win
 * (murtadd_swap_lock()). The version is recorded after the clean-up returns, and
 * only when nothing is left, so a request that dies part-way, or an entry
 * that could not be removed, is retried.
 */
function murtadd_cleanup_on_version_change() {
	if ( get_option( 'murtadd_dev_files_version' ) === MURTADD_VERSION ) {
		return;
	}
	if ( murtadd_keeps_dev_files() ) {
		return;
	}
	$lease = time() . '.' . wp_rand( 100000, 999999 );
	if ( ! add_option( 'murtadd_dev_files_lock', $lease, '', false ) ) {
		$held = (string) get_option( 'murtadd_dev_files_lock' );
		if ( time() - (int) $held < 5 * MINUTE_IN_SECONDS || ! murtadd_swap_lock( 'murtadd_dev_files_lock', $held, $lease ) ) {
			return;
		}
	}
	// Read the version again, from the database, now the lease is held: a
	// request that passed the check above may have paused while another ran
	// the clean-up, recorded the version and released the lease.
	global $wpdb;
	wp_cache_delete( 'murtadd_dev_files_version', 'options' );
	if ( MURTADD_VERSION === (string) $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'murtadd_dev_files_version' ) ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		murtadd_release_lock( 'murtadd_dev_files_lock', $lease );
		return;
	}
	$GLOBALS['murtadd_cleanup_lease'] = $lease;
	add_action( 'murtadd_cleanup_tick', 'murtadd_renew_cleanup_lease' );
	try {
		$gone = murtadd_remove_dev_files();
	} finally {
		remove_action( 'murtadd_cleanup_tick', 'murtadd_renew_cleanup_lease' );
	}
	// The lease is swapped one last time (which fails if another request
	// took it over, and then everything is left to that one), the version is
	// recorded, and only then is the lease released, so no request can start
	// a second clean-up in between.
	$held  = (string) $GLOBALS['murtadd_cleanup_lease'];
	$final = time() . '.' . substr( $held, strpos( $held, '.' ) + 1 ) . '-done';
	unset( $GLOBALS['murtadd_cleanup_lease'] );
	$left = count( murtadd_dev_entries() );
	if ( $left ) {
		// Something could not be removed (permissions, a folder that cannot
		// be read). The version stays unrecorded and the lease is renewed
		// and kept, so the next try comes once it is five minutes stale,
		// never on every request. One log line a day says so.
		murtadd_swap_lock( 'murtadd_dev_files_lock', $held, time() . '.' . substr( $held, strpos( $held, '.' ) + 1 ) );
		if ( ! get_transient( 'murtadd_dev_files_failed' ) ) {
			set_transient( 'murtadd_dev_files_failed', 1, DAY_IN_SECONDS );
			error_log( 'Murtadd: removed ' . $gone . ' development files from the theme folder; ' . $left . ' could not be removed and are tried again every five minutes. Delete them by FTP.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
		}
		return;
	}
	if ( ! murtadd_swap_lock( 'murtadd_dev_files_lock', $held, $final ) ) {
		return;
	}
	update_option( 'murtadd_dev_files_version', MURTADD_VERSION, false );
	murtadd_release_lock( 'murtadd_dev_files_lock', $final );
	if ( $gone ) {
		error_log( 'Murtadd: removed ' . $gone . ' development files from the theme folder.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	}
}
add_action( 'init', 'murtadd_cleanup_on_version_change', 1 );

/**
 * Renew the running clean-up's lease, at most once a minute, with the same
 * compare-and-set, so it never looks stale while it works.
 */
function murtadd_renew_cleanup_lease() {
	$held = (string) ( $GLOBALS['murtadd_cleanup_lease'] ?? '' );
	if ( '' === $held || time() - (int) $held < MINUTE_IN_SECONDS ) {
		return;
	}
	$next = time() . '.' . substr( $held, strpos( $held, '.' ) + 1 );
	if ( murtadd_swap_lock( 'murtadd_dev_files_lock', $held, $next ) ) {
		$GLOBALS['murtadd_cleanup_lease'] = $next;
	}
}

/**
 * Release a lock only while it still holds this request's value, in one
 * DELETE, so a request that lost its lease never frees its successor's.
 *
 * @param string $name The lock's option name.
 * @param string $held This request's value.
 * @return bool Whether this request still held it and released it.
 */
function murtadd_release_lock( $name, $held ) {
	global $wpdb;
	$done = (int) $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s", $name, $held ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	wp_cache_delete( $name, 'options' );
	wp_cache_delete( 'alloptions', 'options' );
	wp_cache_delete( 'notoptions', 'options' );
	return 1 === $done;
}

/**
 * Replace a lock's value, for one request only: the new value is written
 * only where the row still holds the value read before, in a single UPDATE,
 * so of two requests that read the same value exactly one changes the row.
 *
 * @param string $name The lock's option name.
 * @param string $held The value read from it.
 * @param string $next The value to write.
 * @return bool Whether this request changed it.
 */
function murtadd_swap_lock( $name, $held, $next ) {
	global $wpdb;
	$won = (int) $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value = %s", $next, $name, $held ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	wp_cache_delete( $name, 'options' );
	wp_cache_delete( 'alloptions', 'options' );
	return 1 === $won;
}
