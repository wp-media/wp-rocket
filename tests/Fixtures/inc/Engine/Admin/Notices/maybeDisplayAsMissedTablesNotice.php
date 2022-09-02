<?php

return [
    'shouldDisplayNothingWhenOnASToolPage' => [
        'config' => [
            'current_screen'    => (object) [
                'id' => 'tools_page_action-scheduler',
            ],
            'transient'         => 4,
            'is_admin' => true,
        ],
        'expected' => [
            'notice' => false,
            'html' => '',
        ],
    ],
    'shouldDisplayNothingWhenNotAdmin' => [
        'config' => [
            'current_screen'    => (object) [
                'id' => 'dashboard',
            ],
            'transient' => 4,
            'is_admin' => false,
        ],
        'expected' => [
            'notice' => false,
            'html' => '',
        ],
    ],
    'shouldDisplayNothingWhenASTablesAreCorrect' => [
        'config' => [
            'current_screen'    => (object) [
                'id' => 'dashboard',
            ],
            'transient'         => 4,
            'is_admin' => false,
            'found_as_tables' => [
                'wp_actionscheduler_actions',
                'wp_actionscheduler_claims',
                'wp_actionscheduler_groups',
                'wp_actionscheduler_logs',
            ],
        ],
        'expected' => [
            'notice' => false,
            'html' => '',
        ],
    ],
    'shouldDisplayNoticeWhenASTablesAreMissing' => [
        'config' => [
            'current_screen'    => (object) [
                'id' => 'dashboard',
            ],
            'transient' => 4,
            'is_admin' => true,
            'found_as_tables' => [
                'wp_actionscheduler_claims',
                'wp_actionscheduler_groups',
                'wp_actionscheduler_logs',
            ],
            'as_tool_link' => 'example.com/wp-admin/tools.php?page=action-scheduler',
        ],
        'expected' => [
            'notice' => [
                'status'  => 'error',
                'message' => '<strong>WP Rocket</strong>: We detected missing database table related to Action Scheduler. Please visit the following <a href="example.com/wp-admin/tools.php?page=action-scheduler">URL</a> to recreate it, as it is needed for WP Rocket to work correctly.',
                'id'      => 'rocket-notice-as-missed-tables',
            ],
            'html' => '<strong>WP Rocket</strong>: We detected missing database table related to Action Scheduler',
        ],
    ],
];
