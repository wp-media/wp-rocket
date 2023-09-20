<?php

return [
	'shouldAddWhenDivi' => [
		'config'   => [
			'theme-name'     => 'Divi',
			'theme-template' => 'Divi',
			'disabled-items' => [
				'Thing 1',
				'Thing 2',
			],
		],
		'expected' => [
			'Thing 1',
			'Thing 2',
			'Divi',
		],
	],

	'shouldAddWhenDiviChild' => [
		'config'   => [
			'theme-name'     => 'Divi Child',
			'theme-template' => 'divi',
			'disabled-items' => [
				'Thing 1',
				'Thing 2',
			],
		],
		'expected' => [
			'Thing 1',
			'Thing 2',
			'Divi',
		],
	],

	'shouldNotAddWhenNotDivi' => [
		'config'   => [
			'theme-name'     => 'TwentyTwenty',
			'theme-template' => 'TwentyTwenty',
			'disabled-items' => [
				'Thing 1',
				'Thing 2',
			],
		],
		'expected' => [
			'Thing 1',
			'Thing 2',
		],
	],
];
