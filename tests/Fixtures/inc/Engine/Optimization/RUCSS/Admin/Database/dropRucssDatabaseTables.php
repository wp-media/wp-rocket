<?php

return [
	'test_data' => [
		'shouldBailoutWhenTableDoesNotExist' => [
			'input' => [
				'usedCSS' => [
					'exists' => false,
				],
			],
		],
		'shouldUninstallTable' => [
			'input' => [
				'usedCSS' => [
					'exists' => true,
				],
			],
		],
	],
];
