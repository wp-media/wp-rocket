<?php
return [
	'phpShouldReturnFalse' => [
		'config' => [
			'filter_query' => false,
			'rejected' => true,
			'resource' => [
				'url' => 'http://example.com/test.php',
				'status' => 'pending',
			],
			'query' => [
				'url' => 'http://example.com/test.php',
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
		'expected' => false
	],
	'notExistingShouldCreate' => [
		'config' => [
			'filter_query' => false,
			'formatted_url' => 'http://example.com',
			'rejected' => false,
			'resource' => [
				'url' => 'http://example.com',
				'status' => 'pending',
			],
			'query' => [
				'url' => 'http://example.com',
				'status' => 'pending',
			],
			'save' => [
				'url' => 'http://example.com',
				'status' => 'pending',
				'last_accessed' => '838:59:59.000000',
				'is_locked' => false,
			],
			'id' => 10,
			'time' => '838:59:59.000000',
			'rows' => [],
		],
		'expected' => 10
	],
	'notExistingAndErrorShouldCreateAndReturnFalse' => [
		'config' => [
			'filter_query' => false,
			'formatted_url' => 'http://example.com',
			'rejected' => false,
			'resource' => [
				'url' => 'http://example.com',
				'status' => 'pending',
			],
			'query' => [
				'url' => 'http://example.com',
				'status' => 'pending',
			],
			'save' => [
				'url' => 'http://example.com',
				'status' => 'pending',
				'last_accessed' => '838:59:59.000000',
				'is_locked' => false,
			],
			'id' => false,
			'time' => '838:59:59.000000',
			'rows' => [],
		],
		'expected' => false
	],
	'existingShouldDoNothing' => [
		'config' => [
			'filter_query' => false,
			'formatted_url' => 'http://example.com',
			'rejected' => false,
			'resource' => [
				'url' => 'http://example.com',
				'status' => 'pending',
			],
			'query' => [
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
			'rows' => [
				(object) [
					'url' => 'http://example.com',
					'status' => 'completed',
					'id' => 10,
					'last_accessed' => '838:59:59.000000'
				]
			],
		],
		'expected' => false
	],
	'paramsAndFilterDisabledShouldAddWithout' => [
		'config' => [
			'filter_query' => false,
			'rejected' => false,
			'formatted_url' => 'http://example.com?tes=tes',
			'query' => [
				'url' => 'http://example.com',
				'status' => 'pending',
			],
			'resource' => [
				'url' => 'http://example.com?tes=tes',
				'status' => 'pending',
			],
			'save' => [
				'url' => 'http://example.com',
				'status' => 'pending',
				'modified' => '838:59:59.000001'
			],
			'id' => 10,
			'time' => '838:59:59.000001',
			'rows' => [
				(object) [
					'url' => 'http://example.com',
					'status' => 'completed',
					'id' => 10,
					'last_accessed' => '838:59:59.000000'
				]
			],
		],
		'expected' => false
	],
	'paramsAndFilterEnabledShouldAddWithParams' => [
		'config' => [
			'filter_query' => true,
			'rejected' => false,
			'formatted_url' => 'http://example.com?tes=tes',
			'resource' => [
				'url' => 'http://example.com?tes=tes',
				'status' => 'pending',
			],
			'query' => [
				'url' => 'http://example.com?tes=tes',
				'status' => 'pending',
			],
			'save' => [
				'url' => 'http://example.com?tes=tes',
				'status' => 'pending',
				'modified' => '838:59:59.000001'
			],
			'id' => 10,
			'time' => '838:59:59.000001',
			'rows' => [
				(object) [
					'url' => 'http://example.com?tes=tes',
					'status' => 'completed',
					'id' => 10,
					'last_accessed' => '838:59:59.000000'
				]
			],
		],
		'expected' => false
	]
];
