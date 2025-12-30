<?php
use WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator;

return [
	'test_data' => [
		'shouldNotRenderWhenNoPost' => [
			'config' => [
				'rows' => [],
				'is_live_site' => 'example.org',
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
				'response' => [
					'response' => [
						'code' => 200,
						'message' => 'OK',
					],
					'body' => wp_json_encode( (object) [ 'rocket_insights_remote_setting' => true ] ),
				],
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
				'is_live_site' => 'example.org',
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
				'response' => [
					'response' => [
						'code' => 200,
						'message' => 'OK',
					],
					'body' => wp_json_encode( (object) [ 'rocket_insights_remote_setting' => true ] ),
				],
			],
			'expected' => [
				'html' => '<div class="wpr-ri-loading wpr-btn-with-tool-tip">',
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
				'is_live_site' => 'example.org',
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
				'response' => [
					'response' => [
						'code' => 200,
						'message' => 'OK',
					],
					'body' => wp_json_encode( (object) [ 'rocket_insights_remote_setting' => true ] ),
				],
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
				'is_live_site' => 'example.org',
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
				'response' => [
					'response' => [
						'code' => 200,
						'message' => 'OK',
					],
					'body' => wp_json_encode( (object) [ 'rocket_insights_remote_setting' => true ] ),
				],
			],
			'expected' => [
				'html' => '<div class="wpr-ri-score-wrapper wpr-btn-with-tool-tip">',
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
				],
				'is_live_site' => 'example.org',
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
				'response' => [
					'response' => [
						'code' => 200,
						'message' => 'OK',
					],
					'body' => wp_json_encode( (object) [ 'rocket_insights_remote_setting' => true ] ),
				],
			],
			'expected' => [
				'html' => 'wpr-ri-retest-link',
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
				'is_live_site' => 'localhost',
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
				'response' => [
					'response' => [
						'code' => 200,
						'message' => 'OK',
					],
					'body' => wp_json_encode( (object) [ 'rocket_insights_remote_setting' => true ] ),
				],
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
				'is_live_site' => 'example.org',
				'customer_data' => (new UserDataGenerator())->with_reseller_status(1)->generate(),
				'response' => [
					'response' => [
						'code' => 200,
						'message' => 'OK',
					],
					'body' => wp_json_encode( (object) [ 'rocket_insights_remote_setting' => true ] ),
				],
			],
			'expected' => [
				'html' => '',
			]
		],
		'testShouldNotRenderCompletedStateWhenRemoteSettingIsDisabled' => [
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
				'is_live_site' => 'example.org',
				'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate(),
				'response' => [
					'response' => [
						'code' => 200,
						'message' => 'OK',
					],
					'body' => wp_json_encode( (object) [ 'rocket_insights_remote_setting' => false ] ),
				],
			],
			'expected' => [
				'html' => '',
			]
		],
	],
];
