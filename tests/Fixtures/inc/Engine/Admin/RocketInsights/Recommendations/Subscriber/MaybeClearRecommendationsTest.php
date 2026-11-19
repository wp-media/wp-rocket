<?php

return [
	'test_data' => [
		'shouldClearRecommendationsWhenImagifyActivated' => [
			'config' => [
				'plugin' => 'imagify/imagify.php',
			],
			'expected' => [
				'should_clear' => true,
			],
		],

		'shouldClearRecommendationsWhenImagifyDeactivated' => [
			'config' => [
				'plugin' => 'imagify/imagify.php',
			],
			'expected' => [
				'should_clear' => true,
			],
		],

		'shouldClearRecommendationsWhenRocketCDNActivated' => [
			'config' => [
				'plugin' => 'rocketcdn/rocketcdn.php',
			],
			'expected' => [
				'should_clear' => true,
			],
		],

		'shouldClearRecommendationsWhenRocketCDNDeactivated' => [
			'config' => [
				'plugin' => 'rocketcdn/rocketcdn.php',
			],
			'expected' => [
				'should_clear' => true,
			],
		],

		'shouldNotClearRecommendationsForOtherPlugins' => [
			'config' => [
				'plugin' => 'some-other-plugin/plugin.php',
			],
			'expected' => [
				'should_clear' => false,
			],
		],

		'shouldNotClearRecommendationsForWPRocket' => [
			'config' => [
				'plugin' => 'wp-rocket/wp-rocket.php',
			],
			'expected' => [
				'should_clear' => false,
			],
		],

		'shouldNotClearRecommendationsForEmptyPluginPath' => [
			'config' => [
				'plugin' => '',
			],
			'expected' => [
				'should_clear' => false,
			],
		],

		'shouldNotClearRecommendationsForPartialMatch' => [
			'config' => [
				'plugin' => 'imagify/other.php',
			],
			'expected' => [
				'should_clear' => false,
			],
		],

		'shouldNotClearRecommendationsForCaseVariation' => [
			'config' => [
				'plugin' => 'Imagify/Imagify.php',
			],
			'expected' => [
				'should_clear' => false,
			],
		],
	],
];
