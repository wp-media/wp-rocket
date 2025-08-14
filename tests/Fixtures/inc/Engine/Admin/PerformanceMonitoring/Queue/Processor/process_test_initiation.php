<?php

return [
	'testShouldCreateDatabaseRecord' => [
		'config' => [
			'url'       => 'http://example.org',
			'options' => [],
		],
		'expected' => [
			'should_create_record' => true,
		],
	],

	'testShouldCreateMobileRecord' => [
		'config' => [
			'url'       => 'http://example.org',
			'options' => [
				'device' => 'mobile',
			],
		],
		'expected' => [
			'should_create_record' => true,
		],
	],

	'testShouldCreateRecordWithDifferentUrl' => [
		'config' => [
			'url'       => 'http://example.org/page',
			'options' => [],
			
		],
		'expected' => [
			'should_create_record' => true,
		],
	],
];
