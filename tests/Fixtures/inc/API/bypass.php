<?php

return [
	'test_data' => [
		'testShouldReturnFalseWhenNoQueryString' => [
			'wp' => (object) [
				'query_vars' => [],
				'request'    => 'http://example.org',
			],
			'url' => 'http://example.org',
			'expected' => false,
		],
		'testShouldReturnFalseWhenQueryStringNotNowprocket' => [
			'wp' => (object) [
				'query_vars' => [
					'key' => 'value',
				],
				'request'    => 'http://example.org',
			],
			'url' => 'http://example.org?key=value',
			'expected' => false,
		],
		'testShouldReturnTrueWhenQueryStringNowprocket' => [
			'wp' => (object) [
				'query_vars' => [
					'nowprocket' => '1',
				],
				'request'    => 'http://example.org',
			],
			'url' => 'http://example.org?nowprocket=1',
			'expected' => true,
		],
		'testShouldReturnTrueWhenQueryStringNowprocketAndAnother' => [
			'wp' => (object) [
				'query_vars' => [
					'nowprocket' => '1',
					'key'        => 'value',
				],
				'request'    => 'http://example.org',
			],
			'url' => 'http://example.org?nowprocket=1&key=value',
			'expected' => true,
		],
	],
];
