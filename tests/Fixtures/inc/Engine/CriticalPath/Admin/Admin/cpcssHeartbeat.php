<?php

return [
	'testShouldBailOutAtDisabledAsyncCSS' => [
		'config'   => [
			'check_ajax_referer' => true,
			'options'            => [ 'async_css' => 0 ],
		],
		'expected' => [
			'bailout' => true,
			'json'    => 'wp_send_json_error',
		],
	],

	'testShouldBailOutWithoutRocketPermissions' => [
		'config'   => [
			'check_ajax_referer'    => true,
			'options'               => [ 'async_css' => 1 ],
			'rocket_manage_options' => false,
		],
		'expected' => [
			'bailout' => true,
			'json'    => 'wp_send_json_error',
		],
	],

	'testShouldBailOutWithoutCriticalCSSPermissions' => [
		'config'   => [
			'check_ajax_referer'             => true,
			'options'                        => [ 'async_css' => 1 ],
			'rocket_manage_options'          => true,
			'rocket_regenerate_critical_css' => false,
		],
		'expected' => [
			'bailout' => true,
			'json'    => 'wp_send_json_error',
		],
	],

	'testShouldStopNoDataInTransient' => [
		'config'   => [
			'check_ajax_referer'                             => true,
			'options'                                        => [ 'async_css' => 1 ],
			'rocket_manage_options'                          => true,
			'rocket_regenerate_critical_css'                 => true,
			'rocket_cpcss_generation_pending'                => [],
			'rocket_critical_css_generation_process_running' => false,
		],
		'expected' => [
			'bailout'             => false,
			'generation_complete' => true,
			'json'                => 'wp_send_json_success',
			'data'                => [ 'status' => 'cpcss_complete' ],
		],
	],

	'testShouldRunSingleDataInTransientWithProcessGenerateReturnWPError' => [
		'config'   => [
			'check_ajax_referer'                             => true,
			'options'                                        => [ 'async_css' => 1 ],
			'rocket_manage_options'                          => true,
			'rocket_regenerate_critical_css'                 => true,
			'rocket_cpcss_generation_pending'                => [
				'front_page.css' => [
					'url'     => 'https://example.org/',
					'path'    => 'front_page.css',
					'timeout' => false,
					'mobile'  => false,
					'check'   => 0,
				],
			],
			'process_generate'                               => [
				'is_wp_error' => true,
				'status'      => 404,
				'success'     => false,
				'code'        => 'cpcss_generation_failed',
				'message'     => 'Critical CSS for https://example.org/ not generated.',
				'data'        => [
					'state' => 'failed',
				],
			],
			'notice'                                         => [
				'get_error_message' => 'error message',
				'transient' => [
					'items'     => [ 'front_page.css' => [
						'message' => 'error message',
						'success' => false,
						],
				    ],
					'total'     => 1,
				],
			],
			'rocket_critical_css_generation_process_running' => true,
		],
		'expected' => [
			'bailout'             => false,
			'generation_complete' => true,
			'json'                => 'wp_send_json_success',
			'data'                => [ 'status' => 'cpcss_complete' ],
		],
	],

	'testShouldRunSingleDataInTransientWithProcessGenerateReturnSuccess' => [
		'config'   => [
			'check_ajax_referer'                             => true,
			'options'                                        => [
				'async_css' => 1,
			],
			'rocket_manage_options'                          => true,
			'rocket_regenerate_critical_css'                 => true,
			'rocket_cpcss_generation_pending'                => [
				'front_page.css' => [
					'url'     => 'https://example.org/',
					'path'    => 'front_page.css',
					'timeout' => false,
					'mobile'  => false,
					'check'   => 0,
				],
			],
			'process_generate'                               => [
				'status'  => 200,
				'success' => true,
				'code'    => 'cpcss_generation_successful',
				'message' => 'Critical CSS for https://example.org/ generated.',
				'data'    => [
					'state'         => 'complete',
					'critical_path' => '.critical_path { color: red; }',
				],
			],
			'notice'                                         => [
				'transient' => [
					'items'     => [ 'front_page.css' => [
						'message' => 'Critical CSS for https://example.org/ generated.',
						'success' => true,
						],
				    ],
					'total'     => 1,
				],
			],
			'rocket_critical_css_generation_process_running' => true,
		],
		'expected' => [
			'bailout'             => false,
			'generation_complete' => true,
			'json'                => 'wp_send_json_success',
			'data'                => [ 'status' => 'cpcss_complete' ],
		],
	],

	'testShouldRunSingleDataInTransientWithProcessGenerateReturnFailed' => [
		'config'   => [
			'check_ajax_referer'                             => true,
			'options'                                        => [ 'async_css' => 1 ],
			'rocket_manage_options'                          => true,
			'rocket_regenerate_critical_css'                 => true,
			'rocket_cpcss_generation_pending'                => [
				'front_page.css' => [
					'url'     => 'https://example.org/',
					'path'    => 'front_page.css',
					'timeout' => false,
					'mobile'  => false,
					'check'   => 0,
				],
			],
			'process_generate'                               => [
				'status'  => 404,
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for https://example.org/ not generated.',
				'data'    => [
					'state' => 'failed',
				],
			],
			'notice'                                         => [
				'transient' => [
					'items'     => [ 'front_page.css' => [
						'message' => 'Critical CSS for https://example.org/ not generated.',
						'success' => false,
						],
				    ],
					'total'     => 1,
				],
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
			'check_ajax_referer'                             => true,
			'options'                                        => [ 'async_css' => 1 ],
			'rocket_manage_options'                          => true,
			'rocket_regenerate_critical_css'                 => true,
			'rocket_cpcss_generation_pending'                => [
				'front_page.css' => [
					'url'     => 'https://example.org/',
					'path'    => 'front_page.css',
					'timeout' => false,
					'mobile'  => false,
					'check'   => 11,
				],
			],
			'process_generate'                               => [
				'status'  => 200,
				'success' => true,
				'code'    => 'cpcss_generation_pending',
				'message' => 'Critical CSS for https://example.org/ in progress.',
				'data'    => [
					'state' => 'pending',
				],
			],
			'rocket_critical_css_generation_process_running' => true,
		],
		'expected' => [
			'bailout'                             => false,
			'generation_complete'                 => true,
			'bailout_generation_complete'         => true,
			'bailout_timeout'                     => true,
			'json'                                => 'wp_send_json_success',
			'data'                                => [ 'status' => 'cpcss_complete' ],
			'set_rocket_cpcss_generation_pending' => [],
		],
	],

	'testShouldRunSingleDataInTransientWithProcessGeneratePending' => [
		'config'   => [
			'check_ajax_referer'              => true,
			'options'                         => [ 'async_css' => 1 ],
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
			'process_generate'                => [
				'status'  => 200,
				'success' => true,
				'code'    => 'cpcss_generation_pending',
				'message' => 'Critical CSS for https://example.org/ in progress.',
				'data'    => [
					'state' => 'pending',
				],
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
			'check_ajax_referer'                             => true,
			'options'                                        => [ 'async_css' => 1 ],
			'rocket_manage_options'                          => true,
			'rocket_regenerate_critical_css'                 => true,
			'rocket_cpcss_generation_pending'                => [
				'front_page.css' => [
					'url'     => 'https://example.org/',
					'path'    => 'front_page.css',
					'timeout' => false,
					'mobile'  => false,
					'check'   => 0,
				],
				'category.css'   => [
					'url'     => 'https://example.org/category/',
					'path'    => 'category.css',
					'timeout' => false,
					'mobile'  => false,
					'check'   => 0,
				],
			],
			'process_generate'                               => [
				'status'  => 404,
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for https://example.org/ not generated.',
				'data'    => [
					'state' => 'failed',
				],
			],
			'notice'                                         => [
				'transient' => [
					'items'     => [ 'front_page.css' => [
						'message' => 'Critical CSS for https://example.org/ not generated.',
						'success' => false,
						],
				    ],
					'total'     => 2,
				],
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
