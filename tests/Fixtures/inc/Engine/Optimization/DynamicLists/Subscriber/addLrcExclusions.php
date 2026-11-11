<?php

return [
	'shouldReturnUpdatedArray' => [
		'config' => [
			'dynamic_lists' => (object) [
				'lazy_rendering_exclusions' => [
					'a'
				]
			],
			'exclusions' => ['p', 'div']
		],
		'expected' => [
			'p',
			'div',
			'a'
		],
	],
	'shouldReturnUpdatedArrayWhenOriginalEmpty' => [
		'config' => [
			'dynamic_lists' => (object) [
				'lazy_rendering_exclusions' => [
				]
			],
			'exclusions' => ['p', 'div']
		],
		'expected' => [
			'p',
			'div',
		],
	],
];
