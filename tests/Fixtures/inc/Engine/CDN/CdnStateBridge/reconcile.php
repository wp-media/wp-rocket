<?php

return [
	'testShouldDeriveByocdnStateFromLegacyWrite'  => [
		'config'   => [
			'initial' => [
				'cdn'       => 0,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'nothing',
			],
			'write'   => [
				'cdn'      => 1,
				'cdn_type' => 'byocdn',
			],
		],
		'expected' => [
			'cdn'       => 1,
			'cdn_type'  => 'byocdn',
			'cdn_state' => 'byocdn',
		],
	],
	'testShouldDeriveNothingStateWhenLegacyCdnIsDisabled' => [
		'config'   => [
			'initial'      => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_free',
			],
			'subscription' => [
				'subscription_status' => 'running',
				'plan_type'            => 'free',
			],
			'write'        => [
				'cdn' => 0,
			],
		],
		'expected' => [
			'cdn'       => 0,
			'cdn_state' => 'nothing',
		],
	],
	'testShouldDeriveLegacyFieldsFromDirectStateWrite' => [
		'config'   => [
			'initial'      => [
				'cdn'       => 0,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'nothing',
			],
			'subscription' => [
				'subscription_status' => 'running',
				'plan_type'            => 'paid',
			],
			'write'        => [
				'cdn_state' => 'rocketcdn_paid',
			],
		],
		'expected' => [
			'cdn'       => 1,
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'rocketcdn_paid',
		],
	],
	'testShouldLeaveAlreadyConsistentDualWriteUntouched' => [
		'config'   => [
			'initial' => [
				'cdn'       => 0,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'nothing',
			],
			'write'   => [
				'cdn'       => 1,
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'byocdn',
			],
		],
		'expected' => [
			'cdn'       => 1,
			'cdn_type'  => 'byocdn',
			'cdn_state' => 'byocdn',
		],
	],
];
