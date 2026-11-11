<?php

return [
	'paidSubscription'      => [
		'config' => [
			'subscription' => [
				'subscription_status' => 'running',
				'plan_type'           => 'paid',
			],
			'token'        => 'fake-cdn-token-paid',
			'stored'       => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_paid',
			],
		],
	],
	'freeSubscription'      => [
		'config' => [
			'subscription' => [
				'subscription_status' => 'running',
				'plan_type'           => 'free',
			],
			'token'        => 'fake-cdn-token-free',
			'stored'       => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_free',
			],
		],
	],
	'cancelledSubscription' => [
		'config' => [
			'subscription' => [
				'subscription_status' => 'cancelled',
				'website_status'      => 'active',
			],
			'token'        => 'fake-cdn-token-cancelled',
			'stored'       => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_free',
			],
		],
	],
	'noToken'               => [
		'config' => [
			'subscription' => [
				'subscription_status' => 'none',
			],
			'stored'       => [
				'cdn'       => 0,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'nothing',
			],
		],
	],
];
