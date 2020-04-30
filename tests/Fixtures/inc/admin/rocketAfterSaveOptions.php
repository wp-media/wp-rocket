<?php

$rocket_clean_domain = [
	'vfs://public/wp-content/cache/wp-rocket/example.org/'                => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => null,
];

$rocket_generate_advanced_cache_file = require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/functions/advancedCacheContent.php';
$flush_rocket_htaccess               = require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/functions/htaccessContent.php';

return [
	'vfs_dir' => 'wp-content/',

	'settings'  => [
		'cache_mobile'        => true,
		'purge_cron_interval' => true,
		'purge_cron_unit'     => true,
		'minify_css'          => false,
		'exclude_css'         => '',
		'minify_js'           => false,
		'exclude_js'          => '',
		'analytics_enabled'   => '',
		'cdn'                 => false,
		'cdn_cnames'          => false,
	],

	// Test data.
	'test_data' => [

		'testShouldBailOutWhenSettingsNotAndArray' => [
			'settings' => 'not an array',
			'expected' => [],
		],

		'testShouldTriggerCleaningsWhenOptionsChange' => [
			'settings' => [
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => false,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
				'foobar'              => 'barbaz', // This one will trigger cleaning and preload.
			],
			'expected' => [
				'rocket_clean_domain'         => $rocket_clean_domain,
				'flush_rocket_htaccess'       => $flush_rocket_htaccess['with_wp_rules'],
				'rocket_generate_config_file' => '<?php $var = "Some contents.";',
			],
		],

		'testShouldNotCleanMinifyCSSWhenMinifyOptionChanges' => [
			'settings' => [
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => true,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
			],
			'expected' => [
				'rocket_clean_domain'         => $rocket_clean_domain,
				'flush_rocket_htaccess'       => $flush_rocket_htaccess['with_wp_rules'],
				'rocket_generate_config_file' => '<?php $var = "Some contents.";',
			],
		],

		'testShouldNotCleanMinifyCSSWhenExcludeOptionChanges' => [
			'settings' => [
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => 'foobar',
				'minify_js'           => false,
				'exclude_js'          => '',
			],
			'expected' => [
				'rocket_clean_domain'         => $rocket_clean_domain,
				'flush_rocket_htaccess'       => $flush_rocket_htaccess['with_wp_rules'],
				'rocket_generate_config_file' => '<?php $var = "Some contents.";',
			],
		],

		'testShouldCleanMinifyJSWhenMinifyOptionChanges' => [
			'settings' => [
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => true,
				'exclude_js'          => '',
			],
			'expected' => [
				'rocket_clean_domain'         => $rocket_clean_domain,
				'rocket_clean_minify'         => [
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js'    => null,
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/plugins/imagify/assets/js/'  => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/js/'                        => [],

					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js'    => null,
					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js.gz' => null,
				],
				'flush_rocket_htaccess'       => $flush_rocket_htaccess['with_wp_rules'],
				'rocket_generate_config_file' => '<?php $var = "Some contents.";',
			],
		],

		'testShouldCleanMinifyJSWhenExcludeOptionChanges' => [
			'settings' => [
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => 'foobar',
			],
			'expected' => [
				'rocket_clean_domain'         => $rocket_clean_domain,
				'rocket_clean_minify'         => [
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js'    => null,
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/plugins/imagify/assets/js/'  => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/js/'                        => [],

					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js'    => null,
					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js.gz' => null,
				],
				'flush_rocket_htaccess'       => $flush_rocket_htaccess['with_wp_rules'],
				'rocket_generate_config_file' => '<?php $var = "Some contents.";',
			],
		],

		'testShouldCleanMinifyJSWhenCdnOptionIsEnabled' => [
			'settings' => [
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
				'cdn'                 => true,
			],
			'expected' => [
				'rocket_clean_domain'         => $rocket_clean_domain,
				'rocket_clean_minify'         => [
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js'     => null,
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz'  => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css'    => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css.gz' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/plugins/imagify/assets/css/'  => null,
					'vfs://public/wp-content/cache/min/1/wp-content/plugins/imagify/assets/js/'   => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/css/'                        => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/js/'                         => [],

					'vfs://public/wp-content/cache/min/3rd-party/' => [],
				],
				'flush_rocket_htaccess'       => $flush_rocket_htaccess['with_wp_rules'],
				'rocket_generate_config_file' => '<?php $var = "Some contents.";',
			],
		],

		'testShouldGenerateAdvancedCacheFileWhenOptionIsEnabled' => [
			'settings' => [
				'cache_mobile'            => true,
				'purge_cron_interval'     => true,
				'purge_cron_unit'         => true,
				'minify_css'              => false,
				'exclude_css'             => '',
				'minify_js'               => false,
				'exclude_js'              => '',
				'do_caching_mobile_files' => true,
			],
			'expected' => [
				'rocket_clean_domain'                 => $rocket_clean_domain,
				'flush_rocket_htaccess'               => $flush_rocket_htaccess['with_wp_rules'],
				'rocket_generate_config_file'         => '<?php $var = "Some contents.";',
				'rocket_generate_advanced_cache_file' => $rocket_generate_advanced_cache_file['starting'] . $rocket_generate_advanced_cache_file['ending'],
			],
		],

		'testShouldEnableAnalyticsWhenOptionIsEnabled' => [
			'settings' => [
				'cache_mobile'        => true,
				'purge_cron_interval' => true,
				'purge_cron_unit'     => true,
				'minify_css'          => false,
				'exclude_css'         => '',
				'minify_js'           => false,
				'exclude_js'          => '',
				'analytics_enabled'   => true,
			],
			'expected' => [
				'rocket_clean_domain'         => $rocket_clean_domain,
				'flush_rocket_htaccess'       => $flush_rocket_htaccess['with_wp_rules'],
				'rocket_generate_config_file' => '<?php $var = "Some contents.";',
				'set_transient'               => '1',
			],
		],
	],
];
