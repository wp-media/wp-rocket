<?php

return [
	'vfs_dir' => 'wp-content/plugins/wp-rocket/views/settings',

	'test_data' => [
		'shouldBailWhenUserNotAuthorized' => [
			'config' => [
				'user-can'   => false,
				'gf-minify'  => false,
			],
			'expect' => ''
		],

		'shouldBailWhenMinifyEnabled' => [
			'config' => [
				'user-can'   => true,
				'gf-minify'  => true,
			],
			'expect' => '',
		],

		'shouldShowWhenAuthAndMinifyNotEnabled' => [
			'config' => [
				'user-can'   => true,
				'gf-minify'  => false,
			],
			'expect' => 'setting output',
		],
	],
];
