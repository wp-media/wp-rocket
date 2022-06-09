<?php
return [
	'disableShouldReturnSame' => [
		'config' => [
			'is_enabled' => false,
			'home_url' => 'http://example.org',
			'sitemaps' => []
		],
		'expected' => [

		]
	],
	'enableShouldAddSitemap' => [
		'config' => [
			'is_enabled' => true,
			'home_url' => 'http://example.org',
			'sitemaps' => []
		],
		'expected' => [
			'http://example.org/sitemaps.xml'
		]
	]
];
