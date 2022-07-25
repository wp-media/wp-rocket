<?php
return [
	'shouldCreateTaskForFilterIfPresent' => [
		'config' => [
			'sitemaps' => [],
			'filter_sitemaps' => [
				'url'
			],
			'home_url' => 'home_url',
			'wp_sitemap' => 'sitemap',
		],
		'expected' => [
			'transient' => true,
		]
	],
	'shouldCreateTaskFOrSitemapIfNothingFromFilter' => [
		'config' => [
			'sitemaps' => [],
			'filter_sitemaps' => [],
			'home_url' => 'home_url',
			'wp_sitemap' => 'url',
		],
		'expected' => [
			'transient' => true,
		]
	],
	'shouldCreateNoTaskIfNoSitemap' => [
		'config' => [
			'sitemaps' => [],
			'filter_sitemaps' => [],
			'home_url' => 'home_url',
			'wp_sitemap' => false,
		],
		'expected' => [
		]
	]
];
