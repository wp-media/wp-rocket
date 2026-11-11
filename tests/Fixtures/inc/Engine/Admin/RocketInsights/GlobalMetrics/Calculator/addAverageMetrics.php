<?php

return [
	'test_data' => [
		'shouldAddMetricsWhenStatusCompleted' => [
			'config'   => [
				'status'  => 'completed',
				'metrics'    => [
					'lcp'  => 2.5,
					'ttfb' => 0.8,
					'cls'  => 0.1,
					'tbt'  => 300,
				],
				'input_data' => [
					'score'      => 85,
					'pages_num'  => 5,
					'status'     => 'complete',
					'is_running' => false,
				],
			],
			'expected' => [
				'score'           => 85,
				'pages_num'       => 5,
				'status'          => 'complete',
				'is_running'      => false,
				'average_metrics' => [
					'lcp'  => 2.5,
					'ttfb' => 0.8,
					'cls'  => 0.1,
					'tbt'  => 300,
				],
			],
		],

		'shouldNotAddMetricsWhenStatusInProgress' => [
			'config'   => [
				'status'  => 'completed',
				'metrics'    => [],
				'input_data' => [
					'score'      => 0,
					'pages_num'  => 0,
					'status'     => 'pending',
					'is_running' => false,
				],
			],
			'expected' => [
				'score'      => 0,
				'pages_num'  => 0,
				'status'     => 'pending',
				'is_running' => false,
				'average_metrics' => [],
			],
		],
	],
];
