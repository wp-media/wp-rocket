<?php

return [
	'shouldClearCronWhenRUCSSDisabledAndScheduled' => [
		'config' => [
			'remove_unused_css' => 0,
			'scheduled'         => true,
		],
		'expected' => false,
	],
	'shouldNotScheduleCronWhenRUCSSDisabled' => [
		'config' => [
			'remove_unused_css' => 0,
			'scheduled'         => false,
		],
		'expected' => false,
	],
	'shouldKeepCronWhenRUCSSEnabledAndAlreadyScheduled' => [
		'config' => [
			'remove_unused_css' => 1,
			'scheduled'         => true,
		],
		'expected' => true,
	],
	'shouldScheduleCronWhenRUCSSEnabled' => [
		'config' => [
			'remove_unused_css' => 1,
			'scheduled'         => false,
		],
		'expected' => true,
	],
];
