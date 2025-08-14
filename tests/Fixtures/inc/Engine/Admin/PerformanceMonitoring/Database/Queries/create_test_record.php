<?php

return [
	'test_data' => [
		'createDesktopTest' => [
			'config' => [
				'url' => 'https://example.com/page1',
				'options' => [],
			],
			'expected' => [
				'result_type' => 'int',
			],
		],
		'createMobileTest' => [
			'config' => [
				'url' => 'https://example.com/page2',
				'options' => [
					'device' => 'mobile',
				],
			],
			'expected' => [
				'result_type' => 'int',
			],
		],
		'createDesktopTestExplicit' => [
			'config' => [
				'url' => 'https://example.com/page3',
				'options' => [
					'device' => 'desktop',
				],
			],
			'expected' => [
				'result_type' => 'int',
			],
		],
		'createTestWithOtherOptions' => [
			'config' => [
				'url' => 'https://example.com/page4',
				'options' => [
					'device' => 'tablet',
					'other_param' => 'value',
				],
			],
			'expected' => [
				'result_type' => 'int',
			],
		],
		'createTestLongUrl' => [
			'config' => [
				'url' => 'https://example.com/very/long/path/with/many/segments/and/parameters?param1=value1&param2=value2&param3=value3&param4=value4',
				'options' => [
					'device' => 'mobile',
				],
			],
			'expected' => [
				'result_type' => 'int',
			],
		],
	],
];
