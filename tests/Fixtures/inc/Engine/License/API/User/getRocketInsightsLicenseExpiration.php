<?php

return [
	'testShouldReturnZeroWhenPerformanceMonitoringNotSet' => [
		'data'     => (object) [],
		'expected' => 0,
	],
	'testShouldReturnZeroWhenExpirationPropertyNotSet' => [
		'data'     => (object) [
			'performance_monitoring' => (object) [],
		],
		'expected' => 0,
	],
	'testShouldReturnExpirationTimestampWhenSet' => [
		'data'     => (object) [
			'performance_monitoring' => (object) [
				'expiration' => 1893456000,
			],
		],
		'expected' => 1893456000,
	],
	'testShouldCastExpirationToInt' => [
		'data'     => (object) [
			'performance_monitoring' => (object) [
				'expiration' => '1893456000',
			],
		],
		'expected' => 1893456000,
	],
	'testShouldReturnZeroWhenExpirationIsZero' => [
		'data'     => (object) [
			'performance_monitoring' => (object) [
				'expiration' => 0,
			],
		],
		'expected' => 0,
	],
];
