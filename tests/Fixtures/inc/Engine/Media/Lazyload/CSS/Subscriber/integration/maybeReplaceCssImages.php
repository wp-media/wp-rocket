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
			],
			'expected' => [
				'output' => $html_filtered,
				'files' => [
					'/wp-content/cache/wp-rocket/background-css/wp-content/rocket-test-data/styles/lazyload_css_background_images.css' => [
						'exists' => true,
						'content' => file_get_contents(__DIR__ . '/CSS/lazyloaded.css')
					],
					'/wp-content/cache/wp-rocket/background-css/wp-content/rocket-test-data/styles/lazyload_css_background_images.css.json' => [
						'exists' => true,
						'content' => file_get_contents(__DIR__ . '/CSS/lazyload.css.json')
					],
				]
			]
		],
	]
];
