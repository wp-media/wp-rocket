<?php

return [
	// legacy -> state is no longer something the bridge writes to storage: CdnStateResolver
	// resolves it live on every read instead (tests/Integration/.../CdnStateResolver/resolve.php).
	// This fixture now only covers the bridge's remaining direction, state -> legacy.
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
