<?php
return [
    'pluginDisabledShouldReturnSame' => [
        'config' => [
			  'active_plugins' => [
				 'cloudflare/cloudflare.php'
			  ],
              'settings' => [],
              'plugin_active' => false,
              'cloudflare_api_email' => 'email@test.test',
              'cloudflare_api_key' => '1ef242',
			  'cloudflare_cached_domain_name' => 'domain',
		],
        'expected' => [

        ]
    ],
    'emptyEmailShouldReturnSame' => [
	    'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
			'settings' => [],
		    'plugin_active' => true,
		    'cloudflare_api_email' => '',
		    'cloudflare_api_key' => '1ef242',
			'cloudflare_cached_domain_name' => 'domain',
		],
	    'expected' => [

	    ]
    ],
    'emptyAPIKeyShouldReturnSame' => [
	    'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
		    'settings' => [],
		    'plugin_active' => true,
		    'cloudflare_api_email' => 'email@test.test',
		    'cloudflare_api_key' => '',
			'cloudflare_cached_domain_name' => 'domain',
		],
	    'expected' => [

	    ]
    ],

	'emptyDomainShouldReturnSame' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
			'settings' => [],
			'plugin_active' => false,
			'cloudflare_api_email' => 'email@test.test',
			'cloudflare_api_key' => '1ef242',
			'cloudflare_cached_domain_name' => '',
		],
		'expected' => [

		]
	],

    'shouldAddNotice' => [
	    'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
		    'settings' => [],
		    'plugin_active' => true,
		    'cloudflare_api_email' => 'email@test.test',
		    'cloudflare_api_key' => '1ef242',
			'cloudflare_cached_domain_name' => 'domain',
		],
	    'expected' => [
			'title' => 'Your site is using the official Cloudflare plugin. We have enabled Cloudflare auto-purge for compatibility. If you have APO activated, it is also compatible.',
			'description' => 'Cloudflare cache will be purged each time WP Rocket clears its cache to ensure content is always up-to-date.',
		    'helper' => ''
	    ]
    ],

];
