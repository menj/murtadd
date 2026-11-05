<?php
/**
 * Self-test for inc/security-detector.php. Run: php tests/security-detector-test.php
 *
 * Exit code 1 when a seed or content file it reads is missing or is not valid
 * JSON. Exit code 0 when every starter item (doubts, rebuttals, guides, pages,
 * topics and glossary terms in inc/data) scores exactly 0, every clean sample
 * stays below the threshold, and every attack sample is flagged.
 *
 * Not a PHPUnit file: tests/SecurityTest.php runs the same checks in the suite.
 *
 * @package murtadd
 */

// Run from the command line only (phpdbg included): on a web server this file does nothing.
if ( ! in_array( PHP_SAPI, array( 'cli', 'phpdbg' ), true ) ) {
	exit;
}

if ( ! defined( 'MURTADD_SECURITY_TEST' ) ) {
	define( 'MURTADD_SECURITY_TEST', true );
}
require_once dirname( __DIR__ ) . '/inc/security-detector.php';

/**
 * Every seeded title, slug and body, as rows of title, slug, text.
 *
 * @return array<string,array{0:string,1:string,2:string}>
 */
function murtadd_sec_test_seed_rows() {
	$dir  = dirname( __DIR__ ) . '/inc/data';
	$read = static function ( $file ) use ( $dir ) {
		$path = $dir . '/' . $file;
		if ( ! is_readable( $path ) ) {
			throw new RuntimeException( 'MISSING: inc/data/' . $file );
		}
		$data = json_decode( (string) file_get_contents( $path ), true );
		if ( ! is_array( $data ) ) {
			throw new RuntimeException( 'INVALID JSON: inc/data/' . $file . ' (' . json_last_error_msg() . ')' );
		}
		return $data;
	};
	$sources = static function ( $list ) {
		$out = '';
		foreach ( (array) $list as $s ) {
			$out .= ' ' . $s['citation_text'] . ' <a href="' . $s['url'] . '">' . $s['url'] . '</a>';
		}
		return $out;
	};
	$rows = array();
	foreach ( $read( 'seed/doubts.json' ) as $d ) {
		$rows[ 'doubt ' . $d['slug'] ] = array( $d['title'], $d['slug'], $d['statement'] . ' ' . $d['response'] );
	}
	foreach ( $read( 'seed/rebuttals.json' ) as $r ) {
		$rows[ 'rebuttal ' . $r['slug'] ] = array( $r['title'], $r['slug'], $r['claim'] . ' ' . $r['body'] . $sources( $r['sources'] ) );
	}
	foreach ( $read( 'seed/guides.json' ) as $g ) {
		$rows[ 'guide ' . $g['slug'] ] = array( $g['title'], $g['slug'], $g['excerpt'] . ' ' . $g['body'] . $sources( $g['sources'] ) . ' ' . ( $g['focus_keyword'] ?? '' ) );
	}
	foreach ( $read( 'seed/pages.json' ) as $slug => $body ) {
		$rows[ 'page ' . $slug ] = array( ucfirst( str_replace( '-', ' ', $slug ) ), $slug, (string) $body );
	}
	foreach ( $read( 'seed/topics.json' ) as $slug => $name ) {
		$rows[ 'topic ' . $slug ] = array( $name, $slug, '' );
	}
	$glossary = $read( 'glossary.json' );
	foreach ( isset( $glossary['terms'] ) ? (array) $glossary['terms'] : array() as $t ) {
		$rows[ 'glossary ' . $t['slug'] ] = array( '', $t['slug'], implode( ' ', array_filter( $t, 'is_string' ) ) . ' ' . implode( ' ', (array) ( $t['aliases'] ?? array() ) ) );
	}
	return $rows;
}

/**
 * Titles that read like the site's own subjects and must never be held.
 *
 * @return string[]
 */
function murtadd_sec_test_clean_samples() {
	return array(
		'Bethlehem and Bethel in Genesis', 'The Hebrew alphabet', 'Bet, the second letter of the Hebrew alphabet', 'Beth Din and the rabbinic courts',
		'Between the Testaments', 'A better reading of the hadith', 'What is at stake when a child leaves Islam', 'Stake', 'A review of the evidence',
		'Book review: The Apostasy Wars', 'A bonus chapter on the riddah wars', 'The bonus page', 'A time slot for the Friday sermon', 'Elizabeth and Bethany',
		'Kumar and the Vedas', 'The crown of the Dome of the Rock', 'Judith in the Apocrypha', 'Is gambling forbidden in Islam? Maysir in the Quran',
		'Wine and games of chance in Surah al-Maidah', 'The odds that the Quran was copied', 'Spinning the hadith', 'Pinpointing the date of the hijra',
	);
}

/**
 * Titles and slugs from the spam that reached the sister site, and others like them.
 *
 * @return array<int,array{0:string,1:string}>
 */
function murtadd_sec_test_spam_samples() {
	$titles = array(
		'Bet Big Win Big with Bodog Sports Edge', 'Exclusive Coolbet Bonus Code Unlocks Big Wins', 'Скачать Пинап КЗ: Официальный сайт',
		'Krok po kroku: Kompleksowy przewodnik po Pelican Casino', 'Live-Roulette bei Mr Pacho', 'Crash-Games als neuer Trend',
		'Persoonlijke ervaringen met top 10 online casino’s in Nederland 2026', 'Panduan Lengkap 1xBet Download', 'Pusulabet 2026 Giriş Adresi',
		'Sweepstakes Casinos Explained', 'Stake Casino Australia', 'Sportwetten-Analyse vor dem Spiel', 'Connection check jk5g5zw7', 'cl-probe',
		'Mostbet giriş', 'Surfplay review', 'Crownplay bonus', 'Trino Casino', 'Syndicate Casino login', 'Stellar Spins', 'Starda официальный сайт',
		'Slotspalace', 'Sportaza', 'Spybet', 'Squeenaud', 'Spinjo', 'Spellwin', 'Spinsup', 'Spinch', 'Spin Samurai', 'Slots Gallery',
		'Najboljše igralne avtomate v Sloveniji', 'Casino Oyunlarında Bankroll Yönetimi', 'Glücksspiel in der Schweiz', 'Ausländische Online Casinos',
		'Vavada зеркало', 'Melbet app', 'Betify login', 'Casinozer avis', 'AureonPlay', 'PlatinCasino', 'True Blue Casino', 'SydSpin', 'TCL99',
		'Vegas Stars', 'Surge Casino', '1win',
	);
	$out = array();
	foreach ( $titles as $t ) {
		$out[] = array( $t, '' );
	}
	foreach ( array( 'casinos-online-mas-seguros-espana-2026', 'zahranicne-stavkove-kancelarie-na-slovensku-3', 'pin-ap-kazino-oficialnyj-sajt', 'kto-brasil-cassino-online-e-apostas' ) as $slug ) {
		$out[] = array( 'Untitled', $slug );
	}
	return $out;
}

/**
 * Run every check.
 *
 * @return array{fail:int,lines:string[],seeded:int,spam:int}
 */
function murtadd_sec_test_run() {
	$fail  = 0;
	$lines = array();
	try {
		$rows = murtadd_sec_test_seed_rows();
	} catch ( RuntimeException $e ) {
		// A seed or content file the test expects is missing or unreadable:
		// fail, since nothing in it was checked.
		$rows    = array();
		$lines[] = $e->getMessage();
		++$fail;
	}
	foreach ( $rows as $key => $row ) {
		$r = murtadd_spam_score( $row[0], $row[1], $row[2] );
		if ( $r['score'] > 0 ) {
			++$fail;
			$lines[] = 'FALSE POSITIVE (score ' . $r['score'] . '): ' . $key . ' ' . implode( '; ', $r['reasons'] );
		}
	}
	foreach ( murtadd_sec_test_clean_samples() as $t ) {
		if ( murtadd_spam_score( $t, '', '' )['spam'] ) {
			++$fail;
			$lines[] = 'FALSE POSITIVE sample: ' . $t;
		}
	}
	$spam = murtadd_sec_test_spam_samples();
	foreach ( $spam as $s ) {
		$r = murtadd_spam_score( $s[0], $s[1], '' );
		if ( ! $r['spam'] ) {
			++$fail;
			$lines[] = 'MISSED: ' . $s[0] . ' ' . $s[1] . ' (score ' . $r['score'] . ')';
		}
	}
	if ( ! murtadd_spam_score( 'A guide', '', '<p>See <a href="https://best-casino.example/">here</a></p>' )['spam'] ) {
		++$fail;
		$lines[] = 'MISSED: gambling link';
	}
	return array( 'fail' => $fail, 'lines' => $lines, 'seeded' => count( $rows ), 'spam' => count( $spam ) + 1 );
}

if ( PHP_SAPI === 'cli' && isset( $argv[0] ) && realpath( $argv[0] ) === __FILE__ ) {
	$result = murtadd_sec_test_run();
	echo implode( "\n", $result['lines'] ) . ( $result['lines'] ? "\n" : '' );
	echo 'Starter items, terms and glossary entries checked: ' . $result['seeded'] . "\n";
	echo 'Spam samples checked: ' . $result['spam'] . "\n";
	echo $result['fail'] ? 'FAILED: ' . $result['fail'] . "\n" : "PASS\n";
	exit( $result['fail'] ? 1 : 0 );
}
