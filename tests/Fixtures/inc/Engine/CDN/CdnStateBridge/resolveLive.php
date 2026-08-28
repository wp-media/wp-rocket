<?php

return [
	'testShouldReflectCdnForcedOffAtReadTime'          => [
		'config'   => [
			'stored' => [
				'cdn'       => 1,
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'byocdn',
			],
			'force'  => [
				'option' => 'cdn',
				'value'  => false,
			],
		],
		'expected' => 'nothing',
	],
	'testShouldReflectCdnTypeForcedToByocdnAtReadTime' => [
		'config'   => [
			'stored'       => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_free',
			],
			'subscription' => [
				'subscription_status' => 'running',
				'plan_type'           => 'free',
			],
			'force'        => [
				'option' => 'cdn_type',
				'value'  => 'byocdn',
			],
		],
		'expected' => 'byocdn',
	],
	'testShouldFollowStoredValueWhenNothingIsForced'   => [
		'config'   => [
			'stored'       => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_paid',
			],
			'subscription' => [
				'subscription_status' => 'running',
				'plan_type'           => 'paid',
			],
		],
		'expected' => 'rocketcdn_paid',
	],
];
