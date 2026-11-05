<?php
/**
 * The fixes from the live logs of November 2026.
 *
 * murtadd.org was compromised: random ox-*.php files sat live at the root,
 * and spam addresses (/?items/<n>.sthml, /index.php/sitemap<n>.xml,
 * /sitemapimages<n>.xml, /contents/…) were answered 200 with the home page.
 * FTP uploads that sent functions.php before inc/ ended in a fatal error, a
 * working copy put .git, tests and vendor/phpunit on the server, and bots
 * asked for WordPress's own PHP files by address. Each fix is pinned here.
 *
 * @package Murtadd\Tests
 */

// Run from the command line only: on a web server this file does nothing.
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

use PHPUnit\Framework\TestCase;

if ( ! function_exists( 'wp_get_environment_type' ) ) {
	function wp_get_environment_type() {
		return $GLOBALS['murtadd_test_env'] ?? 'production';
	}
}
if ( ! function_exists( 'wp_rand' ) ) {
	function wp_rand( $min = 0, $max = 0 ) {
		return random_int( $min, $max );
	}
}
if ( ! function_exists( 'wp_cache_delete' ) ) {
	function wp_cache_delete( $key, $group = '' ) {
		return true;
	}
}
if ( ! function_exists( 'do_action' ) ) {
	function do_action( $hook, ...$args ) {}
}
if ( ! function_exists( 'wp_doing_cron' ) ) {
	function wp_doing_cron() {
		return false;
	}
}

/** The options table, as far as the lease's compare-and-set queries go. */
class MurtaddTestLeaseDb {
	public $options = 'wp_options';
	public function prepare( $sql, ...$args ) {
		return $args;
	}
	public function get_var( $args ) {
		return $GLOBALS['murtadd_options'][ $args[0] ] ?? null;
	}
	public function query( $args ) {
		if ( 2 === count( $args ) ) {
			list( $name, $held ) = $args;
			if ( ! array_key_exists( $name, $GLOBALS['murtadd_options'] ) || (string) $GLOBALS['murtadd_options'][ $name ] !== $held ) {
				return 0;
			}
			unset( $GLOBALS['murtadd_options'][ $name ] );
			return 1;
		}
		list( $next, $name, $held ) = $args;
		if ( ! array_key_exists( $name, $GLOBALS['murtadd_options'] ) || (string) $GLOBALS['murtadd_options'][ $name ] !== $held ) {
			return 0;
		}
		$GLOBALS['murtadd_options'][ $name ] = $next;
		return 1;
	}
}

final class LiveLogsTest extends TestCase {

	private $saved_wpdb;

	protected function setUp(): void {
		$GLOBALS['murtadd_options']    = array();
		$GLOBALS['murtadd_transients'] = array();
		$GLOBALS['murtadd_test_env']   = 'production';
		$this->saved_wpdb              = $GLOBALS['wpdb'] ?? null;
	}

	protected function tearDown(): void {
		$GLOBALS['wpdb'] = $this->saved_wpdb;
	}

	/* ---------- 410 for the spam addresses ---------- */

	/** @return array */
	public static function spam_addresses() {
		return array(
			'items, sthml'             => array( '/', 'items/61787410522324875176.sthml', true ),
			'items, other ending'      => array( '/', 'items/617.html', false ),
			'items on an inner page'   => array( '/doubts/', 'items/617.sthml', false ),
			'index.php sitemap'        => array( '/index.php/sitemap298.xml', '', true ),
			'index.php image sitemap'  => array( '/index.php/sitemapimages52.xml', '', true ),
			'image sitemap'            => array( '/sitemapimages4336.xml', '', true ),
			'contents'                 => array( '/contents/event/kansyasai/', '', true ),
			'goods.php'                => array( '/goods.php', '', true ),
			'the real sitemap index'   => array( '/sitemap_index.xml', '', false ),
			'the core sitemap'         => array( '/wp-sitemap.xml', '', false ),
			'the retired HTML sitemap' => array( '/sitemap/', '', false ),
			'a search'                 => array( '/', 's=contents', false ),
			'the home page'            => array( '/', '', false ),
		);
	}

	#[\PHPUnit\Framework\Attributes\DataProvider( 'spam_addresses' )]
	public function test_presets( $path, $query, $expected ) {
		$this->assertSame( $expected, murtadd_gone_matches( $path, murtadd_gone_presets(), $query ) );
	}

	public function test_presets_are_on_by_default_and_follow_the_switch() {
		$this->assertCount( 5, murtadd_gone_presets() );
		$GLOBALS['murtadd_options']['murtadd_upkeep'] = array( 'gone_presets' => 0 );
		$this->assertSame( array(), murtadd_gone_presets() );
		$this->assertSame( 0, murtadd_sanitize_upkeep( array() )['gone_presets'] );
		$this->assertSame( 1, murtadd_sanitize_upkeep( array( 'gone_presets' => '1' ) )['gone_presets'] );
	}

	public function test_index_php_permalinks_keep_their_sitemaps() {
		$GLOBALS['murtadd_options']['permalink_structure'] = '/index.php/%postname%/';
		$this->assertNotContains( '/index.php/sitemap*', murtadd_gone_presets() );
		$this->assertContains( '/?items/*.sthml', murtadd_gone_presets() );
	}

	public function test_no_legacy_address_is_a_preset() {
		foreach ( array_keys( murtadd_legacy_redirect_map() ) as $slug ) {
			$this->assertFalse( murtadd_gone_matches( '/' . $slug . '/', murtadd_gone_presets() ), $slug );
		}
	}

	public function test_spam_runs_before_the_legacy_and_canonical_redirects() {
		$spam   = murtadd_test_hook_priority( 'template_redirect', 'murtadd_send_gone_spam' );
		$legacy = murtadd_test_hook_priority( 'template_redirect', 'murtadd_legacy_redirects' );
		$this->assertNotNull( $spam );
		$this->assertLessThan( $legacy, $spam );
		$this->assertLessThan( 10, $spam, 'redirect_canonical runs at 10.' );
		$src = (string) file_get_contents( dirname( __DIR__ ) . '/inc/gone.php' );
		$this->assertStringContainsString( 'if ( ! is_404() && ! is_front_page() && ! is_home() ) {', $src, 'A real post, page or archive is never answered 410.' );
	}

	public function test_owner_query_and_wildcard_patterns() {
		$clean = murtadd_gone_clean( "/?Items/*.sthml\n/feed-*.xml\n/?p=*\n/?pa*\n/?*=1\n/?utm_source=x\n/?s=casino\n/shop/?ref=*\n/?cat=1" );
		$this->assertSame( "/?items/*.sthml\n/feed-*.xml", $clean, 'Keys WordPress or tracking links use are refused.' );
		$patterns = explode( "\n", $clean );
		$this->assertTrue( murtadd_gone_matches( '/', $patterns, 'items/1.sthml' ) );
		$this->assertFalse( murtadd_gone_matches( '/', $patterns, '' ), 'A query pattern needs a query.' );
		$this->assertTrue( murtadd_gone_matches( '/feed-12.xml', $patterns ) );
		$this->assertFalse( murtadd_gone_matches( '/feed.xml', $patterns ) );
		$this->assertTrue( murtadd_gone_matches( '/x', array( '/x' ), 'utm_source=1' ), 'A path pattern ignores the query.' );
	}

	/* ---------- The .htaccess blocks ---------- */

	/**
	 * The RewriteRule patterns of a block, as PCRE.
	 *
	 * @param string[] $lines Block lines.
	 * @return string[]
	 */
	private function rules( $lines ) {
		$rx = array();
		foreach ( $lines as $line ) {
			if ( preg_match( '/^RewriteRule (\S+) - \[F,L(,NC)?\]$/', $line, $m ) ) {
				$rx[] = '#' . $m[1] . '#' . ( empty( $m[2] ) ? '' : 'i' );
			}
		}
		return $rx;
	}

	private function denied( $rx, $path ) {
		foreach ( $rx as $r ) {
			if ( preg_match( $r, $path ) ) {
				return true;
			}
		}
		return false;
	}

	public function test_root_php_block() {
		$rx = $this->rules( murtadd_sec_root_lines( '' ) );
		foreach ( array( 'ox-a1b2.php', 'OX-A1B2.PHP', 'goods.php', 'wp.php', 'wp-config.php', 'admin.php', 'this_is_a_new_hello_world.php' ) as $p ) {
			$this->assertTrue( $this->denied( $rx, $p ), $p );
		}
		foreach ( array( 'index.php', 'wp-login.php', 'wp-cron.php', 'wp-comments-post.php', 'xmlrpc.php', 'wp-signup.php', 'wp-activate.php', 'wp-trackback.php', 'wp-links-opml.php', 'wp-mail.php', 'wp-load.php', 'wp-blog-header.php', 'wp-admin/admin-ajax.php', 'wp-content/plugins/x/y.php', 'style.css', 'index.php5' ) as $p ) {
			$this->assertFalse( $this->denied( $rx, $p ), $p );
		}
		$sub = $this->rules( murtadd_sec_root_lines( 'wp/' ) );
		$this->assertTrue( $this->denied( $sub, 'wp/ox-1.php' ) );
		$this->assertTrue( $this->denied( $sub, 'ox-1.php' ) );
		$this->assertFalse( $this->denied( $sub, 'wp/wp-login.php' ) );
		$this->assertFalse( $this->denied( $sub, 'index.php' ) );
	}

	public function test_root_php_names_are_safe() {
		$this->assertContains( 'index', murtadd_sec_root_php_names() );
		$this->assertSame( murtadd_sec_root_php_names(), array_values( array_filter( murtadd_sec_root_php_names(), static fn( $n ) => (bool) preg_match( '/^[a-z0-9][a-z0-9._-]*$/', $n ) ) ) );
	}

	public function test_core_files_block() {
		$rx = $this->rules( murtadd_sec_core_lines( '' ) );
		foreach ( array( 'wp-includes/blocks/rss.php', 'wp-includes/version.php', 'wp-settings.php', 'wp-config-sample.php', 'readme.html', 'wp-admin/install.php' ) as $p ) {
			$this->assertTrue( $this->denied( $rx, $p ), $p );
		}
		foreach ( array( 'wp-includes/js/tinymce/wp-tinymce.php', 'wp-includes/ms-files.php', 'wp-includes/css/dashicons.min.css', 'wp-login.php', 'wp-content/themes/murtadd/readme.html' ) as $p ) {
			$this->assertFalse( $this->denied( $rx, $p ), $p );
		}
		$this->assertSame( '', murtadd_sec_core_prefix( '/srv/site', '/srv/site/' ) );
		$this->assertSame( 'wp/', murtadd_sec_core_prefix( '/srv/site/', '/srv/site/wp/' ) );
		$this->assertNull( murtadd_sec_core_prefix( '/srv/site/', '/opt/wp/' ) );
	}

	public function test_server_blocks_and_their_constants() {
		$blocks = murtadd_sec_server_blocks( '' );
		$this->assertSame( murtadd_sec_core_lines( '' ), $blocks['Murtadd: deny direct core files'] );
		$this->assertSame( murtadd_sec_root_lines( '' ), $blocks['Murtadd: only WordPress root PHP'] );
		$this->assertSame( array( array(), array() ), array_values( murtadd_sec_server_blocks( null ) ), 'WordPress outside the .htaccess folder: no blocks.' );
		$src = (string) file_get_contents( dirname( __DIR__ ) . '/inc/security.php' );
		$this->assertStringContainsString( "defined( 'MURTADD_ALLOW_ROOT_PHP' ) && MURTADD_ALLOW_ROOT_PHP", $src );
		$this->assertStringContainsString( "defined( 'MURTADD_ALLOW_CORE_FILES' ) && MURTADD_ALLOW_CORE_FILES", $src );
		$this->assertStringContainsString( 'insert_with_markers( $file, MURTADD_SEC_ROOT_MARKER, array() );', $src, 'A theme switch takes the root block out.' );
	}

	public function test_unknown_root_php_is_listed() {
		$dir = sys_get_temp_dir() . '/murtadd-root-' . uniqid();
		mkdir( $dir );
		foreach ( array( 'index.php', 'wp-login.php', 'wp-config.php', 'wp-settings.php', 'ox-9f3.php', 'Shell.PHP', 'readme.html' ) as $f ) {
			file_put_contents( "$dir/$f", 'x' );
		}
		$this->assertSame( array( 'Shell.PHP', 'ox-9f3.php' ), murtadd_sec_unknown_root_php( $dir ) );
		array_map( 'unlink', glob( "$dir/*" ) );
		rmdir( $dir );
	}

	/* ---------- Development files ---------- */

	public function test_dev_entries() {
		$dir = sys_get_temp_dir() . '/murtadd-dev-' . uniqid();
		foreach ( array( '.git/objects', '.github/workflows', '.claude', '.well-known', 'tests', 'config', 'bin', 'docs', 'inc', 'vendor/parsedown', 'vendor/phpunit/phpunit', 'vendor/bin' ) as $d ) {
			mkdir( "$dir/$d", 0755, true );
		}
		foreach ( array( '.git/objects/ab', '.gitignore', '.htaccess', '.user.ini', 'composer.json', 'composer.lock', 'phpunit.xml.dist', 'vendor/autoload.php', 'vendor/.htaccess', 'vendor/parsedown/Parsedown.php', 'docs/readme.md', 'bin/x.php' ) as $f ) {
			file_put_contents( "$dir/$f", 'x' );
		}
		$list = array_map( static fn( $p ) => substr( $p, strlen( $dir ) + 1 ), murtadd_dev_entries( $dir ) );
		sort( $list );
		$this->assertSame( array( '.claude', '.git', '.github', '.gitignore', 'composer.json', 'composer.lock', 'config', 'phpunit.xml.dist', 'tests', 'vendor/autoload.php', 'vendor/bin', 'vendor/phpunit' ), $list );
		foreach ( murtadd_dev_entries( $dir ) as $p ) {
			murtadd_remove_tree( $p );
		}
		$this->assertFileExists( "$dir/vendor/parsedown/Parsedown.php" );
		$this->assertFileExists( "$dir/vendor/.htaccess" );
		$this->assertFileExists( "$dir/docs/readme.md" );
		$this->assertFileExists( "$dir/.htaccess" );
		$this->assertFileDoesNotExist( "$dir/.git" );
		$this->assertFileDoesNotExist( "$dir/vendor/phpunit" );
		$GLOBALS['murtadd_test_env'] = 'development';
		$this->assertSame( array(), murtadd_dev_entries( $dir ) );
		$GLOBALS['murtadd_test_env'] = 'local';
		$this->assertSame( array(), murtadd_dev_entries( $dir ) );
		$GLOBALS['murtadd_test_env'] = 'production';
		murtadd_remove_tree( $dir );
		$this->assertFileDoesNotExist( $dir );
	}

	public function test_the_lease() {
		$GLOBALS['wpdb'] = new MurtaddTestLeaseDb();
		$stale           = ( time() - 600 ) . '.123456';
		$GLOBALS['murtadd_options']['murtadd_dev_files_lock'] = $stale;
		$this->assertTrue( murtadd_swap_lock( 'murtadd_dev_files_lock', $stale, time() . '.1' ) );
		$this->assertFalse( murtadd_swap_lock( 'murtadd_dev_files_lock', $stale, time() . '.2' ), 'Of two requests that read the same stale lease, one wins.' );
		$this->assertFalse( murtadd_release_lock( 'murtadd_dev_files_lock', $stale ), 'A lost lease frees nothing.' );
		$this->assertTrue( murtadd_release_lock( 'murtadd_dev_files_lock', time() . '.1' ) );

		$GLOBALS['murtadd_cleanup_lease']                      = ( time() - 120 ) . '.222222';
		$GLOBALS['murtadd_options']['murtadd_dev_files_lock'] = $GLOBALS['murtadd_cleanup_lease'];
		murtadd_renew_cleanup_lease();
		$this->assertLessThan( 60, time() - (int) $GLOBALS['murtadd_options']['murtadd_dev_files_lock'], 'A running clean-up renews its lease.' );
		unset( $GLOBALS['murtadd_cleanup_lease'] );
	}

	public function test_a_fresh_lease_keeps_a_second_request_out_and_a_dev_site_runs_nothing() {
		$GLOBALS['wpdb'] = new MurtaddTestLeaseDb();
		$GLOBALS['murtadd_options']['murtadd_dev_files_lock'] = time() . '.111111';
		murtadd_cleanup_on_version_change();
		$this->assertArrayNotHasKey( 'murtadd_dev_files_version', $GLOBALS['murtadd_options'] );
		$this->assertSame( time() . '.111111', $GLOBALS['murtadd_options']['murtadd_dev_files_lock'] );
		unset( $GLOBALS['murtadd_options']['murtadd_dev_files_lock'] );
		$GLOBALS['murtadd_test_env'] = 'development';
		murtadd_cleanup_on_version_change();
		$this->assertSame( array(), $GLOBALS['murtadd_options'] );
		$src = (string) file_get_contents( dirname( __DIR__ ) . '/inc/dev-files.php' );
		$this->assertStringContainsString( "add_action( 'init', 'murtadd_cleanup_on_version_change', 1 );", $src );
		$this->assertStringContainsString( "defined( 'MURTADD_KEEP_DOTFILES' ) && MURTADD_KEEP_DOTFILES", $src );
	}

	/* ---------- A theme caught half uploaded ---------- */

	public function test_every_inc_file_is_checked_before_any_loads() {
		$src = (string) file_get_contents( dirname( __DIR__ ) . '/functions.php' );
		$this->assertStringNotContainsString( "require_once get_stylesheet_directory() . '/inc/cpt-doubt.php';", $src );
		$this->assertStringContainsString( 'catch ( Throwable $murtadd_error )', $src );
		$listed = array_merge( murtadd_theme_files(), array( 'seed-data' ) );
		foreach ( glob( dirname( __DIR__ ) . '/inc/*.php' ) as $file ) {
			$this->assertContains( basename( $file, '.php' ), $listed, basename( $file ) );
		}
		$this->assertLessThan( array_search( 'dev-files', murtadd_theme_files(), true ), array_search( 'security', murtadd_theme_files(), true ) );
		$this->assertStringContainsString( "header( 'Retry-After: 300' );", $src );
		$this->assertStringContainsString( "'HTTP/1.1 503 Service Unavailable', true, 503", $src );
	}

	public function test_a_missing_file_is_logged_once() {
		$log = sys_get_temp_dir() . '/murtadd-log-' . uniqid();
		$old = ini_set( 'error_log', $log );
		murtadd_incomplete_theme( 'inc/gone.php is missing' );
		murtadd_incomplete_theme( 'inc/gone.php is missing' );
		murtadd_incomplete_theme( 'inc/seo-title.php is missing' );
		ini_set( 'error_log', (string) $old );
		$lines = file_exists( $log ) ? preg_grep( '/incomplete or being uploaded/', file( $log ) ) : array();
		@unlink( $log );
		$this->assertCount( 2, $lines );
	}

	public function test_who_gets_the_page() {
		$GLOBALS['murtadd_test_is_admin'] = true;
		$this->assertFalse( murtadd_incomplete_serves( false, 'apache2handler' ), 'A missing file leaves the admin alone.' );
		$this->assertTrue( murtadd_incomplete_serves( true, 'apache2handler' ), 'A file that broke part-way sends the admin the page.' );
		$GLOBALS['murtadd_test_is_admin'] = false;
		$this->assertTrue( murtadd_incomplete_serves( false, 'fpm-fcgi' ) );
		$this->assertFalse( murtadd_incomplete_serves( true, 'cli' ) );
		$_SERVER['REQUEST_URI'] = '/wp-login.php';
		$this->assertFalse( murtadd_incomplete_serves( true, 'apache2handler' ) );
		unset( $_SERVER['REQUEST_URI'] );
	}

	public function test_an_entry_that_cannot_go_is_retried_later() {
		$theme = sys_get_temp_dir() . '/murtadd-theme-' . uniqid();
		mkdir( "$theme/.stuck", 0755, true );
		file_put_contents( "$theme/.stuck/f", 'x' );
		$root  = 0 === posix_geteuid();
		$stuck = $root ? ( false !== exec( 'chattr +i ' . escapeshellarg( "$theme/.stuck/f" ) . ' 2>/dev/null', $out, $rc ) && 0 === $rc ) : chmod( "$theme/.stuck", 0555 );
		$free  = static function () use ( $root, $theme ) {
			$root ? exec( 'chattr -i ' . escapeshellarg( "$theme/.stuck/f" ) . ' 2>/dev/null' ) : chmod( "$theme/.stuck", 0755 );
		};
		if ( ! $stuck ) {
			murtadd_remove_tree( $theme );
			$this->markTestSkipped( 'No way to make a file that cannot be removed here.' );
		}
		$GLOBALS['wpdb']                   = new MurtaddTestLeaseDb();
		$GLOBALS['murtadd_test_theme_dir'] = $theme;
		try {
			murtadd_cleanup_on_version_change();
			$lock = (string) ( $GLOBALS['murtadd_options']['murtadd_dev_files_lock'] ?? '' );
			$this->assertArrayNotHasKey( 'murtadd_dev_files_version', $GLOBALS['murtadd_options'] );
			$this->assertNotSame( '', $lock );
			$this->assertLessThan( 60, time() - (int) $lock, 'The lease is kept fresh, so the next try waits.' );
			$free();
			murtadd_cleanup_on_version_change();
			$this->assertFileExists( "$theme/.stuck/f", 'No second try while the lease is fresh.' );
			$GLOBALS['murtadd_options']['murtadd_dev_files_lock'] = ( time() - 600 ) . substr( $lock, strpos( $lock, '.' ) );
			murtadd_cleanup_on_version_change();
			$this->assertFileDoesNotExist( "$theme/.stuck" );
			$this->assertSame( MURTADD_VERSION, $GLOBALS['murtadd_options']['murtadd_dev_files_version'] );
			$this->assertArrayNotHasKey( 'murtadd_dev_files_lock', $GLOBALS['murtadd_options'] );
		} finally {
			$free();
			unset( $GLOBALS['murtadd_test_theme_dir'] );
			murtadd_remove_tree( $theme );
		}
	}

	/* ---------- Scripts that run only from the command line ---------- */

	public function test_every_script_has_the_cli_guard() {
		foreach ( array_merge( glob( __DIR__ . '/*.php' ), glob( dirname( __DIR__ ) . '/bin/*.php' ) ) as $file ) {
			$head = (string) file_get_contents( $file, false, null, 0, 2000 );
			$this->assertStringContainsString( "if ( 'cli' !== PHP_SAPI ) {", $head, basename( $file ) );
		}
	}
}
