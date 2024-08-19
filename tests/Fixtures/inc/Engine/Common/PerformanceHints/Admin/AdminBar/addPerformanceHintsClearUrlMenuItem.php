<?php

return [
	'testShouldReturnNullWhenLocalEnvironment' => [
		'config'   => [
			'environment'       => 'local',
			'is_admin'          => false,
			'post'              => (object) [
				'post_type'   => 'post',
				'post_status' => 'publish',
			],
			'can_display_options' => true,
			'factories'         => true,
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenAdmin' => [
		'config'   => [
			'environment'       => 'production',
			'is_admin'          => true,
			'post'              => (object) [
				'post_type'   => 'post',
				'post_status' => 'publish',
			],
			'can_display_options' => true,
			'factories'         => true,
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenOptionsNotDisplayed' => [
		'config'   => [
			'environment'       => 'production',
			'is_admin'          => false,
			'post'              => (object) [
				'post_type'   => 'post',
				'post_status' => 'draft',
			],
			'can_display_options' => false,
			'factories'         => true,
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenFactoriesIsEmpty' => [
		'config'   => [
			'environment'       => 'production',
			'is_admin'          => false,
			'post'              => (object) [
				'post_type'   => 'post',
				'post_status' => 'publish',
			],
			'can_display_options' => true,
			'factories'         => false,
		],
		'expected' => null,
	],
	'testShouldAddItemWithPerformanceHintsTitle' => [
		'config'   => [
			'environment'       => 'production',
			'is_admin'          => false,
			'post'              => (object) [
				'post_type'   => 'post',
				'post_status' => 'publish',
			],
			'can_display_options' => true,
			'factories'          => true,
		],
		'expected' => [
			'id'    => 'clear-performance-hints-data-url',
			'title' => 'Clear Performance Hints data of this URL',
		],
	],
];
