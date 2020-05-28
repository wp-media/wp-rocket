<?php

return [
	'testShouldNotEnqueueScript' => [
		'config'   => [
			'options' => [
				'async_css' => 0,
			],
		],
		'expected' => false,
	],
	'testShouldEnqueueScript' => [
		'config'   => [
			'options' => [
				'async_css' => 1,
			],
		],
		'expected' => true,
	],
];
