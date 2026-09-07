<?php

return [
	'testShouldBackfillRocketcdnPaidFromLegacyFields'                 => [
		'config'   => [
			'initial'      => [
				'cdn'        => 1,
				'cdn_type'   => 'rocketcdn',
				'cdn_cnames' => [ 'https://3c85d434.delivery.rocketcdn.me' ],
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
	'testShouldBackfillNothingWhenCdnDisabled'                       => [
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
	'testShouldBackfillNothingWhenCdnDisabledButCnameSaved'          => [
		'config'   => [
			'initial'      => [
				'cdn'        => 0,
				'cdn_type'   => 'rocketcdn',
				'cdn_cnames' => [ 'https://3c85d434.delivery.rocketcdn.me' ],
			],
			'subscription' => [
				'subscription_status' => 'running',
				'plan_type'           => 'paid',
			],
		],
		'expected' => [
			'cdn_state' => 'nothing',
		],
	],
	'testShouldBackfillNothingWhenCdnEnabledButNoRocketcdnCnameSaved' => [
		'config'   => [
			'initial'      => [
				'cdn'        => 1,
				'cdn_type'   => 'rocketcdn',
				'cdn_cnames' => [],
			],
			'subscription' => [
				'subscription_status' => 'running',
				'plan_type'           => 'paid',
			],
		],
		'expected' => [
			'cdn_state' => 'nothing',
		],
	],
	'testShouldBackfillNothingWhenCdnDisabledAndNoRocketcdnCnameSaved' => [
		'config'   => [
			'initial'      => [
				'cdn'        => 0,
				'cdn_type'   => 'rocketcdn',
				'cdn_cnames' => [],
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
