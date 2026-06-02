<?php

return [
	'shouldAddDataWithFreshInstall'                 => [
		'sidebar_value'      => 1,
		'has_fresh_install'  => true,
		'input_data'         => [],
		'expected'           => [
			'show_sidebar'                => 1,
			'is_fresh_sidebar_install'    => true,
		],
	],
	'shouldAddDataWithoutFreshInstall'             => [
		'sidebar_value'      => 1,
		'has_fresh_install'  => false,
		'input_data'         => [],
		'expected'           => [
			'show_sidebar'                => 1,
			'is_fresh_sidebar_install'    => false,
		],
	],
	'shouldMergeDataWithFreshInstall'              => [
		'sidebar_value'      => 0,
		'has_fresh_install'  => true,
		'input_data'         => [
			'nonce'   => 'abc123',
			'is_free' => '0',
		],
		'expected'           => [
			'nonce'                       => 'abc123',
			'is_free'                     => '0',
			'show_sidebar'                => 0,
			'is_fresh_sidebar_install'    => true,
		],
	],
	'shouldMergeDataWithoutFreshInstall'           => [
		'sidebar_value'      => 1,
		'has_fresh_install'  => false,
		'input_data'         => [
			'nonce'   => 'abc123',
			'is_free' => '0',
		],
		'expected'           => [
			'nonce'                       => 'abc123',
			'is_free'                     => '0',
			'show_sidebar'                => 1,
			'is_fresh_sidebar_install'    => false,
		],
	],
];
