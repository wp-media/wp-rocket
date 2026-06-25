<?php

$cdn_url = 'https://abcd1234.delivery.rocketcdn.me';

// Reusable API response helpers.
$status_404 = [
	'response' => [
		'code'    => 404,
		'message' => 'Not Found',
	],
	'body'     => '',
];

$status_200_running_paid = [
	'response' => [
		'code'    => 200,
		'message' => 'OK',
	],
	'body'     => json_encode(
		[
			'success'           => true,
			'website_id'        => 12345,
			'website_activated' => true,
			'cdn_url'           => $cdn_url,
			'status'            => 'running',
			'next_date_update'  => '2026-12-01 00:00:00',
			'website_attached'  => true,
			'plan_type'         => 'paid',
			'plan_page_limit'   => null,
			'subscription_id'   => 67890,
		]
	),
];

$website_search_200_pending_paid = [
	'response' => [
		'code'    => 200,
		'message' => 'OK',
	],
	'body'     => json_encode(
		[
			'subscription_status'    => 'cancelled',
			'status'                 => 'pending_deletion',
			'subscription_plan_type' => 'paid',
			'cdn_url'                => $cdn_url,
		]
	),
];

return [
	'cancelPaidAndDeleteWebsite'  => [
		'config'   => [
			'subscription_api_response' => $status_404,
			'website_search_response'   => $status_404,
			'free_pages'                => [
				[
					'url'   => 'http://example.org/',
					'title' => 'Home',
				],
			],
		],
		'expected' => [
			'has_active_subscription'           => false,
			'is_in_grace_period'                => false,
			'is_cancelled_outside_grace_period' => true,
			'is_paid'                           => false,
			'context_driver'                    => 'rocketcdn',
			'cname_applied'                     => false,
			'free_pages_count_in_db'            => 1,
		],
	],

	'cancelPaidGracePeriodStripe' => [
		'config'   => [
			'subscription_api_response' => $status_404,
			'website_search_response'   => $website_search_200_pending_paid,
			'forced_pause_tracking'     => [ 'persistent' => true ],
		],
		'expected' => [
			'has_active_subscription'           => false,
			'is_in_grace_period'                => true,
			'is_cancelled_outside_grace_period' => false,
			'is_paid'                           => true,
			'context_driver'                    => 'rocketcdn_paid',
			'cname_applied'                     => false,
			'should_disable_element'            => true,
		],
	],

	'refundAndDeleteWebsite'      => [
		'config'   => [
			'subscription_api_response' => $status_404,
			'website_search_response'   => $status_404,
			'free_pages'                => [
				[
					'url'   => 'http://example.org/',
					'title' => 'Home',
				],
				[
					'url'   => 'http://example.org/about/',
					'title' => 'About',
				],
			],
		],
		'expected' => [
			'has_active_subscription'           => false,
			'is_in_grace_period'                => false,
			'is_cancelled_outside_grace_period' => true,
			'context_driver'                    => 'rocketcdn',
			'cname_applied'                     => false,
			'free_pages_count_in_db'            => 2,
		],
	],

	'repurchasedPaidActive'       => [
		'config'   => [
			'subscription_api_response' => $status_200_running_paid,
		],
		'expected' => [
			'has_active_subscription'           => true,
			'is_in_grace_period'                => false,
			'is_cancelled_outside_grace_period' => false,
			'is_paid'                           => true,
			'context_driver'                    => 'rocketcdn_paid',
			'cname_applied'                     => true,
			'should_disable_element'            => false,
		],
	],

	'upgradeWithGracePeriod'      => [
		'config'   => [
			'subscription_api_response' => $status_404,
			'website_search_response'   => $website_search_200_pending_paid,
			'forced_pause_tracking'     => [ 'persistent' => true ],
		],
		'expected' => [
			'has_active_subscription'           => false,
			'is_in_grace_period'                => true,
			'is_cancelled_outside_grace_period' => false,
			'is_paid'                           => true,
			'context_driver'                    => 'rocketcdn_paid',
			'cname_applied'                     => false,
			'should_disable_element'            => true,
		],
	],

];
