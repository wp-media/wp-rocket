<?php
return [
	'disabledShouldReturnSame' => [
		'config' => [
			'is_enabled' => false,
			'aioseo_enabled' => false,
			'version' => 0,
			'options' => [
				'modules' => [
					'aiosp_feature_manager_options' => [
						'aiosp_feature_manager_enable_sitemap' => false
					]
				]
			],
			'home_url' => 'http://localhost',
			'sitemap' => 'sitemap',
			'sitemaps' => []
		],
		'expected' => []
	],
	'version3WithSitemapDisabledShouldReturnSame' => [
		'config' => [
			'is_enabled' => true,
			'aioseo_enabled' => false,
			'version' => 3,
			'options' => [
				'modules' => [
					'aiosp_feature_manager_options' => [
						'aiosp_feature_manager_enable_sitemap' => false
					]
				]
			],
			'home_url' => 'http://localhost',
			'sitemap' => 'sitemap',
			'sitemaps' => []
		],
		'expected' => []
	],
	'version3WithSitemapEnabledShouldAddSitemap' => [
		'config' => [
			'is_enabled' => true,
			'aioseo_enabled' => true,
			'version' => 3,
			'options' => [
				'modules' => [
					'aiosp_feature_manager_options' => [
						'aiosp_feature_manager_enable_sitemap' => 'on'
					]
				]
			],
			'home_url' => 'http://example.org/',
			'sitemap' => 'sitemap',
			'sitemaps' => [
			]
		],
		'expected' => [
			'http://example.org/sitemap.xml'
		]
	],
	'version4WithSitemapDisabledShouldReturnSame' => [
		'config' => [
			'is_enabled' => true,
			'aioseo_enabled' => false,
			'version' => 4,
			'options' => [
				'modules' => [
					'aiosp_feature_manager_options' => [
						'aiosp_feature_manager_enable_sitemap' => false
					]
				]
			],
			'home_url' => 'http://localhost',
			'sitemap' => 'sitemap',
			'sitemaps' => []
		],
		'expected' => []
	],
	'version4WithSitemapEnabledShouldAddSitemap' => [
		'config' => [
			'is_enabled' => true,
			'aioseo_enabled' => true,
			'version' => 4,
			'options' => [
				'modules' => [
					'aiosp_feature_manager_options' => [
						'aiosp_feature_manager_enable_sitemap' => 'on'
					]
				]
			],
			'home_url' => 'http://example.org/',
			'sitemap' => 'sitemap',
			'sitemaps' => [
			]
		],
		'expected' => [
			'http://example.org/sitemap.xml'
		]
	]
];
