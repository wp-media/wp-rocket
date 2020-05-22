<?php

return [
	'test_data' => [
		'testShouldBailOutWhenAsyncCssMobileDisabled' => [
			'config' => [
				'old_value' => [
					'async_css'               => 0,
				],
				'value' => [
					'async_css_mobile' => 0,
					'do_caching_mobile_files' => 0,
					'async_css' => 0,
				],
			],
			'expected' => [
				'process_handler_called' => false,
			]
		],
		'testShouldBailOutWhenAsyncCssMobileNotFound' => [
			'config' => [
				'old_value' => [
					'async_css'               => 0,
				],
				'value' => [
					'async_css' => 0,
					'do_caching_mobile_files' => 0,
				],
			],
			'expected' => [
				'process_handler_called' => false,
			]
		],
		'testShouldBailOutWhenDoCachingMobileFilesDisabledANDAsyncCssMobileEnabled' => [
			'config' => [
				'old_value' => [
					'async_css'               => 0,
				],
				'value' => [
					'async_css_mobile'        => 1,
					'do_caching_mobile_files' => 0,
					'async_css'               => 0,
				],
			],
			'expected' => [
				'process_handler_called' => false,
			]
		],
		'testShouldBailOutWhenDoCachingMobileFilesNotFoundANDAsyncCssMobileEnabled' => [
			'config' => [
				'old_value' => [
					'async_css'               => 0,
				],
				'value' => [
					'async_css_mobile' => 1,
					'async_css'        => 0,
				],
			],
			'expected' => [
				'process_handler_called' => false,
			]
		],
		'testShouldBailOutWhenOldANDNewValueOfAsyncCssNotFound' => [
			'config' => [
				'old_value' => [],
				'value' => [
					'async_css_mobile' => 1,
					'do_caching_mobile_files' => 1,
				],
			],
			'expected' => [
				'process_handler_called' => false,
			]
		],
		'testShouldBailOutWhenAsyncCssNewlyActivated' => [
			'config' => [
				'old_value' => [
					'async_css' => 0,
				],
				'value' => [
					'async_css_mobile' => 1,
					'do_caching_mobile_files' => 1,
					'async_css' => 1,
				],
			],
			'expected' => [
				'process_handler_called' => false,
			]
		],
		'testShouldSucceed' => [
			'config' => [
				'old_value' => [
					'async_css' => 1,
				],
				'value' => [
					'async_css_mobile' => 1,
					'do_caching_mobile_files' => 1,
					'async_css' => 1,
				],
			],
			'expected' => [
				'process_handler_called' => true,
			]
		],
	],
];
