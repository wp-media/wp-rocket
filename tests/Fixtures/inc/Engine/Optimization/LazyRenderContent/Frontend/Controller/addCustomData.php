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
		],
	],
];
