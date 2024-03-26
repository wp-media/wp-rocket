<?php

return [
	'testShoulDoNothingWhenDisabled' => [
		'config' => [
			'filter' => false,
			'post_id' => 1,
			'url' => 'http://example.org',
		],
		'expected' => false,
	],
	'testShoulDoNothingURLFalse' => [
		'config' => [
			'filter' => true,
			'post_id' => 1,
			'url' => false,
		],
		'expected' => false,
	],
	'testShoulDeletePost' => [
		'config' => [
			'filter' => true,
			'post_id' => 1,
			'url' => 'http://example.org',
		],
		'expected' => true,
	],
];
