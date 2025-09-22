<?php

return [
	'testShouldResetUserData' => [
		'config' => [
			'user_data' => function() {
				return (new WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator())
					->with_pma_active_sku('perf-monitor-free')
					->with_pma_expiration(time() + 3600) // Not expired
					->generate();
			},
			'updated_user_data' => function() {
				return (new WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator())
					->with_pma_active_sku('perf-monitor-advanced') // Changed to advanced
					->with_pma_expiration(time() + 7200) // Different expiration
					->generate();
			},
		],
		'expected' => [
			'flush_cache_called' => true,
			'set_user_called' => true,
			'sku_before' => 'perf-monitor-free',
			'sku_after' => 'perf-monitor-advanced',
		],
	],
];
