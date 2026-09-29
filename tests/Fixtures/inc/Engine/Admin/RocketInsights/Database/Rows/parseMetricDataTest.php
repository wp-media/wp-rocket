<?php

return [
	'test_data' => [
		'shouldReturnNullForNullInput' => [
			'config'   => [
				'input' => null,
			],
			'expected' => null,
		],
		'shouldReturnNullForEmptyString' => [
			'config'   => [
				'input' => '',
			],
			'expected' => null,
		],
		'shouldReturnArrayWhenInputIsArray' => [
			'config'   => [
				'input' => [
					'lcp'  => 2.5,
					'tbt'  => 150,
					'cls'  => 0.05,
					'ttfb' => 0.8,
				],
			],
			'expected' => [
				'lcp'  => 2.5,
				'tbt'  => 150,
				'cls'  => 0.05,
				'ttfb' => 0.8,
			],
		],
		'shouldDecodeJsonString' => [
			'config'   => [
				'input' => '{"lcp":2.5,"tbt":150,"cls":0.05,"ttfb":0.8}',
			],
			'expected' => [
				'lcp'  => 2.5,
				'tbt'  => 150,
				'cls'  => 0.05,
				'ttfb' => 0.8,
			],
		],
		'shouldHandlePartialMetrics' => [
			'config'   => [
				'input' => '{"lcp":3.2,"cls":0.15}',
			],
			'expected' => [
				'lcp' => 3.2,
				'cls' => 0.15,
			],
		],
		'shouldHandleZeroValues' => [
			'config'   => [
				'input' => [
					'lcp'  => 1.2,
					'tbt'  => 0,
					'cls'  => 0.0,
					'ttfb' => 0.5,
				],
			],
			'expected' => [
				'lcp'  => 1.2,
				'tbt'  => 0,
				'cls'  => 0.0,
				'ttfb' => 0.5,
			],
		],
		'shouldHandleNullValuesInArray' => [
			'config'   => [
				'input' => [
					'lcp'  => 3.2,
					'tbt'  => null,
					'cls'  => 0.15,
					'ttfb' => null,
				],
			],
			'expected' => [
				'lcp'  => 3.2,
				'tbt'  => null,
				'cls'  => 0.15,
				'ttfb' => null,
			],
		],
	],
];
