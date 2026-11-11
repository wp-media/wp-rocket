<?php

return [
	'shouldReturnFalseWhenStatusIsNotCompleted' => [
		'config' => [
			'status' => 'in-progress',
			'fonts' => '["font1", "font2"]',
		],
		'expected' => false,
	],
	'shouldReturnFalseWhenFontsIsEmpty' => [
		'config' => [
			'status' => 'completed',
			'fonts' => '',
		],
		'expected' => false,
	],
	'shouldReturnFalseWhenFontsIsEmptyArray' => [
		'config' => [
			'status' => 'completed',
			'fonts' => '[]',
		],
		'expected' => false,
	],
	'shouldReturnTrueWhenStatusIsCompletedAndFontsIsNotEmpty' => [
		'config' => [
			'status' => 'completed',
			'fonts' => '["font1", "font2"]',
		],
		'expected' => true,
	],
];
