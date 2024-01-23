<?php

return [
	'testNotEnabledShouldDoNothing' => [
		'config' => [
			'is_enabled' => false,
			'has_next_schedule' => false,
			'next_success' => false,
		]
	],
	'testNotEnabledAndScheduleShouldDoCancel' => [
		'config' => [
			'is_enabled' => false,
			'has_next_schedule' => true,
			'next_success' => false,
		]
	],
	'testEnabledAndNextActionScheduledShouldDoNothing' => [
		'config' => [
			'is_enabled' => true,
			'has_next_schedule' => true,
			'next_success' => true,
		]
	],
	'testEnabledAndNoNextActionScheduledShouldSchedule' => [
		'config' => [
			'is_enabled' => true,
			'has_next_schedule' => false,
			'next_success' => false,
		]
	]
];
