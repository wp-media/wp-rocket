<?php

return [
	'testShouldBackfillRocketcdnPaidFromLegacyFields' => [
		'config'   => [
			'initial'      => [
				'cdn'      => 1,
				'cdn_type' => 'rocketcdn',
			],
			'subscription' => [
				'subscription_status' => 'running',
				'plan_type'           => 'paid',
			],
		],
		'expected' => [
			'cdn_state' => 'rocketcdn_paid',
		],
	],
	'testShouldBackfillNothingWhenCdnDisabled'        => [
		'config'   => [
			'initial'      => [
				'cdn'      => 0,
				'cdn_type' => 'rocketcdn',
			],
			'subscription' => [
				'subscription_status' => 'none',
			],
		],
		'expected' => [
			'cdn_state' => 'nothing',
		],
	],
	'testShouldBackfillByocdnFromLegacyFields'        => [
		'config'   => [
			'initial'      => [
				'cdn'      => 1,
				'cdn_type' => 'byocdn',
			],
			'subscription' => [
				'subscription_status' => 'none',
			],
		],
		'expected' => [
			'cdn_state' => 'byocdn',
		],
	],
	'testShouldNotOverwriteExistingCdnState'          => [
		'config'   => [
			'initial'      => [
				'cdn'       => 1,
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'rocketcdn_paid',
			],
			'subscription' => [
				'subscription_status' => 'none',
			],
		],
		'expected' => [
			'cdn_state' => 'rocketcdn_paid',
		],
	],
];
