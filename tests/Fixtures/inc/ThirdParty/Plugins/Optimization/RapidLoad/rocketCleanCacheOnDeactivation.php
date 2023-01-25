<?php

return [
    'test_data' => [
        'shouldDoNothingWhenRapidLoadIsNotActive' => [
            'config' => [
                'autoptimize_uucss_settings' => [],
                'file' => '',
            ],
            'expected' => [
                'file' => 'unusedcss/unusedcss.php',
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
            ],
        ],
    ],
];