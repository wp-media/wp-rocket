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
            'customer_data' => (new UserDataGenerator())->with_pma_active_sku('perf-monitor-advanced'),
            'urls_count' => 3,
        ],
        'expected' => true,
    ],
    'advanced_user_10_urls' => [
        'config' => [
            'customer_data' => (new UserDataGenerator())->with_pma_active_sku('perf-monitor-advanced'),
            'urls_count' => 10,
        ],
        'expected' => false,
    ],
    'free_user_0_urls_no_credits' => [
        'config' => [
            'customer_data' => (new UserDataGenerator()),
            'urls_count' => 0,
            'credits' => 0, // Free user with no credits - should show banner
        ],
        'expected' => true,
    ],
    'free_user_2_urls_no_credits' => [
        'config' => [
            'customer_data' => (new UserDataGenerator()),
            'urls_count' => 2,
            'credits' => 0, // Free user with 2 URLs but no credits - should show banner
        ],
        'expected' => true,
    ],
];
