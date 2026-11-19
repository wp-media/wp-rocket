<?php
return [
	'cancelledOutsideGracePeriod' => [
		'config'   => [
			'subscription_status_code' => 404,
			'website_search_code'      => 404,
		],
		'expected' => false,
	],

	'inGracePeriod'               => [
		'config'   => [
			'subscription_status_code' => 404,
			'website_search_code'      => 200,
			'website_search_body'      => [
				'subscription_status'    => 'cancelled',
				'status'                 => 'pending_deletion',
				'subscription_plan_type' => 'paid',
				'cdn_url'                => 'https://abcd1234.delivery.rocketcdn.me',
			],
		],
		'expected' => true,
	],

	'activeRunningPaid'           => [
		'config'   => [
			'subscription_status_code' => 200,
			'subscription_status_body' => [
				'success'           => true,
				'website_id'        => 12345,
				'website_activated' => true,
				'cdn_url'           => 'https://abcd1234.delivery.rocketcdn.me',
				'status'            => 'running',
				'next_date_update'  => '2026-12-01 00:00:00',
				'website_attached'  => true,
				'plan_type'         => 'paid',
				'plan_page_limit'   => null,
				'subscription_id'   => 67890,
			],
		],
		'expected' => false,
	],
];
