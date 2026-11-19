<?php

return [
	'testShouldSetLimitReachedFalseWhenNoPagesAdded' => [
		'config'   => [
			'page_count' => 0,
		],
		'expected' => false,
	],
	'testShouldSetLimitReachedFalseWhenUnderLimit'   => [
		'config'   => [
			'page_count' => 2,
		],
		'expected' => false,
	],
	'testShouldSetLimitReachedTrueWhenAtLimit'       => [
		'config'   => [
			'page_count' => 3,
		],
		'expected' => true,
	],
	'testShouldSetLimitReachedTrueWhenOverLimit'     => [
		'config'   => [
			'page_count' => 4,
		],
		'expected' => true,
	],
];
