<?php
return [
	'testDoesnTExistsShouldReturnSame' => [
		'config' => [
			'params' => [],
			'exists' => false,
			'slug' => 'calendar'
		],
		'expected' => [],
	],
	'testShouldReturnAsExpected' => [
		'config' => [
			'params' => [],
			'exists' => true,
			'slug' => 'calendar'
		],
		'expected' => ['/calendar/20(.*)'],
	]
];
