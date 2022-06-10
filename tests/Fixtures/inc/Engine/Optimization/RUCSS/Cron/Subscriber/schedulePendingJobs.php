<?php

return [
	'shouldRemoveScheduledWhenRUCSSDisabledAndScheduled' => [
		'config' => [
			'remove_unused_css' => 0,
			'scheduled' => true,
		],
		'expected' => false,
	],
	'shouldNotScheduleCronWhenRUCSSDisabled' => [
		'config' => [
			'remove_unused_css' => 0,
			'scheduled' => false,
		],
		'expected' => false,
	],
	'shouldNotScheduleCronWhenRUCSSEnabledAndScheduled' => [
		'config' => [
			'remove_unused_css' => 1,
			'scheduled' => true,
		],
		'expected' => true,
	],
	'shouldScheduleCron' => [
		'config' => [
			'remove_unused_css' => 1,
			'scheduled' => false,
		],
		'expected' => true,
	],
];
