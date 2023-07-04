<?php

$html = file_get_contents(__DIR__ . '/HTML/html.php');

$html_filtered = file_get_contents(__DIR__ . '/HTML/html_output.php');


return [
	'vfs_dir' => 'wp-content/',
	'structure' => [

	],
	'test_data' => [
		'shouldReturnAsExpected' => [
			'config' => [
				'html' => $html,
				'data' => [
					'html' => $html
				],
			],
			'expected' => [
				'data' => [
					'html' => $html
				],
				'output' => $html_filtered,
				'files' => [
					'' => [
						'exists' => true,
					]
				]
			]
		],
	]
];
