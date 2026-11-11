<?php

return [
	'shouldSendRocketcdnWhenStateBecomesPaid'  => [
		'config'   => [
			'new_cdn_state' => 'rocketcdn_paid',
			'subscription'  => [
				'plan_type'           => 'paid',
				'subscription_status' => 'running',
				'status_code'         => 200,
				'success'             => true,
			],
		],
		'expected' => [
			'transient_cleared' => true,
			'sends_rocketcdn'   => true,
		],
	],
	'shouldNotSendRocketcdnWhenStateStaysFree' => [
		'config'   => [
			'new_cdn_state' => 'rocketcdn_free',
			'subscription'  => [
				'plan_type'           => 'free',
				'subscription_status' => 'running',
				'status_code'         => 200,
				'success'             => true,
			],
		],
		'expected' => [
			'transient_cleared' => false,
			'sends_rocketcdn'   => false,
		],
	],
];
