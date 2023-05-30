<?php
return [
    'pluginDisabledShouldReturnSame' => [
        'config' => [
              'settings' => [],
              'plugin_active' => false,
              'cloudflare_api_email' => 'email@test.test',
              'cloudflare_api_key' => '1ef242',
        ],
        'expected' => [

        ]
    ],
    'emptyEmailShouldReturnSame' => [
	    'config' => [
		    'settings' => [],
		    'plugin_active' => true,
		    'cloudflare_api_email' => '',
		    'cloudflare_api_key' => '1ef242',
	    ],
	    'expected' => [

	    ]
    ],
    'emptyAPIKeyShouldReturnSame' => [
	    'config' => [
		    'settings' => [],
		    'plugin_active' => true,
		    'cloudflare_api_email' => 'email@test.test',
		    'cloudflare_api_key' => '',
	    ],
	    'expected' => [

	    ]
    ],

    'shouldAddNotice' => [
	    'config' => [
		    'settings' => [],
		    'plugin_active' => true,
		    'cloudflare_api_email' => 'email@test.test',
		    'cloudflare_api_key' => '1ef242',
	    ],
	    'expected' => [
			'title' => 'Your site is using the official Cloudflare plugin. We have enabled Cloudflare auto-purge for compatibility. If you have APO activated, it is also compatible.',
			'description' => 'Cloudflare cache will be purged each time WP Rocket clears its cache to ensure content is always up-to-date.',
		    'helper' => ''
	    ]
    ],

];
