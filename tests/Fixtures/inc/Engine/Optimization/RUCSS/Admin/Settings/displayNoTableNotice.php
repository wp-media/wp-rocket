<?php
return [
	'vfs_dir' => 'wp-content/',

	'test_data' => [
		'testRucssDisabledShouldDoNoting' => [
			'config' => [
				'enabled' => false,
				'screen' => (object) [
					'id' => 'settings_page_wprocket',
				],
				'has_right' => false,
				'exists' => true,
			],
			'expected' => false
		],
		'testTableShouldDoNothing' =>
			[
				'config' => [
					'enabled' => true,
					'screen' => (object) [
						'id' => 'settings_page_wprocket',
					],
					'has_right' => false,
					'exists' => false,
				],
				'expected' => false
			],
		'testWrongScreenShouldDoNothing' =>
			[
				'config' => [
					'enabled' => true,
					'screen' => (object) [
						'id' => 'wrong',
					],
					'has_right' => false,
					'exists' => true,
				],
				'expected' => false
			],
		'testNoTableShouldDisplay' =>
			[
				'config' => [
					'enabled' => true,
					'screen' => (object) [
						'id' => 'settings_page_wprocket',
					],
					'has_right' => true,
					'exists' => false,
				],
				'expected' => true
			],
		]
];
