<?php
return [
	'notExistingShouldCreate' => [
		'config' => [
			'resource' => [
				'url' => 'http://example.com',
				'status' => 'pending',
			],
			'save' => [
				'url' => 'http://example.com',
				'status' => 'pending',
				'last_accessed' => '838:59:59.000000'
			],
			'id' => 10,
			'time' => '838:59:59.000000',
			'rows' => [],
		],
		'expected' => 10
	],
	'notExistingAndErrorShouldCreateAndReturnFalse' => [
		'config' => [
			'resource' => [
				'url' => 'http://example.com',
				'status' => 'pending',
			],
			'save' => [
				'url' => 'http://example.com',
				'status' => 'pending',
				'last_accessed' => '838:59:59.000000'
			],
			'id' => false,
			'time' => '838:59:59.000000',
			'rows' => [],
		],
		'expected' => false
	],
	'existingShouldUpdate' => [
		'config' => [
			'resource' => [
				'url' => 'http://example.com',
				'status' => 'pending',
			],
			'save' => [
				'url' => 'http://example.com',
				'status' => 'pending',
				'modified' => '838:59:59.000001'
			],
			'id' => 10,
			'time' => '838:59:59.000001',
			'update_time' => [
				10,
				[
					'last_accessed' => '838:59:59.000001'
				]
			],
			'rows' => [
				(object) [
					'url' => 'http://example.com',
					'status' => 'completed',
					'id' => 10,
					'last_accessed' => '838:59:59.000000'
				]
			],
		],
		'expected' => 10
	],
];
