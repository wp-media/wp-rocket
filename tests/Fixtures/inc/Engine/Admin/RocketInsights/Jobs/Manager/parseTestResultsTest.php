<?php

return [
	'test_data' => [
		'shouldReturnDefaultsWhenDataMissing' => [
			'config'   => [
				'api_response' => [],
			],
			'expected' => [
				'report_url'        => '',
				'performance_score' => 0,
				'metric_data'       => null,
			],
		],
		'shouldReturnDefaultsWhenDataDataMissing' => [
			'config'   => [
				'api_response' => [
					'data' => [],
				],
			],
			'expected' => [
				'report_url'        => '',
				'performance_score' => 0,
				'metric_data'       => null,
			],
		],
		'shouldParseBasicTestResults' => [
			'config'   => [
				'api_response' => [
					'data' => [
						'data' => [
							'report_url'        => 'http://gtmetrix.com/report/123',
							'performance_score' => 85,
						],
					],
				],
			],
			'expected' => [
				'report_url'        => 'http://gtmetrix.com/report/123',
				'performance_score' => 85,
				'metric_data'       => [
					'report_url'        => 'http://gtmetrix.com/report/123',
					'performance_score' => 85,
				],
			],
		],
		'shouldParseTestResultsWithMetrics' => [
			'config'   => [
				'api_response' => [
					'data' => [
						'data' => [
							'report_url'        => 'http://gtmetrix.com/report/456',
							'performance_score' => 90,
							'lcp'               => 2.5,
							'tbt'               => 150,
							'cls'               => 0.05,
							'ttfb'              => 0.8,
						],
					],
				],
			],
			'expected' => [
				'report_url'        => 'http://gtmetrix.com/report/456',
				'performance_score' => 90,
				'metric_data'       => [
					'report_url'        => 'http://gtmetrix.com/report/456',
					'performance_score' => 90,
					'lcp'               => 2.5,
					'tbt'               => 150,
					'cls'               => 0.05,
					'ttfb'              => 0.8,
				],
			],
		],
		'shouldHandlePartialMetrics' => [
			'config'   => [
				'api_response' => [
					'data' => [
						'data' => [
							'report_url'        => 'http://gtmetrix.com/report/789',
							'performance_score' => 75,
							'lcp'               => 3.2,
							'cls'               => 0.15,
						],
					],
				],
			],
			'expected' => [
				'report_url'        => 'http://gtmetrix.com/report/789',
				'performance_score' => 75,
				'metric_data'       => [
					'report_url'        => 'http://gtmetrix.com/report/789',
					'performance_score' => 75,
					'lcp'               => 3.2,
					'cls'               => 0.15,
				],
			],
		],
		'shouldHandleZeroValues' => [
			'config'   => [
				'api_response' => [
					'data' => [
						'data' => [
							'report_url'        => 'http://gtmetrix.com/report/000',
							'performance_score' => 95,
							'lcp'               => 1.2,
							'tbt'               => 0,
							'cls'               => 0.0,
							'ttfb'              => 0.5,
						],
					],
				],
			],
			'expected' => [
				'report_url'        => 'http://gtmetrix.com/report/000',
				'performance_score' => 95,
				'metric_data'       => [
					'report_url'        => 'http://gtmetrix.com/report/000',
					'performance_score' => 95,
					'lcp'               => 1.2,
					'tbt'               => 0,
					'cls'               => 0.0,
					'ttfb'              => 0.5,
				],
			],
		],
	],
];
