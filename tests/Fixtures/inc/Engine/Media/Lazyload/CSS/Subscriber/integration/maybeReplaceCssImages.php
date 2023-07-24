<?php

$html = file_get_contents(__DIR__ . '/HTML/html.php');

$html_filtered = file_get_contents(__DIR__ . '/HTML/html_output.php');

return [
	'vfs_dir' => 'wp-content/',
	'structure' => [
		'wp-content' => [
			'rocket-test-data' => [
				'styles' => [
					'lazyload_css_background_images.css' => file_get_contents(__DIR__ . '/CSS/lazyload_css_background_images.css')
				]
			]
		]
	],
	'test_data' => [
		'shouldReturnAsExpected' => [
			'config' => [
				'html' => $html,
				'lazyload_css_bg_img' => true,
				'response' => [ 'body' => file_get_contents(__DIR__ . '/CSS/external.css'), 'response' => ['code' => 200 ] ],
			],
			'expected' => [
				'output' => $html_filtered,
				'files' => [
					'/wp-content/cache/background-css/wp-content/rocket-test-data/styles/lazyload_css_background_images.css' => [
						'exists' => true,
						'content' => file_get_contents(__DIR__ . '/CSS/lazyloaded.css')
					],
					'/wp-content/cache/background-css/wp-content/rocket-test-data/styles/lazyload_css_background_images.css.json' => [
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
				]
			]
		],
	]
];
