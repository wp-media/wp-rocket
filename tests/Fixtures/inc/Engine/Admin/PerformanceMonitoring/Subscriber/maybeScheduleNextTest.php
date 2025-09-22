<?php

use WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator;

return [
    'testShouldNotScheduleWhenFreeUser' => [
        'config' => [
            'user_data' => (new UserDataGenerator())
                ->with_pma_active_sku('perf-monitor-free')
                ->generate(),
            'performance_monitoring_enabled' => 'monthly',
            'event_scheduled' => false,
        ],
        'expected' => [
            'scheduled' => false,
        ],
    ],
    'testShouldNotScheduleWhenPMDisabled' => [
        'config' => [
            'user_data' => (new UserDataGenerator())
                ->with_pma_active_sku('perf-monitor-advanced')
                ->generate(),
            'performance_monitoring_enabled' => false,
            'event_scheduled' => false,
        ],
        'expected' => [
            'scheduled' => false,
        ],
    ],
    'testShouldNotScheduleWhenAlreadyScheduled' => [
        'config' => [
            'user_data' => (new UserDataGenerator())
                ->with_pma_active_sku('perf-monitor-advanced')
                ->generate(),
            'performance_monitoring_enabled' => 'monthly',
            'event_scheduled' => true,
        ],
        'expected' => [
            'scheduled' => false,
        ],
    ],
    'testShouldScheduleWhenPremiumUserAndPMEnabledAndNotScheduled' => [
        'config' => [
            'user_data' => (new UserDataGenerator())
                ->with_pma_active_sku('perf-monitor-advanced')
                ->generate(),
            'performance_monitoring_enabled' => 'monthly',
            'event_scheduled' => false,
        ],
        'expected' => [
            'scheduled' => true,
        ],
    ],
];
