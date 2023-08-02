<?php

return [
	'shouldReturnTranslatedUrls' => [
		'config' => [
			'url' => 'http://example.org/about',
			'post_type' => 'post',
			'regex' => '(.*)',
		],
		'expected' => [
			'/fr/about(.*)',
			'/us/about(.*)',
		],
	],
];
