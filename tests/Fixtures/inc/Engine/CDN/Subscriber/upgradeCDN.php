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
