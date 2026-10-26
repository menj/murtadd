<?php
// Murtadd error page drop-in {{VERSION}}. Written by the Murtadd theme; safe to delete.
// WordPress shows this page when PHP stops with a fatal error. A file missing
// or half written in a theme or in WordPress itself is an upload in progress,
// answered as maintenance (503, back in a minute or two); anything else is a
// server error (500).
$murtadd_e        = error_get_last();
$murtadd_message  = is_array( $murtadd_e ) ? (string) $murtadd_e['message'] : '';
$murtadd_file     = is_array( $murtadd_e ) ? (string) $murtadd_e['file'] : '';
$murtadd_updating = '' !== $murtadd_message
	&& preg_match( '~/(wp-content/themes/|wp-content/plugins/|wp-includes/|wp-admin/)~', $murtadd_file . ' ' . $murtadd_message )
	&& preg_match( '~Failed opening required|No such file or directory|syntax error|Unclosed|Unterminated|unexpected end of file|Call to undefined (function|method)|Class "[^"]+" not found~', $murtadd_message );
if ( ! headers_sent() ) {
	if ( $murtadd_updating ) {
		header( 'HTTP/1.1 503 Service Unavailable', true, 503 );
		header( 'Retry-After: 120' );
	} else {
		header( 'HTTP/1.1 500 Internal Server Error', true, 500 );
	}
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'X-Robots-Tag: noindex' );
	header( 'Cache-Control: no-store, max-age=0' );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Back shortly | {{SITE_NAME}}</title>
<link rel="stylesheet" href="{{TOKENS_URL}}">
<link rel="stylesheet" href="{{CSS_URL}}">
</head>
<body class="murtadd-error">
<main class="murtadd-error-card">
<p class="murtadd-error-name">{{SITE_NAME}}<span class="murtadd-error-stop">.</span></p>
<?php if ( $murtadd_updating ) : ?>
<p class="murtadd-error-status">Updating</p>
<h1>Back shortly</h1>
<p>The site is part way through an update. It will be back in a minute or two.</p>
<?php else : ?>
<p class="murtadd-error-status">Something went wrong</p>
<h1>This page could not load</h1>
<p>A fault on our side stopped the page. It has been noted, and is not something you did.</p>
<?php endif; ?>
<p>Please try again shortly.</p>
<a class="murtadd-error-again" href="{{HOME_URL}}">Try again</a>
</main>
</body>
</html>
