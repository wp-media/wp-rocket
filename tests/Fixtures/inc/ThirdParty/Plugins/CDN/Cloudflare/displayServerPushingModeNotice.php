<?php

return [
    'shouldDisplayNothingWhenNotWPRSettingsPage' => [
        'config' => [
            'current_screen'    => (object) [
                'id' => 'dashboard',
            ],
            'capability'        => true,
            'remove_unused_css' => true,
            'minify_concatenate_css' => true,
            'server_push' => true,
            'boxes' => []
        ],
        'expected' => [
            'return' => false,
            'html' => '',
        ],
    ],
    'shouldDisplayNothingWhenNoCapability' => [
        'config' => [
            'current_screen'    => (object) [
                'id' => 'settings_page_wprocket',
            ],
            'capability'        => false,
            'remove_unused_css' => true,
            'minify_concatenate_css' => true,
            'server_push' => true,
            'boxes' => []
        ],
        'expected' => [
            'return' => false,
            'html' => '',
        ],
    ],
    'shouldDisplayNothingWhenDisabled' => [
        'config' => [
            'current_screen'    => (object) [
                'id' => 'settings_page_wprocket',
            ],
            'capability'        => true,
            'remove_unused_css' => false,
            'minify_concatenate_css' => false,
            'server_push' => false,
            'boxes' => []
        ],
        'expected' => [
            'return' => false,
            'html' => '',
        ],
    ],
    'shouldDisplayNothingWithServerPushDisabled' => [
        'config' => [
            'current_screen'    => (object) [
                'id' => 'settings_page_wprocket',
            ],
            'capability'        => true,
            'remove_unused_css' => true,
            'minify_concatenate_css' => true,
            'server_push' => false,
            'boxes' => []
        ],
        'expected' => [
            'return' => false,
            'html' => '',
        ],
    ],
    'shouldDisplayNothingWhenNoticeDismissed' => [
        'config' => [
            'current_screen'    => (object) [
                'id' => 'settings_page_wprocket',
            ],
            'capability'        => true,
            'remove_unused_css' => true,
            'minify_concatenate_css' => true,
            'server_push' => false,
            'boxes' => [
                'cloudflare_server_push',
            ]
        ],
        'expected' => [
            'return' => false,
            'html' => '',
        ],
    ],
    'shouldDisplayNotice' => [
        'config' => [
            'current_screen'    => (object) [
                'id' => 'settings_page_wprocket',
            ],
            'capability'        => true,
            'remove_unused_css' => true,
            'minify_concatenate_css' => true,
            'server_push' => true,
            'boxes' => []
        ],
        'expected' => [
            'return' => true,
            'html' => 'Cloudflare\'s HTTP/2 Server Push is incompatible with the features of Remove Unused CSS and Combine CSS files. We strongly recommend disabling it.',
        ],
    ],
];
