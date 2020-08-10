<?php

return [
	'test_data' => [

		'testWithPurgeMethod' => [
			'input' => [
				'constants' => [
					'O2SWITCH_VARNISH_PURGE_KEY' => 'purge_key',
				],
				'headers' => [
					'host' => 'http://www.example.org',
					'X-Purge-Method' => 'regex'
				],
			],
			'expected' => [
				'host' => 'http://www.example.org',
				'X-VC-Purge-Key' => 'purge_key',
				'X-Purge-Regex' => '.*'
			],
		],

		'testWithoutPurgeMethod' => [
			'input' => [
				'constants' => [
					'O2SWITCH_VARNISH_PURGE_KEY' => 'purge_key',
				],
				'headers' => [
					'host' => 'http://www.example.org'
				],
			],
			'expected' => [
				'host' => 'http://www.example.org',
				'X-VC-Purge-Key' => 'purge_key',
			],
		],

	]
];
