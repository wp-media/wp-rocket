<?php

return [
	'test_data' => [
		'shouldReturnExpectedResponseDataWhenSuccess' => [
			'html'     => 'some html',
			'url'      => 'http://example.com/path/to/style.css',
			'success'  => true,
			'expected' => json_encode(
				[
					'code' => 200,
					'message' => 'Success',
					'contents' => [
						'shakedCSS' => 'h1 { color: red; }',
						'unProcessedCss' => [],
					],
				]
			)
		],
		'shouldReturnErrorDataWhenFail' => [
			'html' => 'some html',
			'url' => 'http://example.com.path/to/my-script.js',
			'success' => false,
			'expected' => [
				'code' => 400,
				'message' => 'Unused CSS service is unavailable',
			]
		],
	],
];
