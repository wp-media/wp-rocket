<?php

$settings = [
    'defer_all_js' => 0,
    'lazyload' => 0,
    'lazyload_iframes' => 0,
    'lazyload_youtube' => 0,
    'database_revisions' => 0,
    'database_auto_drafts' => 0,
    'database_trashed_posts' => 0,
    'database_spam_comments' => 0,
    'database_trashed_comments' => 0,
    'database_optimize_tables' => 0,
    'schedule_automatic_cleanup' => 0,
    'do_cloudflare' => 0,
    'cloudflare_devmode' => 0,
    'cloudflare_auto_settings' => 0,
    'cloudflare_protocol_rewrite' => 0,
    'sucury_waf_cache_sync' => 0,
    'cdn' => 0,
    'varnish_auto_purge' => 0,
    'image_dimensions' => 0,
    'optimize_css_delivery' => 0,
];

$settings_with_cache_reject_uri = [
    'defer_all_js' => 0,
    'lazyload' => 0,
    'lazyload_iframes' => 0,
    'lazyload_youtube' => 0,
    'database_revisions' => 0,
    'database_auto_drafts' => 0,
    'database_trashed_posts' => 0,
    'database_spam_comments' => 0,
    'database_trashed_comments' => 0,
    'database_optimize_tables' => 0,
    'schedule_automatic_cleanup' => 0,
    'do_cloudflare' => 0,
    'cloudflare_devmode' => 0,
    'cloudflare_auto_settings' => 0,
    'cloudflare_protocol_rewrite' => 0,
    'sucury_waf_cache_sync' => 0,
    'cdn' => 0,
    'varnish_auto_purge' => 0,
    'image_dimensions' => 0,
    'optimize_css_delivery' => 0,
    'cache_reject_uri' => [],
];

$settings_with_cache_reject_uri_changed = [
    'defer_all_js' => 0,
    'lazyload' => 0,
    'lazyload_iframes' => 0,
    'lazyload_youtube' => 0,
    'database_revisions' => 0,
    'database_auto_drafts' => 0,
    'database_trashed_posts' => 0,
    'database_spam_comments' => 0,
    'database_trashed_comments' => 0,
    'database_optimize_tables' => 0,
    'schedule_automatic_cleanup' => 0,
    'do_cloudflare' => 0,
    'cloudflare_devmode' => 0,
    'cloudflare_auto_settings' => 0,
    'cloudflare_protocol_rewrite' => 0,
    'sucury_waf_cache_sync' => 0,
    'cdn' => 0,
    'varnish_auto_purge' => 0,
    'image_dimensions' => 0,
    'optimize_css_delivery' => 0,
    'cache_reject_uri' => [
        '/hello-world/',
    ],
];

$settings_with_pattern_in_cache_reject_uri_changed = [
    'defer_all_js' => 0,
    'lazyload' => 0,
    'lazyload_iframes' => 0,
    'lazyload_youtube' => 0,
    'database_revisions' => 0,
    'database_auto_drafts' => 0,
    'database_trashed_posts' => 0,
    'database_spam_comments' => 0,
    'database_trashed_comments' => 0,
    'database_optimize_tables' => 0,
    'schedule_automatic_cleanup' => 0,
    'do_cloudflare' => 0,
    'cloudflare_devmode' => 0,
    'cloudflare_auto_settings' => 0,
    'cloudflare_protocol_rewrite' => 0,
    'sucury_waf_cache_sync' => 0,
    'cdn' => 0,
    'varnish_auto_purge' => 0,
    'image_dimensions' => 0,
    'optimize_css_delivery' => 0,
    'cache_reject_uri' => [
        '/hello-world/',
        '/2022/(.*)',
    ],
];

return [
    'vfs_dir'   => 'wp-content/cache/wp-rocket/',

    'settings'  => $settings,

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org' => [
						'index.html'        => '',
						'index.html_gzip'   => '',
						'hello-world' => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
					],
				],
			],
		],
	],

    'test_data' => [
        'testShouldBailOutIfCacheRejectUriNotInSettings' => [
            'config' => [
                'old_value' => $settings,
                'value' => $settings,
            ],
            'expected' => [],
        ],
        'testShouldBailOutIfCacheRejectUriValueHasNotChanged' => [
            'config' => [
                'old_value' => $settings_with_cache_reject_uri,
                'value' => $settings_with_cache_reject_uri,
            ],
            'expected' => [],
        ],
        'testShouldCleanCachePartiallyWithPatternInCacheRejectUri' => [
            'config' => [
                'old_value' => $settings_with_cache_reject_uri,
                'value' => $settings_with_pattern_in_cache_reject_uri_changed,
                'db_url_result' => [
                    (object) [
                       'url' => 'https://example.org/2022/11/15/sed-laboriosam-quibusdam-aliquam-et-eius',
                       'status' => 'completed',
                    ],
                    (object) [
                       'url' => 'https://example.org/2022/11/15/dolorem-sed-consequatur-et-in-accusantium',
                       'status' => 'completed',
                    ],
                ],
                'urls' => [
                    'https://example.org/hello-world/',
                    'https://example.org/2022/11/15/sed-laboriosam-quibusdam-aliquam-et-eius',
                    'https://example.org/2022/11/15/dolorem-sed-consequatur-et-in-accusantium',
                ],
            ],
            'expected' => [
                'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/hello-world/index.html'                    => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/hello-world/index.html_gzip'               => null,
				],
            ],
        ],
        'testShouldCleanCachePartially' => [
            'config' => [
                'old_value' => $settings_with_cache_reject_uri,
                'value' => $settings_with_cache_reject_uri_changed,
                'urls' => [
                    'https://example.org/hello-world/',
                ],
            ],
            'expected' => [
                'cleaned' => [
					'vfs://public/wp-content/cache/wp-rocket/example.org/hello-world/index.html'                    => null,
					'vfs://public/wp-content/cache/wp-rocket/example.org/hello-world/index.html_gzip'               => null,
				],
            ],
        ],
    ],
];