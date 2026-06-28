<?php
return [
	// Subscription in grace period: maybe_pause_cdn_for_inactive_subscription sets the
	// forced_pause_state and the render controller signals the element should be disabled.
	'forcedPauseEnabled'  => [
		'config'   => [
			'forced_pause_state'       => true,
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

	// Active paid subscription: maybe_pause_cdn_for_inactive_subscription does not fire
	// and forced_pause_state is never set, so the element stays enabled.
	'forcedPauseDisabled' => [
		'config'   => [
			'forced_pause_state'       => false,
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
