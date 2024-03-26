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
			'display'  => 'WP Rocket process pending jobs',
		],
	],
	'shouldAddFilteredIntervalWhenRUCSSEnabledAndFilter' => [
		'config' => [
			'remove_unused_css' => 1,
			'interval' => 120,
		],
		'expected' => [
			'interval' => 120,
			'display'  => 'WP Rocket process pending jobs',
		],
	],
	'shouldAddDefaultIntervalWhenRUCSSEnabledForDeletingFailedRows' => [
		'config' => [
			'remove_unused_css' => 1,
			'interval' => null,
		],
		'expected' => [
			'interval' => 259200,
			'display'  => 'WP Rocket clear failed jobs',
		],
	],
	'shouldAddDefaultIntervalWhenRUCSSEnabledForDeletingFailedRowsWithFilter' => [
		'config' => [
			'remove_unused_css' => 1,
			'interval' => 604800,
		],
		'expected' => [
			'interval' => 604800,
			'display'  => 'WP Rocket clear failed jobs',
		]
	],
];
