<?php


return [
	'whenTimestampAndNextSheduleShouldUnshedule' => [
		'config' => [
			'timestamp' => 10210120,
			'next_scheduled' => 1021420,
			'schedule' => 'schedule',
		],
	],
	'whenNotimestampandNextSheduleShouldNotUnshedule' => [
		'config' => [
			'timestamp' => false,
			'next_scheduled' => 1021420,
			'schedule' => 'schedule',
		],
	],
	'whenTimestampandNoNextSheduleShouldUnscheduleAndShedule' => [
		'config' => [
			'timestamp' => 10210120,
			'next_scheduled' => false,
			'schedule' => 'schedule',
		],
	]
];
