<?php
// Murtadd error page drop-in {{VERSION}}. Written by the Murtadd theme; safe to delete.
// WordPress shows this page when it cannot reach the database.
if ( ! headers_sent() ) {
	header( 'HTTP/1.1 503 Service Unavailable', true, 503 );
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'Retry-After: 300' );
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
<p class="murtadd-error-status">Temporarily unavailable</p>
<h1>Back shortly</h1>
<p>The site cannot reach its database just now. This is a fault on our side, not yours.</p>
<p>Please try again in a few minutes.</p>
<a class="murtadd-error-again" href="{{HOME_URL}}">Try again</a>
</main>
</body>
</html>
