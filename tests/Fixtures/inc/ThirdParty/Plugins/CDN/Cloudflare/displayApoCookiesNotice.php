<?php
return [
	'apoDisabledShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
			],
			'home_url' => 'http://example.org',
			'headers' => [

			],
			'response' => [
				'code' => 200
			],
			'screen' => (object) [
				'id' => 'settings_page_wprocket',
			],
			'right_screen' => true,
			'can' => true,
			'is_plugin_activated' => true,
			'plugin_enabled' => true,
			'cloudflare_api_email' => 'example@email.mail',
			'cloudflare_api_key' => 'azz12feee',
			'cloudflare_cached_domain_name' => 'example.org',
			'mandatory_cookies' => [],
			'dynamic_cookies' => [],
			'has_apo' => false,
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
			'home_url' => 'http://example.org',
			'response' => [
				'code' => 200
			],
			'configs' => [
				'method' => 'GET',
			],
			'notice' => [
				'status' => 'warning',
				'dismissible'          => '',
				'message' => 'You are using "Dynamic Cookies Cache". Cloudflare APO is not yet compatible with that feature.<br>You should either disable Cloudflare APO or check with the theme/plugin requiring the use of “Dynamic Cookies Cache” developers for an alternative way to be page-cache friendly. <a href="https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket" data-beacon-article="602593e90a2dae5b58faee1e" target="_blank" rel="noopener noreferrer">More info</a>',
			],
			'notice_content' => 'You are using "Dynamic Cookies Cache". Cloudflare APO is not yet compatible with that feature.<br>You should either disable Cloudflare APO or check with the theme/plugin requiring the use of “Dynamic Cookies Cache” developers for an alternative way to be page-cache friendly. <a href="https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="602593e90a2dae5b58faee1e" target="_blank" rel="noopener noreferrer">More info</a>',
		]
	],
	'noRightShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
			'home_url' => 'http://example.org',
			'headers' => [

			],
			'response' => [
				'code' => 200
			],
			'is_plugin_activated' => true,
			'plugin_enabled' => true,
			'cloudflare_api_email' => 'example@email.mail',
			'cloudflare_api_key' => 'azz12feee',
			'cloudflare_cached_domain_name' => 'example.org',
			'can' => true,
			'mandatory_cookies' => [],
			'dynamic_cookies' => [],
			'screen' => (object) [
				'id' => 'settings_page_wprocket',
			],
			'right_screen' => true,
			'has_apo' => true,
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
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
			'home_url' => 'http://example.org',
			'response' => [
				'code' => 200
			],
			'configs' => [
				'method' => 'GET',
			],
			'notice' => [
				'status' => 'warning',
				'dismissible'          => '',
				'message' => 'You are using "Dynamic Cookies Cache". Cloudflare APO is not yet compatible with that feature.<br>You should either disable Cloudflare APO or check with the theme/plugin requiring the use of “Dynamic Cookies Cache” developers for an alternative way to be page-cache friendly. <a href="https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket" data-beacon-article="602593e90a2dae5b58faee1e" target="_blank" rel="noopener noreferrer">More info</a>',
			],
			'notice_content' => 'You are using "Dynamic Cookies Cache". Cloudflare APO is not yet compatible with that feature.<br>You should either disable Cloudflare APO or check with the theme/plugin requiring the use of “Dynamic Cookies Cache” developers for an alternative way to be page-cache friendly. <a href="https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="602593e90a2dae5b58faee1e" target="_blank" rel="noopener noreferrer">More info</a>',
		]
	],
	'noRightShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
			'home_url' => 'http://example.org',
			'headers' => [

			],
			'response' => [
				'code' => 200
			],
			'is_plugin_activated' => true,
			'plugin_enabled' => true,
			'cloudflare_api_email' => 'example@email.mail',
			'cloudflare_api_key' => 'azz12feee',
			'cloudflare_cached_domain_name' => 'example.org',
			'can' => false,
			'mandatory_cookies' => [],
			'dynamic_cookies' => [],
			'screen' => (object) [
				'id' => 'settings_page_wprocket',
			],
			'right_screen' => true,
			'has_apo' => true,
			'should_display' => false,
			'beacon_response' => [
				'id'  => '602593e90a2dae5b58faee1e',
				'url' => 'https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket',
			],
			'response_fixture' => [
				'headers' => []
			]
		],
		'expected' => [
			'home_url' => 'http://example.org',
			'response' => [
				'code' => 200
			],
			'configs' => [
				'method' => 'GET',
			],
			'notice' => [
				'status' => 'warning',
				'dismissible'          => '',
				'message' => 'You are using "Dynamic Cookies Cache". Cloudflare APO is not yet compatible with that feature.<br>You should either disable Cloudflare APO or check with the theme/plugin requiring the use of “Dynamic Cookies Cache” developers for an alternative way to be page-cache friendly. <a href="https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket" data-beacon-article="602593e90a2dae5b58faee1e" target="_blank" rel="noopener noreferrer">More info</a>',
			],
			'notice_content' => 'You are using "Dynamic Cookies Cache". Cloudflare APO is not yet compatible with that feature.<br>You should either disable Cloudflare APO or check with the theme/plugin requiring the use of “Dynamic Cookies Cache” developers for an alternative way to be page-cache friendly. <a href="https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="602593e90a2dae5b58faee1e" target="_blank" rel="noopener noreferrer">More info</a>',
		]
	],
	'emptyCookiesShouldDisplayNothing' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
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
			'response' => [
				'code' => 200
			],
			'screen' => (object) [
				'id' => 'settings_page_wprocket',
			],
			'right_screen' => true,
			'can' => true,
			'mandatory_cookies' => [],
			'dynamic_cookies' => [],
			'has_apo' => true,
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
			'home_url' => 'http://example.org',
			'response' => [
				'code' => 200
			],
			'configs' => [
				'method' => 'GET',
			],
			'notice' => [
				'status' => 'warning',
				'dismissible'          => '',
				'message' => 'You are using "Dynamic Cookies Cache". Cloudflare APO is not yet compatible with that feature.<br>You should either disable Cloudflare APO or check with the theme/plugin requiring the use of “Dynamic Cookies Cache” developers for an alternative way to be page-cache friendly. <a href="https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket" data-beacon-article="602593e90a2dae5b58faee1e" target="_blank" rel="noopener noreferrer">More info</a>',
			],
			'notice_content' => 'You are using "Dynamic Cookies Cache". Cloudflare APO is not yet compatible with that feature.<br>You should either disable Cloudflare APO or check with the theme/plugin requiring the use of “Dynamic Cookies Cache” developers for an alternative way to be page-cache friendly. <a href="https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="602593e90a2dae5b58faee1e" target="_blank" rel="noopener noreferrer">More info</a>',
		]
	],
	'mandatoryCookiesShouldDisplayNotice' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
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
			'response' => [
				'code' => 200
			],
			'can' => true,
			'screen' => (object) [
				'id' => 'settings_page_wprocket',
			],
			'right_screen' => true,
			'mandatory_cookies' => [
				'cookie'
			],
			'dynamic_cookies' => [],
			'has_apo' => true,
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
			'home_url' => 'http://example.org',
			'response' => [
				'code' => 200
			],
			'configs' => [
				'method' => 'GET',
			],
			'notice' => [
				'status' => 'warning',
				'dismissible'          => '',
				'message' => 'You are using "Dynamic Cookies Cache". Cloudflare APO is not yet compatible with that feature.<br>You should either disable Cloudflare APO or check with the theme/plugin requiring the use of “Dynamic Cookies Cache” developers for an alternative way to be page-cache friendly. <a href="https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket" data-beacon-article="602593e90a2dae5b58faee1e" target="_blank" rel="noopener noreferrer">More info</a>',
			],
			'notice_content' => 'You are using "Dynamic Cookies Cache". Cloudflare APO is not yet compatible with that feature.<br>
You should either disable Cloudflare APO or check with the theme/plugin requiring the use of “Dynamic Cookies Cache” developers for an alternative way to be page-cache friendly.<a href="https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="602593e90a2dae5b58faee1e" target="_blank" rel="noopener noreferrer">
More info</a>',
			]
	],
	'dynamicCookiesShouldDisplayNotice' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
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
				'id' => 'settings_page_wprocket',
			],
			'right_screen' => true,
			'response' => [
				'code' => 200
			],
			'can' => true,
			'mandatory_cookies' => [],
			'dynamic_cookies' => [
				'cookie'
			],
			'has_apo' => true,
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
			'home_url' => 'http://example.org',
			'response' => [
				'code' => 200
			],
			'configs' => [
				'method' => 'GET',
			],
			'notice' => [
				'status' => 'warning',
				'dismissible'          => '',
				'message' => 'You are using "Dynamic Cookies Cache". Cloudflare APO is not yet compatible with that feature.<br>You should either disable Cloudflare APO or check with the theme/plugin requiring the use of “Dynamic Cookies Cache” developers for an alternative way to be page-cache friendly. <a href="https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket" data-beacon-article="602593e90a2dae5b58faee1e" target="_blank" rel="noopener noreferrer">More info</a>',
			],
			'notice_content' => 'You are using "Dynamic Cookies Cache". Cloudflare APO is not yet compatible with that feature.<br>
You should either disable Cloudflare APO or check with the theme/plugin requiring the use of “Dynamic Cookies Cache” developers for an alternative way to be page-cache friendly.<a href="https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="602593e90a2dae5b58faee1e" target="_blank" rel="noopener noreferrer">
More info</a>',
		]
	]
];
