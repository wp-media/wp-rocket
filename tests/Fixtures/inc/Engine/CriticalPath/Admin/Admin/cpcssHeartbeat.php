<?php

return [
	'testShouldBailOutAtDisabledAsyncCSS' => [
		'config'   => [
			'check_ajax_referer'              => true,
			'options'                         => [
				'async_css' => 0,
			],
		],
		'expected' => [
			'bailout' => true,
			'json'    => 'wp_send_json_error',
		],
	],
	'testShouldBailOutWithoutRocketPermissions' => [
		'config'   => [
			'check_ajax_referer'              => true,
			'options'                         => [
				'async_css' => 1,
			],
			'rocket_manage_options'           => false,
		],
		'expected' => [
			'bailout' => true,
			'json'    => 'wp_send_json_error',
		],
	],
	'testShouldBailOutWithoutCriticalCSSPermissions' => [
		'config'   => [
			'check_ajax_referer'              => true,
			'options'                         => [
				'async_css' => 1,
			],
			'rocket_manage_options'           => true,
			'rocket_regenerate_critical_css'  => false,
		],
		'expected' => [
			'bailout' => true,
			'json'    => 'wp_send_json_error',
		],
	],
	'testShouldStopNoDataInTransient' => [
		'config'   => [
			'check_ajax_referer'              => true,
			'options'                         => [
				'async_css' => 1,
			],
			'rocket_manage_options'           => true,
			'rocket_regenerate_critical_css'  => true,
			'rocket_cpcss_generation_pending' => [],
		],
		'expected' => [
			'bailout'                             => false,
			'generation_complete'                 => true,
			'json'                                => 'wp_send_json_success',
			'data'                                => [ 'status' => 'cpcss_complete' ],
			'set_rocket_cpcss_generation_pending' => [],
		],
	],
	'testShouldRunSingleDataInTransientWithProcessGenerateReturnWPError' => [
		'config'   => [
			'check_ajax_referer'              => true,
			'options'                         => [
				'async_css' => 1,
			],
			'rocket_manage_options'           => true,
			'rocket_regenerate_critical_css'  => true,
			'rocket_cpcss_generation_pending' => [
				[
					'url'     => 'https://example.org/',
					'path'    => 'front_page.css',
					'timeout' => false,
					'mobile'  => false,
					'check'   => 0,
				],
			],
			'process_generate' => [
				'is_wp_error' => true,
			],
			'notice' => [
				'get_error_message' => 'error message',
				'transient'         => [
					'items'     => [ 'error message' ],
					'generated' => 0,
				]
			],
		],
		'expected' => [
			'bailout'                             => false,
			'generation_complete'                 => true,
			'json'                                => 'wp_send_json_success',
			'data'                                => [ 'status' => 'cpcss_complete' ],
			'set_rocket_cpcss_generation_pending' => [],
		],
	],
	'testShouldRunSingleDataInTransientWithProcessGenerateReturnSuccess' => [
		'config'   => [
			'check_ajax_referer'              => true,
			'options'                         => [
				'async_css' => 1,
			],
			'rocket_manage_options'           => true,
			'rocket_regenerate_critical_css'  => true,
			'rocket_cpcss_generation_pending' => [
				[
					'url'     => 'https://example.org/',
					'path'    => 'front_page.css',
					'timeout' => false,
					'mobile'  => false,
					'check'   => 0,
				],
			],
			'process_generate' => [
				'code'        => 'cpcss_generation_successful',
				'message'     => 'Critical CSS for https://example.org/ generated.',
			],
			'notice' => [
				'transient'         => [
					'items'     => [ 'Critical CSS for https://example.org/ generated.' ],
					'generated' => 1,
				]
			],
		],
		'expected' => [
			'bailout'                             => false,
			'generation_complete'                 => true,
			'json'                                => 'wp_send_json_success',
			'data'                                => [ 'status' => 'cpcss_complete' ],
			'set_rocket_cpcss_generation_pending' => [],
		],
	],
	'testShouldRunSingleDataInTransientWithProcessGenerateReturnFailed' => [
		'config'   => [
			'check_ajax_referer'              => true,
			'options'                         => [
				'async_css' => 1,
			],
			'rocket_manage_options'           => true,
			'rocket_regenerate_critical_css'  => true,
			'rocket_cpcss_generation_pending' => [
				[
					'url'     => 'https://example.org/',
					'path'    => 'front_page.css',
					'timeout' => false,
					'mobile'  => false,
					'check'   => 0,
				],
			],
			'process_generate' => [
				'code'        => 'cpcss_generation_failed',
				'message'     => 'Critical CSS for https://example.org/ not generated.',
			],
			'notice' => [
				'transient'         => [
					'items'     => [ 'Critical CSS for https://example.org/ not generated.' ],
					'generated' => 1,
				]
			],
		],
		'expected' => [
			'bailout'                             => false,
			'generation_complete'                 => true,
			'json'                                => 'wp_send_json_success',
			'data'                                => [ 'status' => 'cpcss_complete' ],
			'set_rocket_cpcss_generation_pending' => [],
		],
	],
	'testShouldRunSingleDataInTransientWithProcessGenerateReturnTimeout' => [
		'config'   => [
			'check_ajax_referer'              => true,
			'options'                         => [
				'async_css' => 1,
			],
			'rocket_manage_options'           => true,
			'rocket_regenerate_critical_css'  => true,
			'rocket_cpcss_generation_pending' => [
				[
					'url'     => 'https://example.org/',
					'path'    => 'front_page.css',
					'timeout' => false,
					'mobile'  => false,
					'check'   => 11,
				],
			],
			'process_generate' => [
				'code'        => 'cpcss_generation_pending',
				'message'     => 'Critical CSS for https://example.org/ in progress.',
			],
		],
		'expected' => [
			'bailout'                             => false,
			'generation_complete'                 => true,
			'json'                                => 'wp_send_json_success',
			'data'                                => [ 'status' => 'cpcss_complete' ],
			'set_rocket_cpcss_generation_pending' => [],
		],
	],
	'testShouldRunSingleDataInTransientWithProcessGeneratePending' => [
		'config'   => [
			'check_ajax_referer'              => true,
			'options'                         => [
				'async_css' => 1,
			],
			'rocket_manage_options'           => true,
			'rocket_regenerate_critical_css'  => true,
			'rocket_cpcss_generation_pending' => [
				[
					'url'     => 'https://example.org/',
					'path'    => 'front_page.css',
					'timeout' => false,
					'mobile'  => false,
					'check'   => 0,
				],
			],
			'process_generate' => [
				'code'        => 'cpcss_generation_pending',
				'message'     => 'Critical CSS for https://example.org/ in progress.',
			],
		],
		'expected' => [
			'bailout'                             => false,
			'generation_complete'                 => false,
			'json'                                => 'wp_send_json_success',
			'data'                                => [ 'status' => 'cpcss_running' ],
			'set_rocket_cpcss_generation_pending' => [
				[
					'url'     => 'https://example.org/',
					'path'    => 'front_page.css',
					'timeout' => false,
					'mobile'  => false,
					'check'   => 1,
				],
			],
		],
	],
	'testShouldMultipleDataInTransientWithCpcssRunning' => [
		'config'   => [
			'check_ajax_referer'              => true,
			'options'                         => [
				'async_css' => 1,
			],
			'rocket_manage_options'           => true,
			'rocket_regenerate_critical_css'  => true,
			'rocket_cpcss_generation_pending' => [
				[
					'url'     => 'https://example.org/',
					'path'    => 'front_page.css',
					'timeout' => false,
					'mobile'  => false,
					'check'   => 0,
				],
				[
					'url'     => 'https://example.org/category/',
					'path'    => 'category.css',
					'timeout' => false,
					'mobile'  => false,
					'check'   => 0,
				],
			],
			'process_generate' => [
				'code'        => 'cpcss_generation_failed',
				'message'     => 'Critical CSS for https://example.org/ not generated.',
			],
			'notice' => [
				'transient'         => [
					'items'     => [ 'Critical CSS for https://example.org/ not generated.' ],
					'generated' => 1,
				]
			],
		],
		'expected' => [
			'bailout'                             => false,
			'generation_complete'                 => false,
			'json'                                => 'wp_send_json_success',
			'data'                                => [ 'status' => 'cpcss_running' ],
			'set_rocket_cpcss_generation_pending' => [
				1 => [
					'url'     => 'https://example.org/category/',
					'path'    => 'category.css',
					'timeout' => false,
					'mobile'  => false,
					'check'   => 0,
				],
			],
		],
	],
];
