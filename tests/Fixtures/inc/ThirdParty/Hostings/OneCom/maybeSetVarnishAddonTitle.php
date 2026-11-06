<?php

return [
    'testShouldReturnDefaultVarnishSetting' =>[
        'config' => [
	        'onecom_performance_plugin_enabled' => true,
            'is_varnish_active' => false,
            'varnish_field_settings' => [
                'varnish_auto_purge' => [
                    'type'              => 'one_click_addon',
                    'label'             => 'Varnish',
                    'logo'              => [
                        'url'    => '/path-to/home/wp-content/plugins/wp-rocket/assets/img/logo-varnish.svg',
                        'width'  => 152,
                        'height' => 135,
                    ],
                    'title'             => 'If Varnish runs on your server, you must activate this add-on.',
                    'description'       => 'Varnish cache will be purged each time WP Rocket clears its cache to ensure content is always up-to-date.',
                    'section'           => 'one_click',
                    'page'              => 'addons',
                    'settings_page'     => 'varnish',
                    'default'           => 0,
                    'sanitize_callback' => 'sanitize_checkbox',
                ],
            ],
        ],
        'expected' => [
            'title' => 'If Varnish runs on your server, you must activate this add-on.'
        ],
    ],
    'testShouldReturnDefaultVarnishSettingWhenPluginIsDisabled' =>[
	    'config' => [
		    'onecom_performance_plugin_enabled' => false,
		    'is_varnish_active' => true,
		    'varnish_field_settings' => [
			    'varnish_auto_purge' => [
				    'type'              => 'one_click_addon',
				    'label'             => 'Varnish',
				    'logo'              => [
					    'url'    => '/path-to/home/wp-content/plugins/wp-rocket/assets/img/logo-varnish.svg',
					    'width'  => 152,
					    'height' => 135,
				    ],
				    'title'             => 'If Varnish runs on your server, you must activate this add-on.',
				    'description'       => 'Varnish cache will be purged each time WP Rocket clears its cache to ensure content is always up-to-date.',
				    'section'           => 'one_click',
				    'page'              => 'addons',
				    'settings_page'     => 'varnish',
				    'default'           => 0,
				    'sanitize_callback' => 'sanitize_checkbox',
			    ],
		    ],
	    ],
	    'expected' => [
		    'title' => 'If Varnish runs on your server, you must activate this add-on.'
	    ],
    ],
    'testShouldReturnUpdatedVarnishSetting' =>[
        'config' => [
	        'onecom_performance_plugin_enabled' => true,
            'is_varnish_active' => true,
            'varnish_field_settings' => [
                'varnish_auto_purge' => [
                    'type'              => 'one_click_addon',
                    'label'             => 'Varnish',
                    'logo'              => [
                        'url'    => '/path-to/home/wp-content/plugins/wp-rocket/assets/img/logo-varnish.svg',
                        'width'  => 152,
                        'height' => 135,
                    ],
                    'title'             => 'If Varnish runs on your server, you must activate this add-on.',
                    'description'       => 'Varnish cache will be purged each time WP Rocket clears its cache to ensure content is always up-to-date.',
                    'section'           => 'one_click',
                    'page'              => 'addons',
                    'settings_page'     => 'varnish',
                    'default'           => 0,
                    'sanitize_callback' => 'sanitize_checkbox',
                ],
            ],
        ],
        'expected' => [
            'title' => 'Your site is hosted on One.com, we have enabled Varnish auto-purge for compatibility.',
        ],
    ],
];
