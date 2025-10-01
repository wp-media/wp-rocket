<?php

use WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator;

return [
    'testShouldNotScheduleWhenFreeUser' => [
        'config' => [
            'user_data' => (new UserDataGenerator())
                ->with_rocket_insights_active_sku('perf-monitor-free')
                ->generate(),
            'performance_monitoring_enabled' => true,
            'schedule_frequency' => 'monthly',
            'event_scheduled' => false,
        ],
        'expected' => [
            'scheduled' => false,
        ],
    ],
    'testShouldNotScheduleWhenPMDisabled' => [
        'config' => [
            'user_data' => (new UserDataGenerator())
                ->with_rocket_insights_active_sku('perf-monitor-advanced')
                ->generate(),
            'performance_monitoring_enabled' => false,
			'schedule_frequency' => 'monthly',
			'event_scheduled' => false,
        ],
        'expected' => [
            'scheduled' => false,
        ],
    ],
    'testShouldNotScheduleWhenAlreadyScheduled' => [
        'config' => [
            'user_data' => (new UserDataGenerator())
                ->with_rocket_insights_active_sku('perf-monitor-advanced')
                ->generate(),
            'performance_monitoring_enabled' => true,
			'schedule_frequency' => 'monthly',
			'event_scheduled' => true,
        ],
        'expected' => [
            'scheduled' => false,
        ],
    ],
    'testShouldScheduleWhenPremiumUserAndPMEnabledAndNotScheduled' => [
        'config' => [
            'user_data' => (new UserDataGenerator())
                ->with_rocket_insights_active_sku('perf-monitor-advanced')
                ->generate(),
            'performance_monitoring_enabled' => true,
			'schedule_frequency' => 'monthly',
			'event_scheduled' => false,
        ],
        'expected' => [
            'scheduled' => true,
        ],
    ],
];
