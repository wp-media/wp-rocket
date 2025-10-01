<?php

use WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator;

return [
    'free_user_0_urls' => [
        'config' => [
            'customer_data' => (new UserDataGenerator()),
            'urls_count' => 0,
        ],
        'expected' => true,
    ],
    'free_user_3_urls' => [
        'config' => [
            'customer_data' => (new UserDataGenerator()),
            'urls_count' => 3,
        ],
        'expected' => false,
    ],
    'advanced_user_3_urls' => [
        'config' => [
            'customer_data' => (new UserDataGenerator())->with_rocket_insights_active_sku('perf-monitor-advanced'),
            'urls_count' => 3,
        ],
        'expected' => true,
    ],
    'advanced_user_10_urls' => [
        'config' => [
            'customer_data' => (new UserDataGenerator())->with_rocket_insights_active_sku('perf-monitor-advanced'),
            'urls_count' => 10,
        ],
        'expected' => false,
    ],
];
