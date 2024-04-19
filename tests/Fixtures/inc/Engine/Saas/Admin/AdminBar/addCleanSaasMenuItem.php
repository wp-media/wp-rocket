<?php

return [
	'testShouldReturnNullWhenLocalEnvironment' => [
		'config'   => [
			'environment'       => 'local',
			'is_admin'          => false,
			'atf_context'       => false,
			'remove_unused_css' => 0,
			'current_user_can'  => true,
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenNotAdmin' => [
		'config'   => [
			'environment'       => 'production',
			'is_admin'          => false,
			'atf_context'       => false,
			'remove_unused_css' => 0,
			'current_user_can'  => true,
		],
		'expected' => null,
	],
	'testShouldAddItemWithDefaultTitle' => [
		'config'   => [
			'environment'       => 'production',
			'is_admin'          => true,
			'atf_context'       => true,
			'remove_unused_css' => 0,
			'current_user_can'  => true,
		],
		'expected' => [
			'id'    => 'clean-saas',
			'title' => 'Clear Critical Images',
		],
	],
	'testShouldAddItemWithRUCSSTitle' => [
		'config'   => [
			'environment'       => 'production',
			'is_admin'          => true,
			'atf_context'       => true,
			'remove_unused_css' => 1,
			'current_user_can'  => true,
		],
		'expected' => [
			'id'    => 'clean-saas',
			'title' => 'Clear Used CSS',
		],
	],
];
