<?php

return [
	'testExcludedExt' => [
		'url' => 'http://example.org/test.php',
		'expected' => 'http://example.org/test.php',
	],
	'testNoPath' => [
		'url' => 'http://example.org/',
		'expected' => 'http://example.org/',
	],
	'testExcludedFile' => [
		'url' =>'http://example.org/wp-content/uploads/post.css',
		'expected' => 'http://example.org/wp-content/uploads/post.css',
	],
	'testImage' => [
		'url' =>'http://example.org/wp-content/uploads/test.jpg',
		'expected' => 'https://cdn.example.org/wp-content/uploads/test.jpg',
	],
	'testCSS' => [
		'url' =>'http://example.org/wp-content/themes/twentynineteen/style.css',
		'expected' => 'https://cdn.example.org/wp-content/themes/twentynineteen/style.css',
	],
	'testJS' => [
		'url' =>'http://example.org/script.js',
		'expected' => 'https://cdn.example.org/script.js',
	],
	'testRelativePath' => [
		'url' =>'/wp-includes/jquery.js',
		'expected' => 'https://cdn.example.org/wp-includes/jquery.js',
	],
	'testNoScheme' => [
		'url' =>'//example.org/wp-content/uploads/podcast.mp4',
		'expected' => '//cdn.example.org/wp-content/uploads/podcast.mp4',
	],
	'testWithQueryString' => [
		'url' =>'http://example.org/style.css?ver=5.2.3',
		'expected' => 'https://cdn.example.org/style.css?ver=5.2.3',
	],
	'testWithHTTPS' => [
		'url' =>'https://example.org/style.css?ver=5.2.3',
		'expected' => 'https://cdn.example.org/style.css?ver=5.2.3',
	],
];
