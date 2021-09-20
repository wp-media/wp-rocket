<?php

return [
	'shouldReturnSameWhenNative' => [
		'config' => [
			'is_native'  => true,
			'exclusions' => [
				'data-src',
			],
		],
		'expected' => [
			'data-src',
		],
	],
	'shouldReturnUpdatedWhenNotNative' => [
		'config' => [
			'is_native'  => false,
			'exclusions' => [
				'data-src',
			],
		],
		'expected' => [
			'data-src',
			'data-skip-lazy',
			'skip-lazy',
		],
	],
];
