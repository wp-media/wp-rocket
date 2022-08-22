<?php

return [
	'testNotEnabledAndScheduleShouldDoCancel' => [
		'config' => [
			'has_next_schedule' => true,
		]
	],
	'testEnabledAndNoNextActionScheduledShouldSchedule' => [
		'config' => [
			'has_next_schedule' => false,
		]
	]
];

