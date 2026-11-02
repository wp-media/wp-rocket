<?php
return [
	'successfulPaidSubscription' => [
		'config'          => [
			'status_code' => 200,
			'body'        => [
				'success'           => true,
				'subscription_id'   => 67890,
				'website_activated' => true,
				'cdn_url'           => 'https://abcd1234.delivery.rocketcdn.me',
				'status'            => 'running',
				'next_date_update'  => '2026-12-01 00:00:00',
				'website_attached'  => true,
				'plan_type'         => 'paid',
				'plan_page_limit'   => null,
				'website_id'        => 12345,
			],
		],
		'expected_subset' => [
			'id'                            => 67890,
			'is_active'                     => true,
			'cdn_url'                       => 'https://abcd1234.delivery.rocketcdn.me',
			'subscription_next_date_update' => '2026-12-01 00:00:00',
			'subscription_status'           => 'running',
			'website_attached'              => true,
			'plan_type'                     => 'paid',
			'plan_page_limit'               => 0,
			'website_id'                    => 12345,
			'status_code'                   => 200,
			'success'                       => true,
		],
	],

	'apiReportsFailureAtHttp200' => [
		'config'          => [
			'status_code' => 200,
			'body'        => [ 'success' => false ],
		],
		'expected_subset' => [
			'status_code' => 200,
			'success'     => false,
			'is_active'   => false,
		],
	],

	'notFound'                   => [
		'config'          => [
			'status_code' => 404,
			'body'        => [],
		],
		'expected_subset' => [
			'status_code'         => 404,
			'success'             => false,
			'subscription_status' => 'cancelled',
		],
	],

	'serverError'                => [
		'config'          => [
			'status_code' => 500,
			'body'        => [],
		],
		'expected_subset' => [
			'status_code' => 500,
			'success'     => false,
		],
	],
];
