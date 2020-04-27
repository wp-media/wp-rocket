<?php

$html_content = <<<HTML
<!doctype html>
<head>
	<title>Lorem Ipsum</title>
	<style>button{padding:0;border:none;background:none;cursor:pointer}</style>
</head>
<body>
	<h1>Lorem Ipsum</h1>
	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
</body>
HTML;

return [
	'vfs_dir' => 'wp-content/',

	'test_data' => [

		// Test overwriting contents in an existing file.
		[
			'file'    => 'wp-content/cache/busting/1/ga-123456.js',
			'content' => <<<JS
( ( document, window ) => {
    // do some cool stuff.

	function sendHTTPRequest( postData ) {
		const httpRequest = new XMLHttpRequest();

		httpRequest.open( 'POST', ajaxurl );
		httpRequest.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
		httpRequest.send( postData );

		return httpRequest;
	}
} )( document, window );
JS
			,
		],
		[
			'file'    => 'wp-content/cache/critical-css/1/front-page.php',
			'content' => 'button{padding:0;border:none;background:none;cursor:pointer}',
		],
		[
			'file'    => 'vfs://public/wp-content/advanced-cache.php',
			'content' => <<<PHP
<?php
defined( 'ABSPATH' ) || exit;

define( 'WP_ROCKET_ADVANCED_CACHE', true );

if ( ! defined( 'WP_ROCKET_CONFIG_PATH' ) ) {
	define( 'WP_ROCKET_CONFIG_PATH',       WP_CONTENT_DIR . '/wp-rocket-config/' );
}

PHP
			,
		],
		[
			'file'    => 'wp-content/cache/wp-rocket/index.html',
			'content' => $html_content,
		],

		// Test creating a new file.
		[
			'file'    => 'wp-content/cache/wp-rocket/newfile.html',
			'content' => $html_content,
		],
	],
];
