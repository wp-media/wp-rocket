<?php

return [
	'testShouldReturnNothingWhenAllFlagsDisabled'                    => [
		'config'   => [
			'cdn_byocdn_enabled'     => 0,
			'rocketcdn_free_enabled' => 0,
			'rocketcdn_pro_enabled'  => 0,
		],
		'expected' => 'nothing',
	],
	'testShouldReturnRocketcdnWhenOnlyProEnabled'                    => [
		'config'   => [
			'cdn_byocdn_enabled'     => 0,
			'rocketcdn_free_enabled' => 0,
			'rocketcdn_pro_enabled'  => 1,
		],
		'expected' => 'rocketcdn',
	],
	'testShouldReturnRocketcdnWhenOnlyFreeEnabled'                   => [
		'config'   => [
			'cdn_byocdn_enabled'     => 0,
			'rocketcdn_free_enabled' => 1,
			'rocketcdn_pro_enabled'  => 0,
		],
		'expected' => 'rocketcdn',
	],
	'testShouldReturnRocketcdnWhenFreeAndProEnabled'                 => [
		'config'   => [
			'cdn_byocdn_enabled'     => 0,
			'rocketcdn_free_enabled' => 1,
			'rocketcdn_pro_enabled'  => 1,
		],
		'expected' => 'rocketcdn',
	],
	'testShouldReturnByocdnWhenOnlyByocdnEnabled'                    => [
		'config'   => [
			'cdn_byocdn_enabled'     => 1,
			'rocketcdn_free_enabled' => 0,
			'rocketcdn_pro_enabled'  => 0,
		],
		'expected' => 'byocdn',
	],
	'testShouldReturnByocdnWhenByocdnAndProEnabled'                  => [
		'config'   => [
			'cdn_byocdn_enabled'     => 1,
			'rocketcdn_free_enabled' => 0,
			'rocketcdn_pro_enabled'  => 1,
		],
		'expected' => 'byocdn',
	],
	'testShouldReturnByocdnWhenByocdnAndFreeEnabled'                 => [
		'config'   => [
			'cdn_byocdn_enabled'     => 1,
			'rocketcdn_free_enabled' => 1,
			'rocketcdn_pro_enabled'  => 0,
		],
		'expected' => 'byocdn',
	],
	'testShouldReturnByocdnWhenAllFlagsEnabled'                      => [
		'config'   => [
			'cdn_byocdn_enabled'     => 1,
			'rocketcdn_free_enabled' => 1,
			'rocketcdn_pro_enabled'  => 1,
		],
		'expected' => 'byocdn',
	],
];
