<?php

return [
	'expectSameHtmlWhenNotAllowed' => [
		'config' => [
			'html' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/original.php'),
			'is_allowed' => false,
		],
		'expected' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/original.php'),

	],
	'expectFetchCssWithQueueAndReturnSameWhenEmptyUsedCss' => [
		'config' => [
			'html' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/original.php'),
			'is_allowed' => true,
			'home_url' => 'http://example.com',
			'is_mobile' => [
				'has_mobile_cache' => false,
				'is_caching_mobile_files' => false,
				'is_mobile' => false,
			],
			'get_existing_used_css' => [
				'used_css' => null
			],
			'create_new_job' => [
				'safelist' => [],
				'config' => [
					'treeshake' => 1,
					'rucss_safelist' => [],
					'is_mobile' => false,
					'is_home' => true,
				],
				'is_success_response' => true,
				'response' => [
					'code' => 200,
					'contents' => [
						'jobId' => 'id',
						'queueName' => 'name',
					]
				]
			]
		],
		'expected' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/original.php'),
	],
	'expectFetchCssWithJobAndReturnSameWhenEmptyUsedCssAndQueueFailed' => [
		'config' => [
			'html' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/original.php'),
			'is_allowed' => true,
			'home_url' => 'http://example.com',
			'is_mobile' => [
				'has_mobile_cache' => true,
				'is_caching_mobile_files' => true,
				'is_mobile' => true,
			],
			'get_existing_used_css' => [
				'used_css' => null
			],
			'create_new_job' => [
				'safelist' => [],
				'config' => [
					'treeshake' => 1,
					'rucss_safelist' => [],
					'is_mobile' => true,
					'is_home' => true,
				],
				'response' => [
					'code' => 400,
				]
			]
		],
		'expected' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/original.php'),
	],
	'expectedSameHTMlWhenNoEmptyUsedCSSAndWrongStatus' => [
		'config' => [
			'html' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/original.php'),
			'is_allowed' => true,
			'home_url' => 'http://example.com',
			'is_mobile' => [
				'has_mobile_cache' => true,
				'is_caching_mobile_files' => true,
				'is_mobile' => true,
			],
			'get_existing_used_css' => [
				'used_css' => (object) [
					'status' => 'failed',
					'id' => 'id',
				]
			],
		],
		'expected' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/original.php'),
	],
	'expectedSameHTMlWhenNoEmptyUsedCSSAndEmptyCSS' => [
		'config' => [
			'html' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/original.php'),
			'is_allowed' => true,
			'home_url' => 'http://example.com',
			'is_mobile' => [
				'has_mobile_cache' => true,
				'is_caching_mobile_files' => true,
				'is_mobile' => true,
			],
			'get_existing_used_css' => [
				'used_css' => (object) [
					'status' => 'completed',
					'css' => '',
					'id' => 'id',
				]
			],
		],
		'expected' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/original.php'),
	],
	'expectedFilteredHTMlWhenNoEmptyUsedCSS' => [
		'config' => [
			'is_allowed' => true,
			'home_url' => 'http://example.com',
			'is_mobile' => [
				'has_mobile_cache' => true,
				'is_caching_mobile_files' => true,
				'is_mobile' => true,
			],
			'get_existing_used_css' => [
				'used_css' => (object) [
					'status' => 'completed',
					'css' => 'h1{color:red;}',
					'id' => 'id',
				]
			],
			'apply_used_css' => [
				'test'
			],
			'html' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/original.php'),
		],
		'expected' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/filtred.php'),
	]
];
