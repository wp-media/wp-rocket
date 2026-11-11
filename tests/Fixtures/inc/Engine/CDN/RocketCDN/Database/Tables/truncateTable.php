<?php

return [
	'test_data' => [
		'truncateExistingTable' => [
			'config' => [
				'items' => [
					[
						'title' => 'Page one',
						'url'   => 'https://example.org/page-one',
					],
					[
						'title' => 'Page two',
						'url'   => 'https://example.org/page-two',
					],
				],
			],
			'expected' => [
				'result'          => true,
				'remaining_count' => 0,
			],
		],
		'nonExistentTable' => [
			'config' => [
				'items'           => [],
				'uninstall_table' => true,
			],
			'expected' => [
				'result' => 'boolean',
			],
		],
		'getTableName' => [
			'config' => [
				'items' => [],
			],
			'expected' => [
				'table_name_contains' => 'wpr_rocket_cdn',
			],
		],
	],
];
