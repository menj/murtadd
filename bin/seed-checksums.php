<?php
/**
 * Write inc/data/seed/checksums.json: the MD5 of each seed file the content sync reads.
 *
 * Run it after any change to a seed file: php bin/seed-checksums.php
 * The sync stops removing anything when a file does not match, so a stale checksum is safe but blocks
 * removals; tests/ContentSyncTest.php fails until it is current.
 *
 * @package murtadd
 */

// Run from the command line only (phpdbg included): on a web server this file does nothing.
if ( ! in_array( PHP_SAPI, array( 'cli', 'phpdbg' ), true ) ) {
	exit;
}

$dir  = dirname( __DIR__ ) . '/inc/data/seed/';
$sums = array();
foreach ( array( 'rebuttals', 'doubts', 'guides', 'journal', 'supersedes' ) as $name ) {
	$sums[ $name ] = md5_file( $dir . $name . '.json' );
}
file_put_contents( $dir . 'checksums.json', json_encode( $sums, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n" );
echo "checksums.json written\n";
