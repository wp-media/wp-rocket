<?php

return [
	'settings' => [
		'cache_mobile'            => 0,
		'do_caching_mobile_files' => 0,
	],

	'test_data' => [
		[
			'settings' => [],
			'expected' => false,
		],
		[
			'settings' => [
				'cache_mobile' => 1,
			],
			'expected' => false,
		],
		[
			'settings' => [
				'do_caching_mobile_files' => 1,
			],
			'expected' => false,
		],
		[
			'settings' => [
				'cache_mobile'            => 1,
				'do_caching_mobile_files' => 1,
			],
			'expected' => true,
		],
	],
];
