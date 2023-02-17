<?php
return [
	'DoNothingWhenPastdueIsNotThere' => [
		'input' => [
			'test' => 'value',
		],
		'expected' => [
			'test' => 'value',
		],
	],
	'DoNothingWhenArrayIsEmpty' => [
		'input' => [],
		'expected' => [],
	],
	'HideWhenPastdueIsThere' => [
		'input' => [
			'test' => 'value',
			'past-due' => 'value',
		],
		'expected' => [
			'test' => 'value',
		],
	],
];
