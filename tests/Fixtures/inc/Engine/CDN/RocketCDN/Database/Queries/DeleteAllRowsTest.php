<?php

return [
	'test_data' => [
		'deleteRowsAndReturnCount' => [
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
				'initial_count'   => 2,
				'remaining_count' => 0,
			],
		],
		'deleteFromEmptyTable' => [
			'config' => [
				'items' => [],
			],
			'expected' => [
				'initial_count'   => 0,
				'remaining_count' => 0,
			],
		],
	],
];
