<?php

return [
	'vfs_dir' => 'wp-content/plugins/wp-rocket/views/settings',

	'test_data' => [
		'shouldBailWhenUserNotAuthorized' => [
			'config' => [
				'user-can'   => false,
				'filter-not-set' => true,
				'gf-minify'  => false,
			],
			'expect' => '',
		],

		'shouldBailWhenOptimizeFilterSetFalse' => [
			'config' => [
				'user-can' => true,
				'filter-not-set' => false,
				'gf-minify' => false,
			],
			'expect' => '',
		],

		'shouldBailWhenOptimizeGFEnabled' => [
			'config' => [
				'user-can'   => true,
				'filter-not-set' => true,
				'gf-minify'  => true,
			],
			'expect' => '',
		],

		'shouldShowWhenAuthAndOptimizeGFNotEnabled' => [
			'config' => [
				'user-can'   => true,
				'filter-not-set' => true,
				'gf-minify'  => false,
			],
			'expect' => 'setting output',
		],
	],
];
