<?php

return [
	'testShouldNotAddFlagsWhenAbsentFromInput'                    => [
		'config'   => [
			'input' => [
				'cdn_type' => 'byocdn',
			],
		],
		'expected' => [
			'cdn_type' => 'byocdn',
		],
	],
	'testShouldCastTruthyStringValuesToOne'                       => [
		'config'   => [
			'input' => [
				'cdn_type'               => 'rocketcdn',
				'rocketcdn_free_enabled' => '1',
				'rocketcdn_pro_enabled'  => '',
				'cdn_byocdn_enabled'     => 0,
			],
		],
		'expected' => [
			'cdn_type'               => 'rocketcdn',
			'rocketcdn_free_enabled' => 1,
			'rocketcdn_pro_enabled'  => 0,
			'cdn_byocdn_enabled'     => 0,
		],
	],
	'testShouldLeaveSingleEnabledFlagUnchanged'                   => [
		'config'   => [
			'input' => [
				'cdn_type'               => 'byocdn',
				'rocketcdn_free_enabled' => 0,
				'rocketcdn_pro_enabled'  => 0,
				'cdn_byocdn_enabled'     => 1,
			],
		],
		'expected' => [
			'cdn_type'               => 'byocdn',
			'rocketcdn_free_enabled' => 0,
			'rocketcdn_pro_enabled'  => 0,
			'cdn_byocdn_enabled'     => 1,
		],
	],
	'testShouldZeroRocketcdnFlagsWhenByocdnEnabled'                => [
		'config'   => [
			'input' => [
				'cdn_type'               => 'byocdn',
				'rocketcdn_free_enabled' => 1,
				'rocketcdn_pro_enabled'  => 1,
				'cdn_byocdn_enabled'     => 1,
			],
		],
		'expected' => [
			'cdn_type'               => 'byocdn',
			'rocketcdn_free_enabled' => 0,
			'rocketcdn_pro_enabled'  => 0,
			'cdn_byocdn_enabled'     => 1,
		],
	],
	'testShouldZeroFreeFlagWhenProEnabled'                         => [
		'config'   => [
			'input' => [
				'cdn_type'               => 'rocketcdn',
				'rocketcdn_free_enabled' => 1,
				'rocketcdn_pro_enabled'  => 1,
				'cdn_byocdn_enabled'     => 0,
			],
		],
		'expected' => [
			'cdn_type'               => 'rocketcdn',
			'rocketcdn_free_enabled' => 0,
			'rocketcdn_pro_enabled'  => 1,
			'cdn_byocdn_enabled'     => 0,
		],
	],
];
