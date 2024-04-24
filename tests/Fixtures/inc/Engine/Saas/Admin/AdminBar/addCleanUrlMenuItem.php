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
			'atf_context'       => true,
			'rucss_context' => true,
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
			'atf_context'       => true,
			'rucss_context' => true,
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
			'atf_context'       => true,
			'rucss_context' => true,
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenNotAllowed' => [
		'config'   => [
			'environment'       => 'production',
			'is_admin'          => false,
			'post'              => (object) [
				'post_type'   => 'post',
				'post_status' => 'publish',
			],
			'can_display_options' => true,
			'atf_context'       => false,
			'rucss_context' => false,
		],
		'expected' => null,
	],
	'testShouldAddItemWithDefaultTitle' => [
		'config'   => [
			'environment'       => 'production',
			'is_admin'          => false,
			'post'              => (object) [
				'post_type'   => 'post',
				'post_status' => 'publish',
			],
			'can_display_options' => true,
			'atf_context'       => true,
			'rucss_context' => false,
		],
		'expected' => [
			'id'    => 'clear-saas-url',
			'title' => 'Clear Critical Images of this URL',
		],
	],
	'testShouldAddItemWithRUCSSTitle' => [
		'config'   => [
			'environment'       => 'production',
			'is_admin'          => false,
			'post'              => (object) [
				'post_type'   => 'post',
				'post_status' => 'publish',
			],
			'can_display_options' => true,
			'atf_context'       => true,
			'rucss_context' => true,
		],
		'expected' => [
			'id'    => 'clear-saas-url',
			'title' => 'Clear Used CSS of this URL',
		],
	],
];
