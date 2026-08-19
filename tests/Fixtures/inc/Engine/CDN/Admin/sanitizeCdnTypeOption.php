<?php
return [
	'testShouldReturnDefaultWhenCdnTypeIsEmpty' => [
		'config' => [
			'input' => [
				'cdn_type' => '',
			]
		],
		'expected' => [
			'input' => [
				'cdn_type' => 'rocketcdn',
			]
		],
	],
	'testShouldReturnRocketCdnWhenCdnTypeIsRocketCdn' => [
		'config' => [
			'input' => [
				'cdn_type' => 'rocketcdn',
			]
		],
		'expected' => [
			'input' => [
				'cdn_type' => 'rocketcdn',
			]
		],
	],
	'testShouldReturnByoCdnWhenCdnTypeIsByoCdn' => [
		'config' => [
			'input' => [
				'cdn_type' => 'byocdn',
			]
		],
		'expected' => [
			'input' => [
				'cdn_type' => 'byocdn',
			]
		],
	],
	'testShouldReturnDefaultWhenCdnTypeIsInvalid' => [
		'config' => [
			'input' => [
				'cdn_type' => 'invalid',
			]
		],
		'expected' => [
			'input' => [
				'cdn_type' => 'rocketcdn',
			]
		],
	],
	'testShouldSanitizeCdnType' => [
		'config' => [
			'input' => ['cdn_type' => '<b>byocdn</b>'],
		],
		'expected' => [
			'input' => ['cdn_type' => 'rocketcdn'],
		],
	],
	'testShouldNotAddCdnStateWhenAbsentFromInput' => [
		'config' => [
			'input' => [
				'cdn_type' => 'rocketcdn',
			]
		],
		'expected' => [
			'input' => [
				'cdn_type' => 'rocketcdn',
			]
		],
	],
	'testShouldReturnNothingWhenCdnStateIsNothing' => [
		'config' => [
			'input' => [
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'nothing',
			]
		],
		'expected' => [
			'input' => [
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'nothing',
			]
		],
	],
	'testShouldReturnRocketcdnFreeWhenCdnStateIsRocketcdnFree' => [
		'config' => [
			'input' => [
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_free',
			]
		],
		'expected' => [
			'input' => [
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_free',
			]
		],
	],
	'testShouldReturnRocketcdnProWhenCdnStateIsRocketcdnPro' => [
		'config' => [
			'input' => [
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_paid',
			]
		],
		'expected' => [
			'input' => [
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_paid',
			]
		],
	],
	'testShouldReturnByocdnWhenCdnStateIsByocdn' => [
		'config' => [
			'input' => [
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'byocdn',
			]
		],
		'expected' => [
			'input' => [
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'byocdn',
			]
		],
	],
	'testShouldReturnDefaultWhenCdnStateIsInvalid' => [
		'config' => [
			'input' => [
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'invalid',
			]
		],
		'expected' => [
			'input' => [
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'nothing',
			]
		],
	],
	'testShouldSanitizeCdnState' => [
		'config' => [
			'input' => [
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => '<b>invalid</b>',
			],
		],
		'expected' => [
			'input' => [
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'nothing',
			],
		],
	],
];
