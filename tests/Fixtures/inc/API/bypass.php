<?php

return [
	'test_data' => [
		'testShouldReturnFalseWhenNoQueryString' => [
			'query_vars' => [],
			'expected' => false,
		],
		'testShouldReturnFalseWhenQueryStringNotNowprocket' => [
			'query_vars' => [
				'key' => 'value',
			],
			'expected' => false,
		],
		'testShouldReturnTrueWhenQueryStringNowprocket' => [
			'query_vars' => [
				'nowprocket' => '1',
			],
			'expected' => true,
		],
		'testShouldReturnTrueWhenQueryStringNowprocketAndAnother' => [
			'query_vars' => [
				'nowprocket' => '1',
				'key'        => 'value',
			],
			'expected' => true,
		],
	],
];
