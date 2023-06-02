<?php
return [
	'apoDisabledShouldDisplayNothing' => [
		'config' => [
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
			'mandatory_cookies' => [],
			'dynamic_cookies' => [],
			'has_apo' => false,
			'should_display' => false,
			'beacon_response' => [
				'id'  => '602593e90a2dae5b58faee1e',
				'url' => 'https://docs.wp-rocket.me/article/1444-using-cloudflare-apo-with-wp-rocket?utm_source=wp_plugin&utm_medium=wp_rocket',
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
			]
		]
	],
	'noRightShouldDisplayNothing' => [
		'config' => [
			'home_url' => 'http://example.org',
			'headers' => [

			],
			'response' => [
				'code' => 200
			],
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
			]
		]
	],
	'noRightShouldDisplayNothing' => [
		'config' => [
			'home_url' => 'http://example.org',
			'headers' => [

			],
			'response' => [
				'code' => 200
			],
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
			]
		]
	],
	'emptyCookiesShouldDisplayNothing' => [
		'config' => [
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
			]
		]
	],
	'mandatoryCookiesShouldDisplayNotice' => [
		'config' => [
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
			]
		]
	],
	'dynamicCookiesShouldDisplayNotice' => [
		'config' => [
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
			]
		]
	]
];
