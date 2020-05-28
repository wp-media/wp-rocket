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
			'rocket_critical_css_generation_process_running' => false,
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
				'front_page.css' => [
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
					'items'     => [ 'front_page.css' => 'error message' ],
					'generated' => 0,
					'total'     => 1,
				]
			],
			'rocket_critical_css_generation_process_running' => true,
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
				'front_page.css' => [
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
					'items'     => [ 'front_page.css' => 'Critical CSS for https://example.org/ generated.' ],
					'generated' => 1,
					'total'     => 1,
				]
			],
			'rocket_critical_css_generation_process_running' => true,
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
				'front_page.css' => [
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
					'items'     => [ 'front_page.css' => 'Critical CSS for https://example.org/ not generated.' ],
					'generated' => 1,
					'total'     => 1,
				]
			],
			'rocket_critical_css_generation_process_running' => true,
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
				'front_page.css' => [
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
			'rocket_critical_css_generation_process_running' => true,
		],
		'expected' => [
			'bailout'                             => false,
			'generation_complete'                 => true,
			'bailout_generation_complete'         => true,
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
				'front_page.css' => [
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
				'front_page.css' => [
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
				'front_page.css' => [
					'url'     => 'https://example.org/',
					'path'    => 'front_page.css',
					'timeout' => false,
					'mobile'  => false,
					'check'   => 0,
				],
				'category.css' => [
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
					'items'     => [ 'front_page.css' => 'Critical CSS for https://example.org/ not generated.' ],
					'generated' => 1,
					'total'     => 2,
				]
			],
			'rocket_critical_css_generation_process_running' => true,
		],
		'expected' => [
			'bailout'                             => false,
			'generation_complete'                 => false,
			'bailout_generation_complete'         => true,
			'json'                                => 'wp_send_json_success',
			'data'                                => [ 'status' => 'cpcss_running' ],
			'set_rocket_cpcss_generation_pending' => [
				'category.css' => [
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
