<?php
return [
	'sitemapFromThirdPartyShouldCreateJob' => [
		'config' => [
			'old_values' => [
				'manual_preload' => false
			],
			'values' => [
				'manual_preload' => true
			],
			'return_sitemaps' => [
				'http://example.com'
			]
		],
		'expected' => [
			'exist' => true,
			'sitemaps' => [
				'http://example.com'
			]
		]
	],
	'noChangeShouldNotCreateAJob' => [
		'config' => [
			'old_values' => [
				'manual_preload' => true
			],
			'values' => [
				'manual_preload' => true
			],
			'return_sitemaps' => [
				'http://example.com'
			]
		],
		'expected' => [
			'exist' => false,
			'sitemaps' => [
				'http://example.com'
			]
		]
	],
	'disableShouldNotCreateAJob' => [
		'config' => [
			'old_values' => [
				'manual_preload' => true
			],
			'values' => [
				'manual_preload' => false
			],
			'return_sitemaps' => [
				'http://example.com'
			]
		],
		'expected' => [
			'exist' => false,
			'sitemaps' => [
				'http://example.com'
			]
		]
	],
	'sitemapFromWordPressShouldCreateJob' => [
		'config' => [
			'old_values' => [
				'manual_preload' => false
			],
			'values' => [
				'manual_preload' => true
			],
			'return_sitemaps' => []
		],
		'expected' => [
			'exist' => true,
			'sitemaps' => [
				'http://example.org/wp-sitemap.xml'
			]
		]
	]
];
