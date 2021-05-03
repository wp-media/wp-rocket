<?php

return [
	'test_data' => [
		'shouldBailoutWhenTablesDoNotExist' => [
			'input' => [
				'resources' => [
					'exists' => false,
				],
			],
		],
		'shouldDelete' => [
			'input' => [
				'resources' => [
					'exists'   => true,
				],
			],
		],
	],
];
