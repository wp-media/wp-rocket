<?php
return [
	'pluginDisabledShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
			],
			'automatic_platform_optimization' => [
				'id' => 'automatic_platform_optimization',
				'value' => true
			],
			'user_id' => 10,
			'boxes' => [],
			'plugin_enabled' => false,
			'is_plugin_activated' => false,
			'has_apo' => true,
			'cloudflare_api_email' => 'example@email.mail',
			'key' => 'azz12feee',
			'domain' => 'example.org',
			'can' => true,
			'home_url' => 'http://example.org',
			'headers' => [
				'CF-Cache-Status' => 'HIT',
				'cf-apo-via' => 'tcache',
				'cf-edge-cache' => 'cache, platform=wordpress',
			],
			'right_screen' => true,
			'screen' => (object) [
				'id' => 'settings_page_wprocket'
			],
			'cloudflare_mobile_cache' => [
				'id' => 'automatic_platform_optimization_cache_by_device_type',
				'value' => false
			],
			'mobile_cache' => false,
			'should_display' => false,
			'beacon_response' => [
				'id'  => '602593e90a2dae5b58faee1e',
				'url' => 'https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket',
			],
			'response_fixture' => [
				'headers' => [
					'CF-Cache-Status' => 'HIT',
					'cf-apo-via' => 'tcache',
					'cf-edge-cache' => 'cache, platform=wordpress',
				]
			]
		],
		'expected' => [
			'notice' => [
				'status' => 'warning',
				'dismiss_button' => true,
				'action' => 'enable_separate_mobile_cache',
				'message' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.'
			],
			'notice_content' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.',
		]
	],
	'noEmailShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
			'automatic_platform_optimization' => [
				'id' => 'automatic_platform_optimization',
				'value' => true
			],
			'user_id' => 10,
			'boxes' => [],
			'plugin_enabled' => true,
			'can' => true,
			'has_apo' => true,
			'right_screen' => true,
			'is_plugin_activated' => false,
			'cloudflare_api_email' => '',
			'key' => 'azz12feee',
			'cloudflare_cached_domain_name' => 'example.org',
			'home_url' => 'http://example.org',
			'headers' => [
				'CF-Cache-Status' => 'HIT',
				'cf-apo-via' => 'tcache',
				'cf-edge-cache' => 'cache, platform=wordpress',
			],
			'screen' => (object) [
				'id' => 'settings_page_wprocket'
			],
			'cloudflare_mobile_cache' => [
				'id' => 'automatic_platform_optimization_cache_by_device_type',
				'value' => false
			],
			'mobile_cache' => false,
			'should_display' => false,
			'beacon_response' => [
				'id'  => '602593e90a2dae5b58faee1e',
				'url' => 'https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket',
			],
			'response_fixture' => [
				'headers' => [
					'CF-Cache-Status' => 'HIT',
					'cf-apo-via' => 'tcache',
					'cf-edge-cache' => 'cache, platform=wordpress',
				]
			]
		],
		'expected' => [
			'notice' => [
				'status' => 'warning',
				'dismiss_button' => true,
				'action' => 'enable_separate_mobile_cache',
				'message' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.'
			],
			'notice_content' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.'
		]
	],
	'noKeyShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
			'automatic_platform_optimization' => [
				'id' => 'automatic_platform_optimization',
				'value' => true
			],
			'user_id' => 10,
			'boxes' => [],
			'plugin_enabled' => true,
			'right_screen' => true,
			'can' => true,
			'has_apo' => true,
			'is_plugin_activated' => false,
			'cloudflare_api_email' => 'example@email.mail',
			'cloudflare_api_key' => '',
			'cloudflare_cached_domain_name' => 'example.org',
			'home_url' => 'http://example.org',
			'headers' => [
				'CF-Cache-Status' => 'HIT',
				'cf-apo-via' => 'tcache',
				'cf-edge-cache' => 'cache, platform=wordpress',
			],
			'screen' => (object) [
				'id' => 'settings_page_wprocket'
			],
			'cloudflare_mobile_cache' => [
				'id' => 'automatic_platform_optimization_cache_by_device_type',
				'value' => false
			],
			'mobile_cache' => false,
			'should_display' => false,
			'beacon_response' => [
				'id'  => '602593e90a2dae5b58faee1e',
				'url' => 'https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket',
			],
			'response_fixture' => [
				'headers' => [
					'CF-Cache-Status' => 'HIT',
					'cf-apo-via' => 'tcache',
					'cf-edge-cache' => 'cache, platform=wordpress',
				]
			]
		],
		'expected' => [
			'notice' => [
				'status' => 'warning',
				'dismiss_button' => true,
				'action' => 'enable_separate_mobile_cache',
				'message' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.'
			],
			'notice_content' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.'
		]
	],
	'noDomainShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
			'automatic_platform_optimization' => [
				'id' => 'automatic_platform_optimization',
				'value' => true
			],
			'user_id' => 10,
			'boxes' => [],
			'plugin_enabled' => true,
			'can' => true,
			'has_apo' => true,
			'right_screen' => true,
			'is_plugin_activated' => false,
			'cloudflare_api_email' => 'example@email.mail',
			'cloudflare_api_key' => 'azz12feee',
			'cloudflare_cached_domain_name' => '',
			'home_url' => 'http://example.org',
			'headers' => [
				'CF-Cache-Status' => 'HIT',
				'cf-apo-via' => 'tcache',
				'cf-edge-cache' => 'cache, platform=wordpress',
			],
			'screen' => (object) [
				'id' => 'settings_page_wprocket'
			],
			'cloudflare_mobile_cache' => [
				'id' => 'automatic_platform_optimization_cache_by_device_type',
				'value' => false
			],
			'mobile_cache' => false,
			'should_display' => false,
			'beacon_response' => [
				'id'  => '602593e90a2dae5b58faee1e',
				'url' => 'https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket',
			],
			'response_fixture' => [
				'headers' => [
					'CF-Cache-Status' => 'HIT',
					'cf-apo-via' => 'tcache',
					'cf-edge-cache' => 'cache, platform=wordpress',
				]
			]
		],
		'expected' => [
			'notice' => [
				'status' => 'warning',
				'dismiss_button' => true,
				'action' => 'enable_separate_mobile_cache',
				'message' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.'
			],
			'notice_content' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.'
		]
	],
	'noRightShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
			'automatic_platform_optimization' => [
				'id' => 'automatic_platform_optimization',
				'value' => true
			],
			'user_id' => 10,
			'boxes' => [],
			'can' => false,
			'plugin_enabled' => true,
			'right_screen' => true,
			'has_apo' => true,
			'is_plugin_activated' => true,
			'cloudflare_api_email' => 'example@email.mail',
			'cloudflare_api_key' => 'azz12feee',
			'cloudflare_cached_domain_name' => 'example.org',
			'home_url' => 'http://example.org',
			'headers' => [
				'CF-Cache-Status' => 'HIT',
				'cf-apo-via' => 'tcache',
				'cf-edge-cache' => 'cache, platform=wordpress',
			],
			'screen' => (object) [
				'id' => 'settings_page_wprocket'
			],
			'cloudflare_mobile_cache' => [
				'id' => 'automatic_platform_optimization_cache_by_device_type',
				'value' => false
			],
			'mobile_cache' => false,
			'should_display' => false,
			'beacon_response' => [
				'id'  => '602593e90a2dae5b58faee1e',
				'url' => 'https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket',
			],
			'response_fixture' => [
				'headers' => [
					'CF-Cache-Status' => 'HIT',
					'cf-apo-via' => 'tcache',
					'cf-edge-cache' => 'cache, platform=wordpress',
				]
			]
		],
		'expected' => [
			'notice' => [
				'status' => 'warning',
				'dismiss_button' => true,
				'action' => 'enable_separate_mobile_cache',
				'message' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.'
			],
			'notice_content' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.'
		]
	],
	'noAPOShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
			'user_id' => 10,
			'boxes' => [],
			'automatic_platform_optimization' => [
				'id' => 'automatic_platform_optimization',
				'value' => false
			],
			'can' => true,
			'is_plugin_activated' => true,
			'plugin_enabled' => true,
			'has_apo' => false,
			'right_screen' => true,
			'cloudflare_api_email' => 'example@email.mail',
			'cloudflare_api_key' => 'azz12feee',
			'cloudflare_cached_domain_name' => 'example.org',
			'home_url' => 'http://example.org',
			'headers' => [
			],
			'screen' => (object) [
				'id' => 'settings_page_wprocket'
			],
			'cloudflare_mobile_cache' => [
				'id' => 'automatic_platform_optimization_cache_by_device_type',
				'value' => false
			],
			'mobile_cache' => false,
			'should_display' => false,
			'beacon_response' => [
				'id'  => '602593e90a2dae5b58faee1e',
				'url' => 'https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket',
			],
			'response_fixture' => [
				'headers' => [
				]
			]
		],
		'expected' => [
			'notice' => [
				'status' => 'warning',
				'dismiss_button' => true,
				'action' => 'enable_separate_mobile_cache',
				'message' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.'
			],
			'notice_content' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.'
		]
	],
	'noScreenShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
			'automatic_platform_optimization' => [
				'id' => 'automatic_platform_optimization',
				'value' => true
			],
			'user_id' => 10,
			'boxes' => [],
			'can' => true,
			'right_screen' => false,
			'has_apo' => true,
			'is_plugin_activated' => true,
			'plugin_enabled' => true,
			'cloudflare_api_email' => 'example@email.mail',
			'cloudflare_api_key' => 'azz12feee',
			'cloudflare_cached_domain_name' => 'example.org',
			'home_url' => 'http://example.org',
			'headers' => [
				'CF-Cache-Status' => 'HIT',
				'cf-apo-via' => 'tcache',
				'cf-edge-cache' => 'cache, platform=wordpress',
			],
			'screen' => (object) [
				'id' => 'random'
			],
			'cloudflare_mobile_cache' => [
				'id' => 'automatic_platform_optimization_cache_by_device_type',
				'value' => false,
			],
			'mobile_cache' => false,
			'should_display' => false,
			'beacon_response' => [
				'id'  => '602593e90a2dae5b58faee1e',
				'url' => 'https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket',
			],
			'response_fixture' => [
				'headers' => [
					'CF-Cache-Status' => 'HIT',
					'cf-apo-via' => 'tcache',
					'cf-edge-cache' => 'cache, platform=wordpress',
				]
			]
		],
		'expected' => [
			'notice' => [
				'status' => 'warning',
				'dismiss_button' => true,
				'action' => 'enable_separate_mobile_cache',
				'message' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.'
			],
			'notice_content' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.'
		]
	],
	'mobileCacheMatchShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
			'user_id' => 10,
			'boxes' => [],
			'automatic_platform_optimization' => [
				'id' => 'automatic_platform_optimization',
				'value' => true
			],
			'can' => true,
			'right_screen' => true,
			'is_plugin_activated' => true,
			'has_apo' => true,
			'plugin_enabled' => true,
			'cloudflare_api_email' => 'example@email.mail',
			'cloudflare_api_key' => 'azz12feee',
			'cloudflare_cached_domain_name' => 'example.org',
			'home_url' => 'http://example.org',
			'headers' => [
				'CF-Cache-Status' => 'HIT',
				'cf-apo-via' => 'tcache',
				'cf-edge-cache' => 'cache, platform=wordpress',
			],
			'screen' => (object) [
				'id' => 'settings_page_wprocket'
			],
			'cloudflare_mobile_cache' => [
				'id' => 'automatic_platform_optimization_cache_by_device_type',
				'value' => false
			],
			'mobile_cache' => false,
			'should_display' => false,
			'beacon_response' => [
				'id'  => '602593e90a2dae5b58faee1e',
				'url' => 'https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket',
			],
			'response_fixture' => [
				'headers' => [
					'CF-Cache-Status' => 'HIT',
					'cf-apo-via' => 'tcache',
					'cf-edge-cache' => 'cache, platform=wordpress',
				]
			]
		],
		'expected' => [
			'notice' => [
				'status' => 'warning',
				'dismiss_button' => true,
				'action' => 'enable_separate_mobile_cache',
				'message' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.'
			],
			'notice_content' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.'
		]
	],
	'mobileCacheMismatchMobileEnabledShouldDisplayNotice' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
			'user_id' => 10,
			'boxes' => [],
			'automatic_platform_optimization' => [
				'id' => 'automatic_platform_optimization',
				'value' => true
			],
			'can' => true,
			'right_screen' => true,
			'is_plugin_activated' => true,
			'has_apo' => true,
			'plugin_enabled' => true,
			'cloudflare_api_email' => 'example@email.mail',
			'cloudflare_api_key' => 'azz12feee',
			'cloudflare_cached_domain_name' => 'example.org',
			'home_url' => 'http://example.org',
			'headers' => [
				'CF-Cache-Status' => 'HIT',
				'cf-apo-via' => 'tcache',
				'cf-edge-cache' => 'cache, platform=wordpress',
			],
			'screen' => (object) [
				'id' => 'settings_page_wprocket'
			],
			'cloudflare_mobile_cache' => [
				'id' => 'automatic_platform_optimization_cache_by_device_type',
				'value' => false,
			],
			'mobile_cache' => true,
			'should_display' => true,
			'beacon_response' => [
				'id'  => '602593e90a2dae5b58faee1e',
				'url' => 'https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket',
			],
			'response_fixture' => [
				'headers' => [
					'CF-Cache-Status' => 'HIT',
					'cf-apo-via' => 'tcache',
					'cf-edge-cache' => 'cache, platform=wordpress',
				]
			]
		],
		'expected' => [
			'notice' => [
				'status' => 'warning',
				'dismissible' => '',
				'message' => '<strong>WP Rocket:</strong> You are using "Separate cache files for mobile devices". You need to activate "Cache by Device Type" <a href="http://example.org/wp-admin/options-general.php?page=cloudflare">setting</a> on Cloudflare APO to serve the right version of the cache. <a href="https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket" data-beacon-article="602593e90a2dae5b58faee1e" target="_blank" rel="noopener noreferrer">More info</a>'
			],
			'notice_content' => '<strong>WP Rocket:</strong>You are using "Separate cache files for mobile devices". You need to activate "Cache by Device Type" <a href="http://example.org/wp-admin/options-general.php?page=cloudflare">setting</a> on Cloudflare APO to serve the right version of the cache. <a href="https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="602593e90a2dae5b58faee1e" target="_blank" rel="noopener noreferrer">More info</a>'
		],
	],
	'mobileCacheMismatchMobileDisabledShouldDisplayNotice' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
			'home_url' => 'http://example.org',
			'headers' => [
				'CF-Cache-Status' => 'HIT',
				'cf-apo-via' => 'tcache',
				'cf-edge-cache' => 'cache, platform=wordpress',
			],
			'user_id' => 10,
			'boxes' => [],
			'automatic_platform_optimization' => [
				'id' => 'automatic_platform_optimization',
				'value' => true
			],
			'can' => true,
			'right_screen' => true,
			'has_apo' => true,
			'is_plugin_activated' => true,
			'plugin_enabled' => true,
			'cloudflare_api_email' => 'example@email.mail',
			'cloudflare_api_key' => 'azz12feee',
			'cloudflare_cached_domain_name' => 'example.org',
			'screen' => (object) [
				'id' => 'settings_page_wprocket'
			],
			'cloudflare_mobile_cache' => [
				'id' => 'automatic_platform_optimization_cache_by_device_type',
				'value' => true
			],
			'mobile_cache' => false,
			'should_display' => true,
			'beacon_response' => [
				'id'  => '602593e90a2dae5b58faee1e',
				'url' => 'https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket',
			],
			'response_fixture' => [
				'headers' => [
					'CF-Cache-Status' => 'HIT',
					'cf-apo-via' => 'tcache',
					'cf-edge-cache' => 'cache, platform=wordpress',
				]
			]
		],
		'expected' => [
			'notice' => [
				'status' => 'warning',
				'dismiss_button' => 'display_apo_cache_notice',
				'dismissible' => '',
				'action' => 'enable_separate_mobile_cache',
				'message' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.'
			],
			'notice_content' => '<strong>WP Rocket:</strong>You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.'
		]
	]
];
