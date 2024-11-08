<?php

return [
	'testShouldReturnFalseWhenBypass' => [
		'config' => [
			'bypass' => true,
			'do_not_optimize' => false,
			'option' => true,
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenDoNotOptimize' => [
		'config' => [
			'bypass' => false,
			'do_not_optimize' => true,
			'option' => true,
		],
		'expected' => false,
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
