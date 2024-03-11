<?php
return [
	'pendingShouldAddToTheQueue' => [
		'config' => [
			'rocket_preload_cache_pending_jobs_cron_rows_count' => 10,
			'manual_preload' => true,
			'rocket_preload_outdated' => -1,
			'rows' => [
				[
					'url' => 'http://example.org/test',
					'status' => 'pending'
				],
				[
					'url' => 'http://example.org/test2',
					'status' => 'pending'
				],
			],
			'actions' => [

			]
		],
		'expected' => [
			'rows' => [
				[
					'url' => 'http://example.org/test',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test2',
					'status' => 'in-progress'
				],
			],
			'actions' => [
				[
					'exists' => true,
					'args' => [
						'hook'   => 'rocket_preload_job_preload_url',
						'args' => ['http://example.org/test']
					],
				],
				[
					'exists' => true,
					'args' => [
						'hook'   => 'rocket_preload_job_preload_url',
						'args' => ['http://example.org/test2']
					],
				],
			]
		]
	],
	'InProgressAndInQueueShouldNotFail' => [
		'config' => [
			'rocket_preload_cache_pending_jobs_cron_rows_count' => 10,
			'manual_preload' => true,
			'rocket_preload_outdated' => -1,
			'rows' => [
				[
					'url' => 'http://example.org/test',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test2',
					'status' => 'pending'
				],
				[
					'url' => 'http://example.org/test3',
					'status' => 'in-progress'
				],
			],
			'actions' => [
				'http://example.org/test'
			]
		],
		'expected' => [
			'rows' => [
				[
					'url' => 'http://example.org/test',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test2',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test3',
					'status' => 'failed'
				],
			],
			'actions' => [
				[
					'exists' => true,
					'args' => [
						'hook'   => 'rocket_preload_job_preload_url',
						'args' => ['http://example.org/test']
					],
				],
				[
					'exists' => true,
					'args' => [
						'hook'   => 'rocket_preload_job_preload_url',
						'args' => ['http://example.org/test2']
					],
				],
			]
		]
	],
	'InProgressShouldNotExceedMaxQueue' => [
		'config' => [
			'rocket_preload_cache_pending_jobs_cron_rows_count' => 5,
			'manual_preload' => true,
			'rocket_preload_outdated' => 1000,
			'rows' => [
				[
					'url' => 'http://example.org/test',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test2',
					'status' => 'pending'
				],
				[
					'url' => 'http://example.org/test3',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test4',
					'status' => 'pending'
				],
				[
					'url' => 'http://example.org/test5',
					'status' => 'pending'
				],
			],
			'actions' => [
				'http://example.org/test'
			]
		],
		'expected' => [
			'rows' => [
				[
					'url' => 'http://example.org/test',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test2',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test3',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test4',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test5',
					'status' => 'pending'
				],
			],
			'actions' => [
				[
					'exists' => true,
					'args' => [
						'hook'   => 'rocket_preload_job_preload_url',
						'args' => ['http://example.org/test']
					],
				],
				[
					'exists' => true,
					'args' => [
						'hook'   => 'rocket_preload_job_preload_url',
						'args' => ['http://example.org/test2']
					],
				],
				[
					'exists' => false,
					'args' => [
						'hook'   => 'rocket_preload_job_preload_url',
						'args' => ['http://example.org/test3']
					],
				],
				[
					'exists' => true,
					'args' => [
						'hook'   => 'rocket_preload_job_preload_url',
						'args' => ['http://example.org/test4']
					],
				],
				[
					'exists' => false,
					'args' => [
						'hook'   => 'rocket_preload_job_preload_url',
						'args' => ['http://example.org/test5']
					],
				],
			]
		]
	],
	'BigBatchShouldNotFail' => [
		'config' => [
			'rocket_preload_cache_pending_jobs_cron_rows_count' => 3,
			'manual_preload' => true,
			'rocket_preload_outdated' => -1,
			'rows' => [
				[
					'url' => 'http://example.org/test',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test2',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test3',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test4',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test5',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test6',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test7',
					'status' => 'in-progress'
				],
			],
			'actions' => [
				'http://example.org/test',
				'http://example.org/test3',
				'http://example.org/test4',
				'http://example.org/test5',
				'http://example.org/test6',
				'http://example.org/test7',
			]
		],
		'expected' => [
			'rows' => [
				[
					'url' => 'http://example.org/test',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test2',
					'status' => 'failed'
				],
				[
					'url' => 'http://example.org/test3',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test4',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test5',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test6',
					'status' => 'in-progress'
				],
				[
					'url' => 'http://example.org/test7',
					'status' => 'in-progress'
				],
			],
			'actions' => [
				[
					'exists' => true,
					'args' => [
						'hook'   => 'rocket_preload_job_preload_url',
						'args' => ['http://example.org/test']
					],
				],
				[
					'exists' => false,
					'args' => [
						'hook'   => 'rocket_preload_job_preload_url',
						'args' => ['http://example.org/test2']
					],
				],
				[
					'exists' => true,
					'args' => [
						'hook'   => 'rocket_preload_job_preload_url',
						'args' => ['http://example.org/test3']
					],
				],
				[
					'exists' => true,
					'args' => [
						'hook'   => 'rocket_preload_job_preload_url',
						'args' => ['http://example.org/test4']
					],
				],
				[
					'exists' => true,
					'args' => [
						'hook'   => 'rocket_preload_job_preload_url',
						'args' => ['http://example.org/test5']
					],
				],
				[
					'exists' => true,
					'args' => [
						'hook'   => 'rocket_preload_job_preload_url',
						'args' => ['http://example.org/test6']
					],
				],
				[
					'exists' => true,
					'args' => [
						'hook'   => 'rocket_preload_job_preload_url',
						'args' => ['http://example.org/test7']
					],
				],
			]
		]
	],
];
