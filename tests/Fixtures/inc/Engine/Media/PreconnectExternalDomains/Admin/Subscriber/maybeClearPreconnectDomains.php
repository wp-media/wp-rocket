<?php


return [
	'testShouldClearPreconnectDomainsWhenMinifyCssChanged' => [
		'config' => [
			'rows' => [
				[
					'url' => 'https://example.com',
					'is_mobile' => false,
					'domains' => 'example.com',
					'error_message' => '',
					'status' => 'active',
				],
			],
			'old' => [
				'minify_css' => true,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
			],
			'new' => [
				'minify_css' => false,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
			],
		],
		'expected' => [
			'row_count' => 0,
		],
	],
	'testShouldClearPreconnectDomainsWhenMinifyJsChanged' => [
		'config' => [
			'rows' => [
				[
					'url' => 'https://example.com',
					'is_mobile' => false,
					'domains' => 'example.com',
					'error_message' => '',
					'status' => 'active',
				],
			],
			'old' => [
				'minify_css' => true,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
			],
			'new' => [
				'minify_css' => true,
				'minify_js' => false,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
			],
		],
		'expected' => [
			'row_count' => 0,
		],
	],
	'testShouldClearPreconnectDomainsWhenExcludeCssChanged' => [
		'config' => [
			'rows' => [
				[
					'url' => 'https://example.com',
					'is_mobile' => false,
					'domains' => 'example.com',
					'error_message' => '',
					'status' => 'active',
				],
			],
			'old' => [
				'minify_css' => true,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
			],
			'new' => [
				'minify_css' => true,
				'minify_js' => true,
				'exclude_css' => 'style.css',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
			],
		],
		'expected' => [
			'row_count' => 0,
		],
	],
	'testShouldClearPreconnectDomainsWhenExcludeJsChanged' => [
		'config' => [
			'rows' => [
				[
					'url' => 'https://example.com',
					'is_mobile' => false,
					'domains' => 'example.com',
					'error_message' => '',
					'status' => 'active',
				],
			],
			'old' => [
				'minify_css' => true,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
			],
			'new' => [
				'minify_css' => true,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => 'script.js',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
			],
		],
		'expected' => [
			'row_count' => 0,
		],
	],
	'testShouldClearPreconnectDomainsWhenCdnChanged' => [
		'config' => [
			'rows' => [
				[
					'url' => 'https://example.com',
					'is_mobile' => false,
					'domains' => 'example.com',
					'error_message' => '',
					'status' => 'active',
				],
			],
			'old' => [
				'minify_css' => true,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
			],
			'new' => [
				'minify_css' => true,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => '',
				'cdn_cnames' => 'example.com',
			],
		],
		'expected' => [
			'row_count' => 0,
		],
	],
	'testShouldClearPreconnectDomainsWhenCdnCnamesChanged' => [
		'config' => [
			'rows' => [
				[
					'url' => 'https://example.com',
					'is_mobile' => false,
					'domains' => 'example.com',
					'error_message' => '',
					'status' => 'active',
				],
			],
			'old' => [
				'minify_css' => true,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
			],
			'new' => [
				'minify_css' => true,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => '',
			],
		],
		'expected' => [
			'row_count' => 0,
		],
	],
	'testShouldClearPreconnectDomainsWhenHostFontLocallyChanged' => [
		'config' => [
			'rows' => [
				[
					'url' => 'https://example.com',
					'is_mobile' => false,
					'domains' => 'example.com',
					'error_message' => '',
					'status' => 'active',
				],
			],
			'old' => [
				'minify_css' => true,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
				'host_fonts_locally' => true
			],
			'new' => [
				'minify_css' => true,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
				'host_fonts_locally' => false,
			],
		],
		'expected' => [
			'row_count' => 0,
		],
	],
	'testShouldNotClearPreconnectDomains' => [
		'config' => [
			'rows' => [
				[
					'url' => 'https://example.com',
					'is_mobile' => false,
					'domains' => 'example.com',
					'error_message' => '',
					'status' => 'active',
				],
			],
			'old' => [
				'minify_css' => true,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
			],
			'new' => [
				'minify_css' => true,
				'minify_js' => true,
				'exclude_css' => '',
				'exclude_js' => '',
				'cdn' => 'https://cdn.example.com',
				'cdn_cnames' => 'example.com',
			],
		],
		'expected' => [
			'row_count' => 1,
		],
	],
];
