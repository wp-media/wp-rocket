<?php

return [
	'shouldAddShowSidebarOneToEmptyData'         => [
		'sidebar_value' => 1,
		'input_data'    => [],
		'expected'      => [
			'show_sidebar' => 1,
		],
	],
	'shouldAddShowSidebarZeroToEmptyData'        => [
		'sidebar_value' => 0,
		'input_data'    => [],
		'expected'      => [
			'show_sidebar' => 0,
		],
	],
	'shouldMergeShowSidebarIntoExistingData'     => [
		'sidebar_value' => 1,
		'input_data'    => [
			'nonce'   => 'abc123',
			'is_free' => '0',
		],
		'expected'      => [
			'nonce'        => 'abc123',
			'is_free'      => '0',
			'show_sidebar' => 1,
		],
	],
	'shouldMergeShowSidebarZeroIntoExistingData' => [
		'sidebar_value' => 0,
		'input_data'    => [
			'nonce'   => 'abc123',
			'is_free' => '0',
		],
		'expected'      => [
			'nonce'        => 'abc123',
			'is_free'      => '0',
			'show_sidebar' => 0,
		],
	],
];
