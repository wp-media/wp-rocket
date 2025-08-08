<?php

return [
	'test_data' => [
		'nonExistentTable' => [
			'config' => [
				'items' => [],
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
				'table_name_contains' => 'wpr_performance_monitoring',
			],
		],
	],
];
