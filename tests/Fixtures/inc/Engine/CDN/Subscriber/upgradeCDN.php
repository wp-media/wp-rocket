<?php
return [
	'shouldSetByocdnWhenLegacyCdnIsEnabled' => [
		'config' => [
			'new_version' => '3.22.0',
			'old_version' => '3.21.1',
			'cdn_enabled' => 1,
			'current_options' => [
				'cdn' => 1,
			],
			'has_active_subscription' => false,
			'cdn_cnames' => [
				'https://cdnexample.org/'
			],
		],
		'expected' => [
			'should_update' => true,
			'cdn_type'      => 'byocdn',
			'options'       => [
				'cdn'      => 1,
				'cdn_type' => 'byocdn',
			],
		],
	],
	'shouldSetRocketcdnWhenCdnIsNotEnabled' => [
		'config' => [
			'new_version' => '3.22.0',
			'old_version' => '3.21.1',
			'cdn_enabled' => 0,
			'current_options' => [],
			'has_active_subscription' => false,
		],
		'expected' => [
			'should_update' => true,
			'cdn_type'      => 'rocketcdn',
			'options'       => [
				'cdn'	  => 1,
				'cdn_type' => 'rocketcdn',
			],
		],
	],
	'shouldSetRocketcdnWhenHavingrocketcdnSubscription' => [
		'config' => [
			'new_version' => '3.22.0',
			'old_version' => '3.21.1',
			'cdn_enabled' => 1,
			'current_options' => [
				'cdn' => 1,
			],
			'has_active_subscription' => true,
		],
		'expected' => [
			'should_update' => true,
			'cdn_type'      => 'rocketcdn',
			'options'       => [
				'cdn'	  => 1,
				'cdn_type' => 'rocketcdn',
			],
		],
	],
];
