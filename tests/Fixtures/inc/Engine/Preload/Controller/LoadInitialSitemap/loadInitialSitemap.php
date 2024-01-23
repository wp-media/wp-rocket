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
			'crawl_urls' => [
				'url1',
				'url2',
			],
			'add_crawl_urls' => [
				[['url' => 'home_url']],
				[['url' => 'url1']],
				[['url' => 'url2']],
			],
			'is_sitemap_activated' => true
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
			'crawl_urls' => [
				'url1',
				'url2',
			],
			'add_crawl_urls' => [
				[['url' => 'home_url']],
				[['url' => 'url1']],
				[['url' => 'url2']],
			],
			'is_sitemap_activated' => true
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
			'crawl_urls' => [
				'url1',
				'url2',
			],
			'add_crawl_urls' => [
				[['url' => 'home_url']],
				[['url' => 'url1']],
				[['url' => 'url2']],
			],
			'is_sitemap_activated' => false
		],
		'expected' => [
		]
	]
];
