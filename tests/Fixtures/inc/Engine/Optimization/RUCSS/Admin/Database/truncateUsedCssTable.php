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
		'shouldTruncate' => [
			'input' => [
				'usedCSS' => [
					'exists'   => true,
					'truncate' => true,
				],
			],
		],
	],
];
