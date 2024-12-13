<?php

return [
	'testShouldDoNothingWhenOldValueAndNewValueAreNotSet' => [
		'old_value' => [],
		'value'     => [],
		'expected'  => false,
	],
	'testShouldDoNothingWhenOldValueAndNewValueAreTheSame' => [
		'old_value' => [
			'host_fonts_locally' => 0,
		],
		'value'     => [
			'host_fonts_locally' => 0,
		],
		'expected'  => false,
	],
	'testShouldDeleteAllFilesWhenOldValueAndNewValueAreDifferent' => [
		'old_value' => [
			'host_fonts_locally' => 0,
		],
		'value'     => [
			'host_fonts_locally' => 1,
		],
		'expected'  => true,
	],
];
