<?php

$html = file_get_contents(__DIR__ . '/HTML/html.php');

$html_filtered = file_get_contents(__DIR__ . '/HTML/html_output.php');
$html_excluded = file_get_contents(__DIR__ . '/HTML/html_excluded.php');

return [
	'vfs_dir' => 'wp-content/',
	'structure' => [
		'wp-content' => [
			'rocket-test-data' => [
				'styles' => [
					'lazyload_css_background_images.css' => file_get_contents(__DIR__ . '/CSS/lazyload_css_background_images.css'),
					'excluded.css' => file_get_contents(__DIR__ . '/CSS/excluded.css'),
				]
			]
		]
	],
	'test_data' => [
		'shouldReturnAsExpected' => [
			'config' => [
				'html' => $html,
				'current_time' => 17895120,
				'lazyload_css_bg_img' => true,
				'external' => [
					'url' => 'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/lazyload_css_background_images.min.css',
					'response' => [ 'body' => file_get_contents(__DIR__ . '/CSS/external.css'), 'response' => ['code' => 200 ] ],
				],
				'no_background' => [
					'url' => 'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/no_background.css',
					'response' => [ 'body' => file_get_contents(__DIR__ . '/CSS/no_background.css'), 'response' => ['code' => 200 ] ],
				],
				'excluded' => []
			],
			'expected' => [
				'output' => $html_filtered,
				'files' => [
					'/wp-content/cache/background-css/example.org/wp-content/rocket-test-data/styles/lazyload_css_background_images.css' => [
						'exists' => true,
						'content' => file_get_contents(__DIR__ . '/CSS/lazyloaded.css')
					],
					'/wp-content/cache/background-css/example.org/wp-content/rocket-test-data/styles/lazyload_css_background_images.css.json' => [
						'exists' => true,
						'content' => file_get_contents(__DIR__ . '/CSS/lazyload.css.json')
					],
					'/wp-content/cache/background-css/new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/lazyload_css_background_images.min.css' => [
						'exists' => true,
						'content' => file_get_contents(__DIR__ . '/CSS/external_lazyloaded.css')
					],
					'/wp-content/cache/background-css/new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/lazyload_css_background_images.min.css.json' => [
						'exists' => true,
						'content' => file_get_contents(__DIR__ . '/CSS/external.css.json')
					],
					'/wp-content/cache/background-css/new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/no_background.css' => [
						'exists' => false,
					],
					'/wp-content/cache/background-css/new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/no_background.css.json' => [
						'exists' => false,
					],
				]
			]
		],
		'exclusionsShouldExclude' => [
			'config' => [
				'html' => $html,
				'current_time' => 17895120,
				'lazyload_css_bg_img' => true,
				'external' => [
					'url' => 'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/lazyload_css_background_images.min.css',
					'response' => [ 'body' => file_get_contents(__DIR__ . '/CSS/external.css'), 'response' => ['code' => 200 ] ],
				],
				'no_background' => [
					'url' => 'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/no_background.css',
					'response' => [ 'body' => file_get_contents(__DIR__ . '/CSS/no_background.css'), 'response' => ['code' => 200 ] ],
				],
				'excluded' => [
					'.external-css-background-images',
					'http://example.org/wp-content/rocket-test-data/styles/excluded.css'
				]
			],
			'expected' => [
				'output' => $html_excluded,
				'files' => [
					'/wp-content/cache/background-css/example.org/wp-content/rocket-test-data/styles/lazyload_css_background_images.css' => [
						'exists' => true,
						'content' => file_get_contents(__DIR__ . '/CSS/lazyloaded.css')
					],
					'/wp-content/cache/background-css/example.org/wp-content/rocket-test-data/styles/lazyload_css_background_images.css.json' => [
						'exists' => true,
						'content' => file_get_contents(__DIR__ . '/CSS/lazyload.css.json')
					],
					'/wp-content/cache/background-css/new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/lazyload_css_background_images.min.css' => [
						'exists' => true,
						'content' => file_get_contents(__DIR__ . '/CSS/external_lazyloaded.css')
					],
					'/wp-content/cache/background-css/new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/lazyload_css_background_images.min.css.json' => [
						'exists' => true,
						'content' => file_get_contents(__DIR__ . '/CSS/external.css.json')
					],
					'/wp-content/cache/background-css/new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/no_background.css' => [
						'exists' => false,
					],
					'/wp-content/cache/background-css/new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/no_background.css.json' => [
						'exists' => false,
					],
				]
			]
		],
	]
];
