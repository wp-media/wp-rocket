<?php

return [
    'shouldDisplayNothingWhenNotWPRSettingsPage' => [
        'config' => [
            'current_screen'    => (object) [
                'id' => 'dashboard',
            ],
            'capability'        => true,
            'remove_unused_css' => 1,
            'minify_concatenate_css' => 1,
            'server_push' => true,
        ],
        'expected' => false,
    ],
    'shouldDisplayNothingWhenNoCapability' => [
        'config' => [
            'current_screen'    => (object) [
                'id' => 'settings_page_wprocket',
            ],
            'capability'        => false,
            'remove_unused_css' => 1,
            'minify_concatenate_css' => 1,
            'server_push' => true,
        ],
        'expected' => false,
    ],
    'shouldDisplayNothingWhenDisabled' => [
        'config' => [
            'current_screen'    => (object) [
                'id' => 'settings_page_wprocket',
            ],
            'capability'        => true,
            'remove_unused_css' => 0,
            'minify_concatenate_css' => 0,
            'server_push' => false,
        ],
        'expected' => false,
    ],
    'shouldDisplayNothingWithServerPushDisabled' => [
        'config' => [
            'current_screen'    => (object) [
                'id' => 'settings_page_wprocket',
            ],
            'capability'        => true,
            'remove_unused_css' => 1,
            'minify_concatenate_css' => 1,
            'server_push' => false,
        ],
        'expected' => false,
    ],
];
