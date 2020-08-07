<?php

return [
	'test_data' => [

		'testWithoutRegex' => [
			'input' => [
				'full_purge_url' => 'http://www.example.org/',
				'main_purge_url' => 'http://www.example.org/'
			],
			'expected' => 'http://www.example.org/',
		],

		'testWithRegex' => [
			'input' => [
				'full_purge_url' => 'http://www.example.org/.*',
				'main_purge_url' => 'http://www.example.org/',
				'regex' => '.*'
			],
			'expected' => 'http://www.example.org/',
		],

	]
];
