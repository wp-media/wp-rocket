<?php

return [
	'testShouldReturnEmptyArrayWhenOptionsAreEmpty'  => [
		'config'   => [
			'allowed_keys' => [ 'cache_webp', 'minify_css' ],
			'options'      => [],
		],
		'expected' => [],
	],
	'testShouldReturnOnlyAllowlistedKeys'            => [
		'config'   => [
			'allowed_keys' => [ 'cache_webp', 'minify_css', 'lazyload' ],
			'options'      => [
				'cache_webp'        => 1,
				'minify_css'        => 1,
				'lazyload'          => 0,
				// These are not in the allowlist and must be absent from the result.
				'analytics_enabled' => 1,
				'secret_cache_key'  => 'abc123secret',
			],
		],
		'expected' => [
			'cache_webp' => 1,
			'minify_css' => 1,
			'lazyload'   => 0,
		],
	],
	'testShouldReturnEmptyWhenNoStoredOptionMatchesAllowlist' => [
		'config'   => [
			'allowed_keys' => [ 'cache_webp', 'minify_css' ],
			'options'      => [
				'secret_cache_key' => 'abc123secret',
				'license'          => 'license-data',
				'consumer_key'     => 'consumer-secret',
			],
		],
		'expected' => [],
	],
	'testShouldReturnAllOptionsWhenAllStoredKeysAreAllowed' => [
		'config'   => [
			'allowed_keys' => [ 'cache_webp', 'cache_logged_user', 'minify_css', 'minify_js', 'lazyload', 'cdn' ],
			'options'      => [
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
	'testShouldHandleArrayValuesCorrectly'           => [
		'config'   => [
			'allowed_keys' => [
				'cache_reject_uri',
				'cache_reject_cookies',
				'exclude_css',
				'exclude_js',
				'cdn_cnames',
				'remove_unused_css_safelist',
				'dns_prefetch',
			],
			'options'      => [
				'cache_reject_uri'             => [ '/cart/', '/checkout/' ],
				'cache_reject_cookies'         => [ 'woocommerce_items_in_cart' ],
				'exclude_css'                  => [ 'plugin.css', 'theme.css' ],
				'exclude_js'                   => [ 'analytics.js' ],
				'cdn_cnames'                   => [ 'cdn1.example.com', 'cdn2.example.com' ],
				'remove_unused_css_safelist'   => [ '.keep-this', '#important' ],
				'dns_prefetch'                 => [ 'prefetch.example.com' ],
				// Not in allowlist.
				'delay_js_exclusions_selected' => [ 'woocommerce' ],
			],
		],
		'expected' => [
			'cache_reject_uri'           => [ '/cart/', '/checkout/' ],
			'cache_reject_cookies'       => [ 'woocommerce_items_in_cart' ],
			'exclude_css'                => [ 'plugin.css', 'theme.css' ],
			'exclude_js'                 => [ 'analytics.js' ],
			'cdn_cnames'                 => [ 'cdn1.example.com', 'cdn2.example.com' ],
			'remove_unused_css_safelist' => [ '.keep-this', '#important' ],
			'dns_prefetch'               => [ 'prefetch.example.com' ],
		],
	],
	'testShouldPreserveOptionValuesOfDifferentTypes' => [
		'config'   => [
			'allowed_keys' => [
				'cache_webp',
				'minify_css',
				'purge_cron_interval',
				'purge_cron_unit',
				'automatic_cleanup_frequency',
				'heartbeat_site_behavior',
				'cdn_cnames',
			],
			'options'      => [
				'cache_webp'                  => 1,
				'minify_css'                  => 0,
				'purge_cron_interval'         => 10,
				'purge_cron_unit'             => 'HOUR_IN_SECONDS',
				'automatic_cleanup_frequency' => 'weekly',
				'heartbeat_site_behavior'     => '',
				'cdn_cnames'                  => [ 'cdn.example.com' ],
				// Not in allowlist.
				'secret_key'                  => 'should-be-filtered',
				'critical_css'                => 'body{margin:0}',
			],
		],
		'expected' => [
			'cache_webp'                  => 1,
			'minify_css'                  => 0,
			'purge_cron_interval'         => 10,
			'purge_cron_unit'             => 'HOUR_IN_SECONDS',
			'automatic_cleanup_frequency' => 'weekly',
			'heartbeat_site_behavior'     => '',
			'cdn_cnames'                  => [ 'cdn.example.com' ],
		],
	],
];
