<?php

return [
	'testShouldReturnNullWithInvalidLicense' => [
		'config'   => [
			'rocket_valid_key' 	=> false,
			'environment'       => 'production',
			'is_admin'          => true,
			'atf_context'       => true,
			'current_user_can'  => true,
		],
		'expected' => null,
	],
	'testShouldAddItemWithPerformanceHintTitle' => [
		'config'   => [
			'rocket_valid_key' 	=> true,
			'environment'       => 'production',
			'is_admin'          => true,
			'atf_context'       => true,
			'current_user_can'  => true,
		],
		'expected' => [
			'id'    => 'clear-performance-hints',
			'title' => 'Clear Performance Hints data',
		],
	],
	'testShouldReturnNullWhenLocalEnvironment' => [
		'config'   => [
			'rocket_valid_key' 	=> true,
			'environment'       => 'local',
			'is_admin'          => false,
			'atf_context'       => false,
			'current_user_can'  => true,
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenNotAdmin' => [
		'config'   => [
			'rocket_valid_key' 	=> true,
			'environment'       => 'production',
			'is_admin'          => false,
			'atf_context'       => false,
			'current_user_can'  => true,
		],
		'expected' => null,
	],
];
