<?php

return [
	'testShouldReturntrueWhenBypass' => [
		'config' => [
			'bypass' => true,
			'do_not_optimize' => false,
			'option' => true,
		],
		'expected' => true,
	],
	'testShouldReturnTrueWhenDoNotOptimize' => [
		'config' => [
			'bypass' => false,
			'do_not_optimize' => true,
			'option' => true,
		],
		'expected' => true,
	],
	'testShouldReturnFalseWhenOptionDisabled' => [
		'config' => [
			'bypass' => false,
			'do_not_optimize' => true,
			'option' => false,
		],
		'expected' => false,
	],
	'testShouldReturnTrueWhenOptionEnabled' => [
		'config' => [
			'bypass' => false,
			'do_not_optimize' => false,
			'option' => true,
		],
		'expected' => true,
	],
];
