<?php
return [
	'rocketRucssAfterClearingUsedcssShouldCleanUrl' => [
		'config' => [
			'hook' => 'rocket_rucss_after_clearing_usedcss',
			'url' => 'http://example.org/about/',
		],
		'expected' => [
			'url' => 'http://example.org/about/kinsta-clear-cache/',
			'config' => [
				'blocking' => false,
				'timeout'  => 0.01,
			]
		]
	],
	'rocketRucssCompleteJobStatusSshouldCleanUrl' => [
		'config' => [
			'hook' => 'rocket_saas_complete_job_status',
			'url' => 'http://example.org/about/',
		],
		'expected' => [
			'url' => 'http://example.org/about/kinsta-clear-cache/',
			'config' => [
				'blocking' => false,
				'timeout'  => 0.01,
			]
		]
	],
	'afterRocketCleanFileStatusSshouldCleanUrl' => [
		'config' => [
			'hook' => 'after_rocket_clean_file',
			'url' => 'http://example.org/about/',
		],
		'expected' => [
			'url' => 'http://example.org/about/kinsta-clear-cache/',
			'config' => [
				'blocking' => false,
				'timeout'  => 0.01,
			]
		]
	],
];
