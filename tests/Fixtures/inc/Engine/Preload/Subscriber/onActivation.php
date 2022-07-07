<?php
return [
	'activateShouldLaunchSitemapFetching' => [
		'config' => [
			'is_enabled' => true,
			'return_sitemaps' => [
				'http://example.org/sitemap.xml'
			]
		],
		'expected' => [
			'exist' => true,
			'sitemaps' => [
				'http://example.org/sitemap.xml'
			]
		]
	],
	'disactivateShouldDoNothing' => [
		'config' => [
			'is_enabled' => false,
			'return_sitemaps' => [
				'http://example.org/sitemap.xml'
			]
		],
		'expected' => [
			'exist' => false,
			'sitemaps' => [
				'http://example.org/sitemap.xml'
			]
		]
	]
];
