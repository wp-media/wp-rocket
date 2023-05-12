<?php
return [
    'rocket_cache_dynamic_cookiesFilterShouldAddGeoTCookies' => [
        'config' => [
			  'hook' => 'rocket_cache_dynamic_cookies',
              'cookies' => [
				  'cookie1',
				  'cookie2',
				  'geot_rocket_cookie3',
			  ],
			  'enabled_cookies' => [
				  'cookie3',
				  'cookie4',
			  ]
        ],
        'expected' => [
			'cookie1',
			'cookie2',
			'geot_rocket_cookie3',
			'geot_rocket_cookie4',
		]
    ],
	'rocket_cache_mandatory_cookiesFilterShouldAddGeoTCookies' => [
		'config' => [
			'hook' => 'rocket_cache_mandatory_cookies',
			'cookies' => [
				'cookie1',
				'cookie2',
				'geot_rocket_cookie3',
			],
			'enabled_cookies' => [
				'cookie3',
				'cookie4',
			]
		],
		'expected' => [
			'cookie1',
			'cookie2',
			'geot_rocket_cookie3',
			'geot_rocket_cookie4',
		]
	],

];
