<?php

return [
	'shouldReturnDefaultWhenRUCSSDisabled' => [
		'config' => [
			'remove_unused_css' => 0,
			'interval' => null,
		],
		'expected' => null,
	],
	'shouldAddDefaultIntervalWhenRUCSSEnabled' => [
		'config' => [
			'remove_unused_css' => 1,
			'interval' => null,
		],
		'expected' => [
			'interval' => 60,
			'display'  => 'WP Rocket Remove Unused CSS pending jobs',
		],
	],
	'shouldAddFilteredIntervalWhenRUCSSEnabledAndFilter' => [
		'config' => [
			'remove_unused_css' => 1,
			'interval' => 120,
		],
		'expected' => [
			'interval' => 120,
			'display'  => 'WP Rocket Remove Unused CSS pending jobs',
		],
	],
];
