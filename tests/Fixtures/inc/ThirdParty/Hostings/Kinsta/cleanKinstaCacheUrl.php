<?php
return [
	'rocketRucssAfterClearingUsedcssShouldCleanUrl' => [
		'config' => [
			'hook' => 'rocket_rucss_after_clearing_usedcss',
			'url' => 'url',
		],
		'expected' => [
			'url' => 'url/kinsta-clear-cache/',
			'config' => [
				'blocking' => false,
				'timeout'  => 0.01,
			]
		]
	],
	'rocketRucssCompleteJobStatusSshouldCleanUrl' => [
		'config' => [
			'hook' => 'rocket_rucss_complete_job_status',
			'url' => 'url',
		],
		'expected' => [
			'url' => 'url/kinsta-clear-cache/',
			'config' => [
				'blocking' => false,
				'timeout'  => 0.01,
			]
		]
	],
	'afterRocketCleanFileStatusSshouldCleanUrl' => [
		'config' => [
			'hook' => 'after_rocket_clean_file',
			'url' => 'url',
		],
		'expected' => [
			'url' => 'url/kinsta-clear-cache/',
			'config' => [
				'blocking' => false,
				'timeout'  => 0.01,
			]
		]
	],
];
