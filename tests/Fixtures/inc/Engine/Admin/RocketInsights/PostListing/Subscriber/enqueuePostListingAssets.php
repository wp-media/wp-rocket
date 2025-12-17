<?php
use WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator;

return [
	'shouldEnqueueOnPostListingPage' => [
		'config'   => [
			'screen_id' => 'edit-post',
			'post_type' => 'post',
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
			'should_enqueue' => true,
		],
	],
	'shouldEnqueueOnPageListingPage' => [
		'config'   => [
			'screen_id' => 'edit-page',
			'post_type' => 'page',
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
			'should_enqueue' => true,
		],
	],
	'shouldNotEnqueueOnDashboard'    => [
		'config'   => [
			'screen_id' => 'dashboard',
			'post_type' => null,
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
			'should_enqueue' => false,
		],
	],
	'shouldNotEnqueueOnSettingsPage' => [
		'config'   => [
			'screen_id' => 'settings_page_wprocket',
			'post_type' => null,
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
			'should_enqueue' => false,
		],
	],
	'shouldNotEnqueueForExcludedPostType' => [
		'config'   => [
			'screen_id' => 'edit-elementor_library',
			'post_type' => 'elementor_library',
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
			'should_enqueue' => false,
		],
	],
	'shouldNotEnqueueOnPostListingPageForLocalEnv' => [
		'config'   => [
			'screen_id' => 'edit-post',
			'post_type' => 'post',
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
			'should_enqueue' => false,
		],
	],
	'shouldNotEnqueueOnPostListingPageForReseller' => [
		'config'   => [
			'screen_id' => 'edit-post',
			'post_type' => 'post',
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
			'should_enqueue' => false,
		],
	],
	'shouldNotEnqueueOnPostListingWhenRemoteSettingIsDisabled' => [
		'config'   => [
			'screen_id' => 'edit-post',
			'post_type' => 'post',
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
			'should_enqueue' => false,
		],
	],
];
