<?php
return [
	'pluginDisabledShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
			],
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
		],
		'expected' => [
			'notice' => [
				'message' => '<p>You are using “Separate cache files for mobile devices”. You need to activate “Cache by Device Type” on Cloudflare APO to serve the right version of the cache: (add the path to activate “Cache by Device Type” on Cloudflare plugin)</p><a href="https://docs.wp-rocket.me/article/1313-create-different-cache-files-with-dynamic-and-mandatory-cookies">More info</a>'
			]
		]
	],
	'noEmailShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
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
		],
		'expected' => [
			'notice' => [
				'message' => '<p>You are using “Separate cache files for mobile devices”. You need to activate “Cache by Device Type” on Cloudflare APO to serve the right version of the cache: (add the path to activate “Cache by Device Type” on Cloudflare plugin)</p><a href="https://docs.wp-rocket.me/article/1313-create-different-cache-files-with-dynamic-and-mandatory-cookies">More info</a>'
			]
		]
	],
	'noKeyShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
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
		],
		'expected' => [
			'notice' => [
				'message' => '<p>You are using “Separate cache files for mobile devices”. You need to activate “Cache by Device Type” on Cloudflare APO to serve the right version of the cache: (add the path to activate “Cache by Device Type” on Cloudflare plugin)</p><a href="https://docs.wp-rocket.me/article/1313-create-different-cache-files-with-dynamic-and-mandatory-cookies">More info</a>'
			]
		]
	],
	'noDomainShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
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
		],
		'expected' => [
			'notice' => [
				'message' => '<p>You are using “Separate cache files for mobile devices”. You need to activate “Cache by Device Type” on Cloudflare APO to serve the right version of the cache: (add the path to activate “Cache by Device Type” on Cloudflare plugin)</p><a href="https://docs.wp-rocket.me/article/1313-create-different-cache-files-with-dynamic-and-mandatory-cookies">More info</a>'
			]
		]
	],
	'noRightShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
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
		],
		'expected' => [
			'notice' => [
				'message' => '<p>You are using “Separate cache files for mobile devices”. You need to activate “Cache by Device Type” on Cloudflare APO to serve the right version of the cache: (add the path to activate “Cache by Device Type” on Cloudflare plugin)</p><a href="https://docs.wp-rocket.me/article/1313-create-different-cache-files-with-dynamic-and-mandatory-cookies">More info</a>'
			]
		]
	],
	'noAPOShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
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
		],
		'expected' => [
			'notice' => [
				'message' => '<p>You are using “Separate cache files for mobile devices”. You need to activate “Cache by Device Type” on Cloudflare APO to serve the right version of the cache: (add the path to activate “Cache by Device Type” on Cloudflare plugin)</p><a href="https://docs.wp-rocket.me/article/1313-create-different-cache-files-with-dynamic-and-mandatory-cookies">More info</a>'
			]
		]
	],
	'noScreenShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
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
		],
		'expected' => [
			'notice' => [
				'message' => '<p>You are using “Separate cache files for mobile devices”. You need to activate “Cache by Device Type” on Cloudflare APO to serve the right version of the cache: (add the path to activate “Cache by Device Type” on Cloudflare plugin)</p><a href="https://docs.wp-rocket.me/article/1313-create-different-cache-files-with-dynamic-and-mandatory-cookies">More info</a>'
			]
		]
	],
	'mobileCacheMatchShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
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
		],
		'expected' => [
			'notice' => [
				'message' => '<p>You are using “Separate cache files for mobile devices”. You need to activate “Cache by Device Type” on Cloudflare APO to serve the right version of the cache: (add the path to activate “Cache by Device Type” on Cloudflare plugin)</p><a href="https://docs.wp-rocket.me/article/1313-create-different-cache-files-with-dynamic-and-mandatory-cookies">More info</a>'
			]
		]
	],
	'mobileCacheMismatchMobileEnabledShouldDisplayNotice' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
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
			'mobile_cache' => true,
			'should_display' => true,
		],
		'expected' => [
			'notice' => [
				'message' => '<p>You are using “Separate cache files for mobile devices”. You need to activate “Cache by Device Type” on Cloudflare APO to serve the right version of the cache: (add the path to activate “Cache by Device Type” on Cloudflare plugin)</p><a href="https://docs.wp-rocket.me/article/1313-create-different-cache-files-with-dynamic-and-mandatory-cookies">More info</a>'
			]
		]
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
		],
		'expected' => [
			'notice' => [
				'message' => '<p>You have “Cache by Device Type” enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.</p>'
			]
		]
	]
];
