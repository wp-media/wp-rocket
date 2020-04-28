<?php

return [
	'testShouldDisplayOptionDisabledWarning' => [
			'config' => [
			'options' => [
				'async_css' => 0,
			],
			'post' => [
				'post_status' => 'draft',
			],
			'is_option_excluded' => true,
		],
		'expected' => 'Enable Optimize CSS delivery in WP Rocket settings to use this feature',
	],
	'testShouldDisplayPostNotPublishedWarning' => [
			'config' => [
			'options' => [
				'async_css' => 1,
			],
			'post' => [
				'post_status' => 'draft',
			],
			'is_option_excluded' => true,
		],
		'expected' => 'Publish the post to use this feature',
	],
	'testShouldDisplayOptionExcludedFromPostWarning' => [
			'config' => [
			'options' => [
				'async_css' => 1,
			],
			'post' => [
				'post_status' => 'publish',
			],
			'is_option_excluded' => true,
		],
		'expected' => 'Enable Optimize CSS delivery in the options above to use this feature',
	],
	'testShouldNoWarning' => [
			'config' => [
			'options' => [
				'async_css' => 1,
			],
			'post' => [
				'post_status' => 'publish',
			],
			'is_option_excluded' => false,
		],
		'expected' => '',
	],
];
