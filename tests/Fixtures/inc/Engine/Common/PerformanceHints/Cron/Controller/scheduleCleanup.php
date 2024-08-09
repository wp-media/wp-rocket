<?php

return [
	'shouldNotScheduleCronDueToExistingSchedule' => [
		'config' => [
			'scheduled' => false,
		],
		'expected' => true
	],
	'shouldScheduleCron' => [
		'config' => [
			'scheduled' => true,
		],
		'expected' => true
	],
];
