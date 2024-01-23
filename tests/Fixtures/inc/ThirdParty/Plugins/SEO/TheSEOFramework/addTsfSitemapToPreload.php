<?php
return [
	'disabledShouldReturnSame' => [
		'config' => [
			'is_tsf_active' => false,
			'sitemaps' => [],
			'version' => '4.0',
			'sitemap' => 'sitemap',
			'endpoints' => [

			],
			'url' => 'url'
		],
		'expected' => [

		]
	],
	'version3ShouldReturnSitemap' => [
		'config' => [
			'is_tsf_active' => true,
			'sitemaps' => [],
			'version' => '3.0',
			'sitemap' => 'sitemap',
			'endpoints' => [

			],
			'url' => 'url'
		],
		'expected' => [
			'sitemap',
		]
	],
	'version4ShouldReturnSitemap' => [
		'config' => [
			'is_tsf_active' => true,
			'sitemaps' => [],
			'version' => '4.0',
			'sitemap' => 'sitemap',
			'endpoints' => [
				10 => [
					'robots' => 'robots'
				]
			],
			'url' => 'url'
		],
		'expected' => [
			'url'
		]
	]
];
