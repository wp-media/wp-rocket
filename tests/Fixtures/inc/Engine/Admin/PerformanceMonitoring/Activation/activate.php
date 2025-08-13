<?php

return [
	'testShouldScheduleHomepageTestsAsExpected' => [
		'config' => [
			'settings' => [
				'version' => '',
			]
		],
		'expected' => [
			'schedule_actions' => 2
		],
	],
	'testShouldNotScheduleHomepageOnUpgrade' => [
		'config' => [
			'settings' => [
				'version' => '3.19',
			]
		],
		'expected' => [
			'schedule_actions' => 0
		]
	]
];
