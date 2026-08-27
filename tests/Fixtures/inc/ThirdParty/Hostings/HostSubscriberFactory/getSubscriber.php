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

		'testShouldReturnSavvii' => [
			'host'     => 'savvii',
			'expected' => 'WP_Rocket\ThirdParty\Hostings\Savvii',
		],

		'testShouldReturnFlywheel' => [
			'host'     => 'flywheel',
			'expected' => 'WP_Rocket\ThirdParty\Hostings\Flywheel',
		],

		'testShouldReturnSiteground' => [
			'host'     => 'siteground',
			'expected' => 'WP_Rocket\ThirdParty\Hostings\SiteGround',
		],

		'testShouldReturnWpserveur' => [
			'host'     => 'wpserveur',
			'expected' => 'WP_Rocket\ThirdParty\Hostings\WpServeur',
		],

		'testShouldReturnPresslabs' => [
			'host'     => 'presslabs',
			'expected' => 'WP_Rocket\ThirdParty\Hostings\Presslabs',
		],

		'testShouldReturnPagely' => [
			'host'     => 'pagely',
			'expected' => 'WP_Rocket\ThirdParty\Hostings\Pagely',
		],
	],
];
