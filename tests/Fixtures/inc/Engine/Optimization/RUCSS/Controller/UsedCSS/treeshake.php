<?php

return [
	'expectSameHtmlWhenNotAllowed' => [
		'config' => [
			'home_url' => 'http://example.com',
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
				'create_job' => true,
				'safelist' => [],
				'skipped_attr' => [],
				'config' => [
					'treeshake' => 1,
					'rucss_safelist' => [],
					'skip_attr' => [],
					'is_mobile' => false,
					'is_home' => true,
				],
				'is_success_response' => true,
				'response' => [
					'code' => 200,
					'contents' => [
						'jobId' => '',
						'queueName' => '',
					]
				]
			]
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
					'hash' => '1234',
					'status' => 'completed',
					'css' => '',
					'id' => 1,
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
					'hash' => '1234',
					'status' => 'completed',
					'css' => 'h1{color:red;}',
					'id' => 1,
				]
			],
			'apply_used_css' => [
				'test'
			],
			'html' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/original.php'),
		],
		'expected' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/filtered.php'),
	],
	'expectedFilteredHTMlWhenNoPreconnectGoogleAPI' => [
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
					'hash' => '1234',
					'status' => 'completed',
					'css' => 'h1{color:red;}',
					'id' => 1,
				]
			],
			'apply_used_css' => [
				'test'
			],
			'html' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/google_fonts.php'),
		],
		'expected' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/filtered.php'),
	],
	'expectedFilteredHTMlWhenNoEmptyUsedCSSExcludeAttr' => [
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
					'hash' => '1234',
					'status' => 'completed',
					'css' => 'h1{color:red;}',
					'id' => 1,
				]
			],
			'apply_used_css' => [
				'test'
			],
			'dynamic_lists'=> [
				'rucss_inline_atts_exclusions' => [
					'rocket-lazyload-inline-css'
				],
				'rucss_inline_content_exclusions' => [],
			],
			'html' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/original_exclude_attr.php'),
		],
		'expected' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/filtered_exclude_attr.php'),
	],
	'expectedFilteredHTMlWhenNoEmptyUsedCSSExcludeContent' => [
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
					'hash' => '1234',
					'status' => 'completed',
					'css' => 'h1{color:red;}',
					'id' => 1,
				]
			],
			'apply_used_css' => [
				'test'
			],
			'dynamic_lists' => [
				'rucss_inline_atts_exclusions' => [],
				'rucss_inline_content_exclusions' => [
					'#wpv-expandable-'
				]
			],
			'html' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/original_exclude_content.php'),
		],
		'expected' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/filtered_exclude_content.php'),
	],
	'expectSameHtmlWhenNoTitleTag' => [
		'config' => [
			'home_url' => 'http://example.com',
			'html' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/original_without_title.php'),
			'is_allowed' => true,
		],
		'expected' => file_get_contents(WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/RUCSS/Controller/UsedCSS/HTML/original_without_title.php'),

	],
];
