<?php

return [
	'testShouldReturnTrueWhenFilterIsTrue'          => [
		'config'   => [
			'filter_value' => true,
		],
		'expected' => true,
	],
	'testShouldReturnFalseWhenFilterIsFalse'        => [
		'config'   => [
			'filter_value' => false,
		],
		'expected' => false,
	],
	'testShouldReturnTrueWhenFilterIsTruthyNonBool' => [
		'config'   => [
			'filter_value' => 'yes',
		],
		'expected' => true,
	],
];
