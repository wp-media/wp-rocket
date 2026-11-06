<?php

return [
	'test_data' => [
		'shouldBailoutWhenTablesDoNotExist' => [
			'input' => [
				'usedCSS' => [
					'exists' => false,
				],
			],
		],
		'shouldDelete' => [
			'input' => [
				'usedCSS' => [
					'exists'   => true,
				],
			],
		],
	],
];
