<?php

return [

	// Needed for rocket_analytics_data().
	'settings' => [
		'cdn_cnames' => [],
		'sitemaps'   => [],
	],

	'notice' => <<<HTML
<div class="notice notice-alt notice-warning is-dismissible" id="rocketcdn-promote-notice">
	<h2 class="notice-title">New!</h2>
	<p>Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network!</p>
	<p><a href="#page_cdn" class="wpr-button" id="rocketcdn-learn-more-dismiss">Learn More</a></p>
</div>
HTML
	,

	'test_data' => [

		'testShouldDisplayNothingWhenWhiteLabel' => [
			'rocketcdn_data' => [],
			'expected'       => [
				'should_display' => false,
			],
			'config'         => [
				'home_url'    => 'http://localhost',
				'white_label' => true,
			],
		],

		'testShouldDisplayNothingWhenNotLiveSite' => [
			'rocketcdn_data' => [],
			'expected'       => [
				'should_display' => false,
			],
			'config'         => [
				'home_url' => 'http://localhost',
			],
		],

		'testShouldNotDisplayNoticeWhenNoCapability' => [
			'rocketcdn_data' => [],
			'expected'       => [
				'should_display' => false,
			],
			'config'         => [
				'role' => 'editor',
			],
		],

		'testShouldNotDisplayNoticeWhenNotRocketPage' => [
			'rocketcdn_data' => [],
			'expected'       => [
				'should_display' => false,
			],
			'config'         => [
				'role'   => 'administrator',
				'screen' => 'edit.php',
			],
		],

		'testShouldNotDisplayNoticeWhenDismissed' => [
			'rocketcdn_data' => [],
			'expected'       => [
				'should_display' => false,
			],
			'config'         => [
				'role'      => 'administrator',
				'user_meta' => true,
				'screen'    => 'settings_page_wprocket'
            ],
		],

		'testShouldNotDisplayNoticeWhenActive' => [
			'rocketcdn_data' => [ 'subscription_status' => 'running' ],
			'expected'       => [
				'should_display' => false,
				'config'         => [
					'role'   => 'administrator',
					'screen' => 'settings_page_wprocket',
				],
			],

			'testShouldDisplayNoticeWhenNotActive' => [
				'rocketcdn_data' => [ 'subscription_status' => 'cancelled' ],
				'expected'       => [
					'should_display' => true,
				],
				'config'         => [
					'role'   => 'administrator',
					'screen' => 'settings_page_wprocket',
				],
			],
		],
	],
];
