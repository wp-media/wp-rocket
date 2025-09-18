<?php

return [
	'testShouldNotDisplayAnythingWhenNoUpgrade' => [
		'config' => [
			'upgrade_param' => false,
			'user_data' => function() {
				return (new WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator())
					->with_pma_active_sku('perf-monitor-free')
					->with_pma_expiration(time() + 3600) // Not expired
					->generate();
			},
		],
		'expected' => [
			'upgrade_action' => false,
			'has_upgrade_notice' => false,
			'has_user' => false,
		],
	],
	'testShouldDisplayUpgradeNoticeWithPriceWhenUpgradeAvailable' => [
		'config' => [
			'upgrade_param' => true,
			'user_data' => function() {
				return (new WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator())
					->with_pma_active_sku('perf-monitor-free')
					->with_pma_expiration(time() + 3600) // Not expired
					->generate();
			},
		],
		'expected' => [
			'upgrade_action' => true,
			'has_upgrade_notice' => true,
			'has_user' => true,
		],
	],
];
