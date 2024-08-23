<?php

return [
	'testShouldReturnDataWithAllowed' => [
		'config'   => [
			'is_allowed' => true,
		],
		'data'     => [],
		'expected' => [
			'lrc_elements' => 'div, main, footer, section, article, header',
			'status'   => [
				'lrc' => true,
			],
			'lrc_threshold' => 1800
		],
	],
	'testShouldReturnDataWithNotAllowed' => [
		'config'   => [
			'is_allowed' => false,
		],
		'data'     => [],
		'expected' => [
			'lrc_elements' => 'div, main, footer, section, article, header',
			'status'   => [
				'lrc' => false,
			],
			'lrc_threshold' => 1800,
		],
	],
];
