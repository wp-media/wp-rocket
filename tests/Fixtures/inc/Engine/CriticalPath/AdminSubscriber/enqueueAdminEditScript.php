<?php

return [

	'testShouldNotEnqueueScriptDifferentPage' => [
		'config'   => [
			'page' => 'options-general.php',
		],
		'expected' => false,
	],

	'testShouldNotEnqueueScriptDisabledWarning' => [
		'config'   => [
			'page'               => 'edit.php',
			'options'            => [
				'async_css' => 0,
			],
			'post'               => (object) [
				'ID'          => 1,
				'post_status' => 'draft',
				'post_type'   => 'post',
			],
			'is_option_excluded' => true,
		],
		'expected' => false,
	],

	'testShouldNotEnqueueScriptPostNotPublishedAndOptionExcludedWarning' => [
		'config'   => [
			'page'               => 'post.php',
			'options'            => [
				'async_css' => 1,
			],
			'post'               => (object) [
				'ID'          => 1,
				'post_status' => 'draft',
				'post_type'   => 'post',
			],
			'is_option_excluded' => true,
		],
		'expected' => false,
	],

	'testShouldNotEnqueueScriptPostNotPublishedWarning' => [
		'config'   => [
			'page'               => 'edit.php',
			'options'            => [
				'async_css' => 1,
			],
			'post'               => (object) [
				'ID'          => 1,
				'post_status' => 'draft',
				'post_type'   => 'post',
			],
			'is_option_excluded' => false,
		],
		'expected' => false,
	],

	'testShouldNotEnqueueScriptExcludedFromPostWarning' => [
		'config'   => [
			'page'               => 'edit.php',
			'options'            => [
				'async_css' => 1,
			],
			'post'               => (object) [
				'ID'          => 1,
				'post_status' => 'publish',
				'post_type'   => 'post',
			],
			'is_option_excluded' => true,
		],
		'expected' => false,
	],

	'testShouldEnqueueScript' => [
		'config'   => [
			'page'               => 'edit.php',
			'options'            => [
				'async_css' => 1,
			],
			'post'               => (object) [
				'ID'          => 1,
				'post_status' => 'publish',
				'post_type'   => 'post',
			],
			'is_option_excluded' => false,
		],
		'expected' => true,
	],
];
