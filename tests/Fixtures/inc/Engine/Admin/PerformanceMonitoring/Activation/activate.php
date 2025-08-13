<?php

return [
	'testShouldScheduleHomepageTestsAsExpected' => [
		'config' => [
			'is_first_install' => true,
		],
		'expected' => [
			'schedule_actions' => 2
		],
	],
	'testShouldNotScheduleHomepageOnUpgrade' => [
		'config' => [
			'is_first_install' => false
		],
		'expected' => [
			'schedule_actions' => 0
		]
	]
];