<?php

return [
	'testShouldReturnFalseWhenFilterIsFalse' => [
		'config'   => [
			'filter_value' => false,
		],
		'expected' => false,
	],
	'testShouldReturnTrueWhenFilterIsTrue'   => [
		'config'   => [
			'filter_value' => true,
		],
		'expected' => true,
	],
];
