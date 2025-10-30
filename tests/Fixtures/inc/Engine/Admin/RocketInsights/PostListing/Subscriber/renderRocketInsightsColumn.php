<?php
use WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator;

return [
	'test_data' => [
		'shouldNotRenderWhenNoPost' => [
			'config' => [
				'rows' => [],
				'is_live_site' => true,
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
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
				],
				'is_live_site' => true,
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
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
				],
				'is_live_site' => true,
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
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
				],
				'is_live_site' => true,
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
			],
			'expected' => [
				'html' => '<div class="wpr-ri-score-wrapper">',
			]
		],
		'testShouldNotRenderCompletedStateForLocalEnv' => [
			'config' => [
				'rows' => [
					[
						'url' 	  	 => 'https://example.com/page-to-test',
						'status'     => 'completed',
						'score'      => 90,
						'is_blurred' => 0,
						'report_url' => 'https://example.com/report',
					]
				],
				'is_live_site' => false,
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
			],
			'expected' => [
				'html' => '',
			]
		],
		'testShouldNotRenderCompletedStateForReseller' => [
			'config' => [
				'rows' => [
					[
						'url' 	  	 => 'https://example.com/page-to-test',
						'status'     => 'completed',
						'score'      => 90,
						'is_blurred' => 0,
						'report_url' => 'https://example.com/report',
					]
				],
				'is_live_site' => true,
				'customer_data' => (new UserDataGenerator())->with_reseller_status(1)->generate(),
			],
			'expected' => [
				'html' => '',
			]
		],
	],
];
