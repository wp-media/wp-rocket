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
            'html' => 'Cloudflare server pushing mode can create incompatiblities with Remove Unused CSS and Combine CSS features',
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
            'html' => 'Cloudflare server pushing mode can create incompatiblities with Remove Unused CSS and Combine CSS features',
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
            'html' => 'Cloudflare server pushing mode can create incompatiblities with Remove Unused CSS and Combine CSS features',
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
            'html' => 'Cloudflare server pushing mode can create incompatiblities with Remove Unused CSS and Combine CSS features',
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
            'html' => 'Cloudflare server pushing mode can create incompatiblities with Remove Unused CSS and Combine CSS features',
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
            'html' => 'Cloudflare server pushing mode can create incompatiblities with Remove Unused CSS and Combine CSS features',
        ],
    ],
];
