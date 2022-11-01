<?php
return [
	'shouldNotRecreateASTable' => [
		'config' => [
			'found_as_tables' => [
                'wp_actionscheduler_actions',
                'wp_actionscheduler_claims',
                'wp_actionscheduler_groups',
                'wp_actionscheduler_logs',
            ],
		],
		'expected' => false,
	],
	'shouldRecreateASTable' => [
		'config' => [
			'found_as_tables' => [
                'wp_actionscheduler_claims',
                'wp_actionscheduler_groups',
                'wp_actionscheduler_logs',
            ],
		],
		'expected' => true,
	],
];
