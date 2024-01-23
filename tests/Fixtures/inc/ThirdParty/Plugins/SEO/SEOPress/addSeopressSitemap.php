<?php
return [
	'enableShouldAddSitemap' => [
		'config' => [
			'home_url' => 'http://example.org',
			'sitemaps' => []
		],
		'expected' => [
			'http://example.org/sitemaps.xml'
		]
	]
];
