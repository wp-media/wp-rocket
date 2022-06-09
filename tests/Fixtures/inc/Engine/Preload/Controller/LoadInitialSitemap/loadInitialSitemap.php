<?php
return [
	'shouldCreateTaskForFilterIfPresent' => [
		'config' => [
			'sitemaps' => [],
			'filter_sitemaps' => [
				'url'
			],
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
			'wp_sitemap' => false,
		],
		'expected' => [
		]
	]
];
