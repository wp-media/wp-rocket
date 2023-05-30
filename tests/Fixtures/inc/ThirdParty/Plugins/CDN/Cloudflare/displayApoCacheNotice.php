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
	        'mandatory_cookies' => [],
	        'dynamic_cookies' => [],
	        'has_apo' => false,
	        'should_display' => false,
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
			'mandatory_cookies' => [],
			'dynamic_cookies' => [],
			'has_apo' => true,
			'should_display' => false,
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
				'message' => '<p>You are using “Dynamic Cookies Cache”. Cloudflare APO is not yet compatible with that feature.</p><p>You should either disable Cloudflare APO or check with the theme/plugin requiring the use of “Dynamic Cookies Cache” developers for an alternative way to be page-cache friendly. <a href="https://docs.wp-rocket.me/article/1313-create-different-cache-files-with-dynamic-and-mandatory-cookies">More info</a></p>',
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
			'mandatory_cookies' => [
				'cookie'
			],
			'dynamic_cookies' => [],
			'has_apo' => true,
			'should_display' => true,
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
				'message' => '<p>You are using “Dynamic Cookies Cache”. Cloudflare APO is not yet compatible with that feature.</p><p>You should either disable Cloudflare APO or check with the theme/plugin requiring the use of “Dynamic Cookies Cache” developers for an alternative way to be page-cache friendly. <a href="https://docs.wp-rocket.me/article/1313-create-different-cache-files-with-dynamic-and-mandatory-cookies">More info</a></p>',
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
			'response' => [
				'code' => 200
			],
			'mandatory_cookies' => [
				'cookie'
			],
			'dynamic_cookies' => [],
			'has_apo' => true,
			'should_display' => true,
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
				'message' => '<p>You are using “Dynamic Cookies Cache”. Cloudflare APO is not yet compatible with that feature.</p><p>You should either disable Cloudflare APO or check with the theme/plugin requiring the use of “Dynamic Cookies Cache” developers for an alternative way to be page-cache friendly. <a href="https://docs.wp-rocket.me/article/1313-create-different-cache-files-with-dynamic-and-mandatory-cookies">More info</a></p>',
			]
		]
	]
];
