<?php

$rocket_clean_domain = [
	'vfs://public/wp-content/cache/wp-rocket/example.org/'                => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => null,
];
$rocket_clean_minify = [
	'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js'    => null,
	'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz' => null,
	'vfs://public/wp-content/cache/min/1/wp-content/plugins/imagify/assets/js/'  => null,
	'vfs://public/wp-content/cache/min/1/wp-includes/js/'                        => [],

	'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js'    => null,
	'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js.gz' => null,
];

$advanced_cache = require WP_ROCKET_TESTS_FIXTURES_DIR . '/content/advancedCacheContent.php';
$htaccess       = require WP_ROCKET_TESTS_FIXTURES_DIR . '/content/htaccessContent.php';

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
		'version'             => '3.15',
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
				'flush_rocket_htaccess'       => [
					$htaccess['start'],
					$htaccess['FileETag'],
					$htaccess['mod_alias'],
					$htaccess['wp_rules_start'],
					$htaccess['end'],
				],
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
				'flush_rocket_htaccess'       => [
					$htaccess['start'],
					$htaccess['FileETag'],
					$htaccess['mod_alias'],
					$htaccess['wp_rules_start'],
					$htaccess['end'],
				],
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
				'flush_rocket_htaccess'       => [
					$htaccess['start'],
					$htaccess['FileETag'],
					$htaccess['mod_alias'],
					$htaccess['wp_rules_start'],
					$htaccess['end'],
				],
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
				'rocket_clean_minify'         => $rocket_clean_minify,
				'flush_rocket_htaccess'       => [
					$htaccess['start'],
					$htaccess['FileETag'],
					$htaccess['mod_alias'],
					$htaccess['wp_rules_start'],
					$htaccess['end'],
				],
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
				'rocket_clean_minify'         => $rocket_clean_minify,
				'flush_rocket_htaccess'       => [
					$htaccess['start'],
					$htaccess['FileETag'],
					$htaccess['mod_alias'],
					$htaccess['wp_rules_start'],
					$htaccess['end'],
				],
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
				'rocket_clean_minify'         => $rocket_clean_minify,
				'flush_rocket_htaccess'       => [
					$htaccess['start'],
					$htaccess['FileETag'],
					$htaccess['CORS'],
					$htaccess['mod_alias'],
					$htaccess['wp_rules_start'],
					$htaccess['end'],
				],
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
				'flush_rocket_htaccess'               => [
					$htaccess['start'],
					$htaccess['FileETag'],
					$htaccess['mod_alias'],
					$htaccess['end'],
				],
				'rocket_generate_config_file'         => '<?php $var = "Some contents.";',
				'rocket_generate_advanced_cache_file' => $advanced_cache['mobile'],
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
				'flush_rocket_htaccess'       => [
					$htaccess['start'],
					$htaccess['FileETag'],
					$htaccess['mod_alias'],
					$htaccess['wp_rules_start'],
					$htaccess['end'],
				],
				'rocket_generate_config_file' => '<?php $var = "Some contents.";',
				'set_transient'               => '1',
			],
		],

		'versionShouldNotTriggerClearing' => [
			'settings' => [
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
				'version'             => '3.16',
			],
			'expected' => [
				'flush_rocket_htaccess'       => [
					$htaccess['start'],
					$htaccess['FileETag'],
					$htaccess['mod_alias'],
					$htaccess['wp_rules_start'],
					$htaccess['end'],
				],
				'rocket_generate_config_file' => '<?php $var = "Some contents.";',
			],
		]
	],
];
