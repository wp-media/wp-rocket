<?php

return [
	'testShouldReturnEmptyArrayWhenOptionsAreEmpty' => [
		'config'   => [
			'options' => [],
		],
		'expected' => [],
	],
	'testShouldFilterOutAllDenylistKeysWhenAllPresent' => [
		'config'   => [
			'options' => [
				'cache_mobile'            => 1,
				'do_caching_mobile_files' => 1,
				'secret_cache_key'        => 'abc123secret',
				'cache_ssl'               => 1,
				'minify_css_key'          => 'css-key-123',
				'minify_js_key'           => 'js-key-456',
				'defer_all_js_safe'       => 1,
				'preload_fonts'           => 1,
				'dns_prefetch'            => [ 'example.com' ],
				'cloudflare_email'        => 'admin@example.com',
				'cloudflare_api_key'      => 'cf-api-key-secret',
				'cloudflare_zone_id'      => 'zone-123',
				'cloudflare_old_settings' => [ 'old' => 'settings' ],
				'sucury_waf_api_key'      => 'sucuri-key',
				'consumer_key'            => 'consumer-key-secret',
				'consumer_email'          => 'consumer@example.com',
				'secret_key'              => 'top-secret-key',
				'license'                 => 'license-data',
			],
		],
		'expected' => [],
	],
	'testShouldReturnAllOptionsWhenNoDenylistKeysPresent' => [
		'config'   => [
			'options' => [
				'cache_webp'        => 1,
				'cache_logged_user' => 0,
				'minify_css'        => 1,
				'minify_js'         => 1,
				'lazyload'          => 1,
				'cdn'               => 0,
			],
		],
		'expected' => [
			'cache_webp'        => 1,
			'cache_logged_user' => 0,
			'minify_css'        => 1,
			'minify_js'         => 1,
			'lazyload'          => 1,
			'cdn'               => 0,
		],
	],
	'testShouldFilterOnlyDenylistKeysWhenMixed' => [
		'config'   => [
			'options' => [
				// Allowed keys.
				'cache_webp'           => 1,
				'minify_css'           => 1,
				'delay_js'             => 1,
				'remove_unused_css'    => 0,
				'lazyload'             => 1,
				'manual_preload'       => 1,
				'cdn'                  => 1,
				'cdn_cnames'           => [ 'cdn.example.com' ],
				'varnish_auto_purge'   => 0,
				// Denylist keys - should be filtered out.
				'secret_cache_key'     => 'secret-value',
				'consumer_key'         => 'consumer-secret',
				'consumer_email'       => 'secret@example.com',
				'license'              => 'license-secret',
				'cloudflare_api_key'   => 'cf-secret',
				'sucury_waf_api_key'   => 'sucuri-secret',
			],
		],
		'expected' => [
			'cache_webp'         => 1,
			'minify_css'         => 1,
			'delay_js'           => 1,
			'remove_unused_css'  => 0,
			'lazyload'           => 1,
			'manual_preload'     => 1,
			'cdn'                => 1,
			'cdn_cnames'         => [ 'cdn.example.com' ],
			'varnish_auto_purge' => 0,
		],
	],
	'testShouldHandleArrayValuesCorrectly' => [
		'config'   => [
			'options' => [
				'cache_reject_uri'           => [ '/cart/', '/checkout/' ],
				'cache_reject_cookies'       => [ 'woocommerce_items_in_cart' ],
				'exclude_css'                => [ 'plugin.css', 'theme.css' ],
				'exclude_js'                 => [ 'analytics.js' ],
				'cdn_cnames'                 => [ 'cdn1.example.com', 'cdn2.example.com' ],
				'remove_unused_css_safelist' => [ '.keep-this', '#important' ],
				// Denylist - should be filtered.
				'dns_prefetch'               => [ 'prefetch.example.com' ],
			],
		],
		'expected' => [
			'cache_reject_uri'           => [ '/cart/', '/checkout/' ],
			'cache_reject_cookies'       => [ 'woocommerce_items_in_cart' ],
			'exclude_css'                => [ 'plugin.css', 'theme.css' ],
			'exclude_js'                 => [ 'analytics.js' ],
			'cdn_cnames'                 => [ 'cdn1.example.com', 'cdn2.example.com' ],
			'remove_unused_css_safelist' => [ '.keep-this', '#important' ],
		],
	],
	'testShouldPreserveOptionValuesOfDifferentTypes' => [
		'config'   => [
			'options' => [
				'cache_webp'                  => 1,
				'minify_css'                  => 0,
				'critical_css'                => 'body{margin:0}',
				'purge_cron_interval'         => 10,
				'purge_cron_unit'             => 'HOUR_IN_SECONDS',
				'automatic_cleanup_frequency' => 'weekly',
				'heartbeat_site_behavior'     => '',
				'cdn_cnames'                  => [ 'cdn.example.com' ],
				// Denylist.
				'secret_key'                  => 'should-be-filtered',
			],
		],
		'expected' => [
			'cache_webp'                  => 1,
			'minify_css'                  => 0,
			'critical_css'                => 'body{margin:0}',
			'purge_cron_interval'         => 10,
			'purge_cron_unit'             => 'HOUR_IN_SECONDS',
			'automatic_cleanup_frequency' => 'weekly',
			'heartbeat_site_behavior'     => '',
			'cdn_cnames'                  => [ 'cdn.example.com' ],
		],
	],
];
