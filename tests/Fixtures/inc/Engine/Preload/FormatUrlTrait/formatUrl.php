<?php
return [
	'withParamsShouldReturnRightOrder' => [
		'config' => [
			'url' => 'http://example.org/?&b=aaa&a=sdsd',
			'queries' => '&b=aaa&a=sdsd',
			'queries_array' => [
				'b'=> 'aaa',
				'a' => 'sdsd',
			],
			'simple_url' => 'http://example.org',
			'return_url' => 'http://example.org?a=sdsd&b=aaa'
		],
		'expected' => 'http://example.org?a=sdsd&b=aaa',
	],
	'withoutParamsShouldReturnSame' => [
		'config' => [
			'url' => 'http://example.org/',
			'queries' => null,
			'queries_array' => [
			],
			'simple_url' => 'http://example.org',
			'return_url' => 'http://example.org'
		],
		'expected' => 'http://example.org',
	]
];
