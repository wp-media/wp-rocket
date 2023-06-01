<?php
return [
	'pluginDisabledShouldDisplayNothing' => [
		'config' => [
			'plugin_enabled' => false,
			'email' => 'example@email.mail',
			'key' => 'azz12feee',
			'domain' => 'example.org',
			'settings' => [
				'id' => 'automatic_platform_optimization',
				'value' => false,
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
	'noEmailShouldDisplayNothing' => [
		'config' => [
			'plugin_enabled' => true,
			'email' => '',
			'key' => 'azz12feee',
			'domain' => 'example.org',
			'settings' => [
				'id' => 'automatic_platform_optimization',
				'value' => false,
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
			'plugin_enabled' => true,
			'email' => 'example@email.mail',
			'key' => '',
			'domain' => 'example.org',
			'settings' => [
				'id' => 'automatic_platform_optimization',
				'value' => false,
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
			'plugin_enabled' => true,
			'email' => 'example@email.mail',
			'key' => 'azz12feee',
			'domain' => '',
			'settings' => [
				'id' => 'automatic_platform_optimization',
				'value' => false,
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
			'plugin_enabled' => true,
			'email' => 'example@email.mail',
			'key' => 'azz12feee',
			'domain' => 'example.org',
			'settings' => [
				'id' => 'automatic_platform_optimization',
				'value' => false,
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
			'plugin_enabled' => true,
			'email' => 'example@email.mail',
			'key' => 'azz12feee',
			'domain' => 'example.org',
			'settings' => [
				'id' => 'automatic_platform_optimization',
				'value' => true,
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
			'plugin_enabled' => true,
			'email' => 'example@email.mail',
			'key' => 'azz12feee',
			'domain' => 'example.org',
			'settings' => [
				'id' => 'automatic_platform_optimization',
				'value' => true,
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
			'plugin_enabled' => true,
			'email' => 'example@email.mail',
			'key' => 'azz12feee',
			'domain' => 'example.org',
			'settings' => [
				'id' => 'automatic_platform_optimization',
				'value' => true,
			],
			'screen' => (object) [
				'id' => 'settings_page_wprocket'
			],
			'cloudflare_mobile_cache' => [
				'id' => 'automatic_platform_optimization_cache_by_device_type',
				'value' => false
			],
			'mobile_cache' => true,
			'should_display' => false,
		],
		'expected' => [
			'notice' => [
				'message' => '<p>You are using “Separate cache files for mobile devices”. You need to activate “Cache by Device Type” on Cloudflare APO to serve the right version of the cache: (add the path to activate “Cache by Device Type” on Cloudflare plugin)</p><a href="https://docs.wp-rocket.me/article/1313-create-different-cache-files-with-dynamic-and-mandatory-cookies">More info</a>'
			]
		]
	],
	'mobileCacheMismatchMobileDisabledShouldDisplayNotice' => [
		'config' => [
			'plugin_enabled' => true,
			'email' => 'example@email.mail',
			'key' => 'azz12feee',
			'domain' => 'example.org',
			'settings' => [
				'id' => 'automatic_platform_optimization',
				'value' => true,
			],
			'screen' => (object) [
				'id' => 'settings_page_wprocket'
			],
			'cloudflare_mobile_cache' => [
				'id' => 'automatic_platform_optimization_cache_by_device_type',
				'value' => true
			],
			'mobile_cache' => false,
			'should_display' => false,
		],
		'expected' => [
			'notice' => [
				'message' => '<p>You have “Cache by Device Type” enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.</p>'
			]
		]
	]
];
