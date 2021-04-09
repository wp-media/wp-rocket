<?php

return [
	'test_data' => [
		'shouldBailoutWhenTablesDoNotExist' => [
			'input' => [
				'resources' => [
					'exists' => false,
				],
				'usedCSS' => [
					'exists' => false,
				],
			],
		],
		'shouldBailoutOnlyResources' => [
			'input' => [
				'resources' => [
					'exists' => false,
				],
				'usedCSS' => [
					'exists' => true,
				],
			],
		],
		'shouldBailoutOnlyUsedCSS' => [
			'input' => [
				'resources' => [
					'exists' => true,
				],
				'usedCSS' => [
					'exists' => false,
				],
			],
		],
		'shouldUninstallBoth' => [
			'input' => [
				'resources' => [
					'exists' => true,
				],
				'usedCSS' => [
					'exists' => true,
				],
			],
		],
	],
];
