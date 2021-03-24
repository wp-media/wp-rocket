<?php

return [
	'test_data' => [
		'shouldReturnExpectedDataWhenSuccess' => [
			'config' => [
				'html' => 'some html',
				'url' => 'http://example.com/path/to/style.css',
				'options' => [1],
			],
			'mockResponse' => [
				'headers'  => [
					'date'                      => 'Wed, 24 Mar, 2021 14:26:14 GMT',
					'content-type'              => 'application/json',
					'x-powered-by'              => 'PHP/7.4.16',
					'cache-control'             => 'no-cache, private',
					'x-frame-options'           => 'SAMEORIGIN',
					'x-xss-protection'          => '1; mode=block',
					'x-content-type-options'    => 'nosniff',
					'strict-transport-security' => 'max-age=15724800; includeSubDomains'
				],
				'body'     => json_encode(
					[
						'code' => 200,
						'message' => 'OK',
						'contents' => [
							'shakedCSS' => 'h1{color:red;}',
							'unProcessedCss' => [],
						],
					]
				),
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
				'cookies'  => [],
				'filename' => null,
			],
			'expected' => [
				'code' => 200,
				'message' => 'OK',
				'css' => 'h1{color:red;}',
				'unprocessed_css' => [],
			],
		],

//			'html'     => 'some html',
//			'url'      => 'http://example.com/path/to/style.css',
//			'success'  => true,
//			'expected' => json_encode(
//				[
//					'code' => 200,
//					'message' => 'Success',
//					'contents' => [
//						'shakedCSS' => 'h1 { color: red; }',
//						'unProcessedCss' => [],
//					],
//				]
//			)
		],
//		'shouldReturnErrorDataWhenFail' => [
//			'html' => 'some html',
//			'url' => 'http://example.com.path/to/my-script.js',
//			'success' => false,
//			'expected' => [
//				'code' => 400,
//				'message' => 'Unused CSS service is unavailable',
//			]
//		],
//	],
];
