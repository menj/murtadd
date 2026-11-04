<?php
/**
 * Build the static error pages in errors/ for the web server's ErrorDocument
 * lines. Like the drop-ins they carry no inline style or script
 * (docs/ssot.md, rule 1): they link the theme's tokens and error-page
 * stylesheets, which the web server serves from the theme folder.
 * Run: php bin/build-error-pages.php (add --check to fail when a page is stale).
 *
 * @package murtadd
 */

$murtadd_root  = dirname( __DIR__ );
$murtadd_check = in_array( '--check', $argv, true );
$murtadd_css   = '/wp-content/themes/murtadd/assets/css/';
$murtadd_pages = array(
	'403' => array( 'Access denied', 'Error 403', array( 'This address is closed to visitors.', 'Every page of the site is open from the home page.' ), 'Go to the home page' ),
	'500' => array( 'Something went wrong', 'Error 500', array( 'A fault on our side stopped the page. It has been noted, and is not something you did.', 'Please try again shortly.' ), 'Try again' ),
	'502' => array( 'Bad gateway', 'Error 502', array( 'A server between you and the site gave a wrong answer.', 'Please try again shortly.' ), 'Try again' ),
	'503' => array( 'Back shortly', 'Temporarily unavailable', array( 'The site is busy or offline for a few minutes.', 'Please try again shortly. Nothing you were reading has gone anywhere.' ), 'Try again' ),
);
$murtadd_stale = 0;
if ( ! $murtadd_check && ! is_dir( $murtadd_root . '/errors' ) ) {
	mkdir( $murtadd_root . '/errors' );
}
foreach ( $murtadd_pages as $murtadd_code => $murtadd_p ) {
	$murtadd_lines = '';
	foreach ( $murtadd_p[2] as $murtadd_line ) {
		$murtadd_lines .= '<p>' . htmlspecialchars( $murtadd_line, ENT_QUOTES, 'UTF-8' ) . "</p>\n";
	}
	$murtadd_html = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>' . $murtadd_p[0] . ' | Murtadd</title>
<link rel="stylesheet" href="' . $murtadd_css . 'tokens.min.css">
<link rel="stylesheet" href="' . $murtadd_css . 'error-page.min.css">
</head>
<body class="murtadd-error">
<main class="murtadd-error-card">
<p class="murtadd-error-name">Murtadd<span class="murtadd-error-stop">.</span></p>
<p class="murtadd-error-status">' . $murtadd_p[1] . '</p>
<h1>' . $murtadd_p[0] . '</h1>
' . $murtadd_lines . '<a class="murtadd-error-again" href="/">' . $murtadd_p[3] . '</a>
</main>
</body>
</html>
';
	$murtadd_path = $murtadd_root . '/errors/' . $murtadd_code . '.html';
	if ( $murtadd_check ) {
		if ( ! is_readable( $murtadd_path ) || file_get_contents( $murtadd_path ) !== $murtadd_html ) {
			fwrite( STDERR, "stale: errors/$murtadd_code.html\n" );
			++$murtadd_stale;
		}
		continue;
	}
	file_put_contents( $murtadd_path, $murtadd_html );
	echo "wrote errors/$murtadd_code.html\n";
}
exit( $murtadd_stale ? 1 : 0 );
