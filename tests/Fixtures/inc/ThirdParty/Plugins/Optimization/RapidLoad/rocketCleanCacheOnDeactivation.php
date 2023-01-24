<?php
$rocket_clean_domain = [
	'vfs://public/wp-content/cache/wp-rocket/example.org/'                => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-123456/' => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org-tester-987654/'  => null,
];

return [
    'vfs_dir'   => 'wp-content/cache/',

    'test_data' => [
        'shouldDoNothingWhenRapidLoadIsNotActive' => [
            'config' => [
                'autoptimize_uucss_settings' => [],
                'file' => '',
            ],
            'expected' => [
                'file' => 'unusedcss/unusedcss.php',
                'rocket_clean_domain' => $rocket_clean_domain,
            ],
        ],
        'shouldDoNothingWhenDeactivatedPluginIsNotRapidLoad' => [
            'config' => [
                'autoptimize_uucss_settings' => [
                    'uucss_api_key_verified' => 1,
                ],
                'file' => 'wp-rocket/wp-rocket.php',
                'rocket_dismiss_box' => 'rocket_warning_plugin_modification',
            ],
            'expected' => [
                'file' => 'unusedcss/unusedcss.php',
                'rocket_clean_domain' => $rocket_clean_domain,
            ],
        ],
        'shouldCleanCacheWhenRapidLoadIsDeactivated' => [
            'config' => [
                'autoptimize_uucss_settings' => [
                    'uucss_api_key_verified' => 1,
                ],
                'file' => 'unusedcss/unusedcss.php',
                'rocket_dismiss_box' => 'rocket_warning_plugin_modification',
            ],
            'expected' => [
                'file' => 'unusedcss/unusedcss.php',
                'rocket_clean_domain' => $rocket_clean_domain,
            ],
        ],
    ]
];