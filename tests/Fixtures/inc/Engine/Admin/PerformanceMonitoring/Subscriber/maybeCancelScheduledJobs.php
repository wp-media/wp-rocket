<?php

use WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator;

return [
    'testShouldNotCancelWhenPremiumUser' => [
        'config' => [
            'user_data' => (new UserDataGenerator())
                ->with_pma_active_sku('perf-monitor-advanced')
                ->generate(),
            'performance_monitoring_enabled' => true,
            'event_scheduled' => true,
        ],
        'expected' => [
            'unscheduled' => false,
        ],
    ],
    'testShouldNotCancelWhenFreeUserAndPMEnabled' => [
        'config' => [
            'user_data' => (new UserDataGenerator())
                ->with_pma_active_sku('perf-monitor-free')
                ->generate(),
            'performance_monitoring_enabled' => true,
            'event_scheduled' => true,
        ],
        'expected' => [
            'unscheduled' => false,
        ],
    ],
    'testShouldNotCancelWhenFreeUserAndPMDisabledButNoEvent' => [
        'config' => [
            'user_data' => (new UserDataGenerator())
                ->with_pma_active_sku('perf-monitor-free')
                ->generate(),
            'performance_monitoring_enabled' => false,
            'event_scheduled' => false,
        ],
        'expected' => [
            'unscheduled' => false,
        ],
    ],
    'testShouldCancelWhenFreeUserAndPMDisabledAndEventScheduled' => [
        'config' => [
            'user_data' => (new UserDataGenerator())
                ->with_pma_active_sku('perf-monitor-free')
                ->generate(),
            'performance_monitoring_enabled' => false,
            'event_scheduled' => true,
        ],
        'expected' => [
            'unscheduled' => true,
        ],
    ],
];
