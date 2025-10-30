<?php

return [
	'test_data' => [
		'shouldNotRenderWhenNoPost' => [
			'config' => [
				'rows' => [],
			],
			'expected' => [
				'html' => '',
			]
		],
		'shouldRenderLoadingState' => [
			'config' => [
				'rows' => [
					[
						'url' 	  	 => 'https://example.com/page-to-test',
						'status'     => 'pending',
						'score'      => 0,
						'is_blurred' => 0,
					]
				]
			],
			'expected' => [
				'html' => '<div class="wpr-ri-loading">',
			]
		],
		'testShouldRenderBlurredState' => [
			'config' => [
				'rows' => [
					[
						'url' 	  	 => 'https://example.com/page-to-test',
						'status'     => 'completed',
						'score'      => 85,
						'is_blurred' => 1,
						'report_url' => 'https://example.com/report',

					]
				]
			],
			'expected' => [
				'html' => '<div class="wpr-ri-blurred">',
			]
		],
		'testShouldRenderCompletedState' => [
			'config' => [
				'rows' => [
					[
						'url' 	  	 => 'https://example.com/page-to-test',
						'status'     => 'completed',
						'score'      => 90,
						'is_blurred' => 0,
						'report_url' => 'https://example.com/report',
					]
				]
			],
			'expected' => [
				'html' => '<div class="wpr-ri-score-wrapper">',
			]
		],
		'testShouldRenderFailedStateWithRetestButton' => [
			'config' => [
				'rows' => [
					[
						'url' 	  	 => 'https://example.com/page-to-test',
						'status'     => 'failed',
						'score'      => 0,
						'is_blurred' => 0,
						'report_url' => '',
					]
				]
			],
			'expected' => [
				'html' => 'wpr-ri-retest-link',
			]
		],
	],
];
