<?php

return [
	'test_data' => [
		'testShouldReturnEmptyWhenNotCompatibleHost' => [
			'host'     => 'ovh',
			'expected' => 'WP_Rocket\ThirdParty\NullSubscriber',
		],
		'testShouldReturnCloudways' => [
			'host'     => 'cloudways',
			'expected' => 'WP_Rocket\ThirdParty\Hostings\Cloudways',
		],
		'testShouldReturnPressable' => [
			'host'     => 'pressable',
			'expected' => 'WP_Rocket\ThirdParty\Hostings\Pressable',
		],
		'testShouldReturnSpinUpWP' => [
			'host'     => 'spinupwp',
			'expected' => 'WP_Rocket\ThirdParty\Hostings\SpinUpWP',
		],
	],
];
