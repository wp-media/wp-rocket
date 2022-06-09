<?php
return [
	'everyMinuteShouldReturnSame' => [
		'config' => [
			'every_minute' => []
		],
		'expected' => [
			'every_minute' => []
		]
	],
	'noEveryMinuteShouldAdd' => [
		'config' => [],
		'expected' => [
			'every_minute' => [
				'interval' => 60,
				'display'  => 'Every minute',
			]
		]
	]
];
