<?php

return [
	'test_data' => [
		'shouldAddMetricsWhenTestsExist' => [
			'config'   => [
				'has_tests'  => true,
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

		'shouldNotAddMetricsWhenNoTests' => [
			'config'   => [
				'has_tests'  => false,
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
			],
		],

		'shouldPreserveExistingData' => [
			'config'   => [
				'has_tests'  => true,
				'metrics'    => [
					'lcp'  => 3.2,
					'ttfb' => 1.0,
					'cls'  => 0.15,
					'tbt'  => 450,
				],
				'input_data' => [
					'score'      => 75,
					'pages_num'  => 10,
					'status'     => 'complete',
					'is_running' => false,
					'custom_key' => 'custom_value',
				],
			],
			'expected' => [
				'score'           => 75,
				'pages_num'       => 10,
				'status'          => 'complete',
				'is_running'      => false,
				'custom_key'      => 'custom_value',
				'average_metrics' => [
					'lcp'  => 3.2,
					'ttfb' => 1.0,
					'cls'  => 0.15,
					'tbt'  => 450,
				],
			],
		],
	],
];
