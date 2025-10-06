<?php

namespace WP_Rocket\Tests\Fixtures\inc\Engine\Admin\PerformanceMonitoring\Subscriber;

use WP_Rocket\Tests\Fixtures\Generators\PmaPromoGenerator;
use WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator;

return [
    'testShouldNotDisplayWhenNoUpgrades' => [
        'config' => [
            'customer_data' => (new UserDataGenerator())
                ->with_pma_active_sku('perf-monitor-advanced')
                ->with_reseller_status(0)
                ->generate(),
        ],
        'expected' => '',
    ],
    'testShouldDisplayWithPriceWhenUpgradesAvailable' => [
        'config' => [
            'customer_data' => (new UserDataGenerator())
                ->with_pma_active_sku('perf-monitor-free')
                ->with_reseller_status(0)
                ->generate(),
        ],
        'expected' => '<span class="wpr-currency">
$</span>
<span class="wpr-price-number">
0</span>
<span class="wpr-price-decimal">
.00</span>',
    ],
	'testShouldDisplayPromoPriceWhenPromoActive' => [
		'config' => [
			'customer_data' => (new UserDataGenerator())
				->with_pma_active_sku('perf-monitor-free')
				->with_reseller_status(0)
				->with_promo('perf-monitor-advanced', function (PmaPromoGenerator $generator) {
					$generator->with_price("10.0");
					$generator->with_expires_at(time() + MONTH_IN_SECONDS);
					return $generator;
				})
				->generate(),
		],
		'expected' => '<p class="wpr-pma-price-before-discount">
$0.00</p>
<p class="wpr-pma-price">
<span class="wpr-currency">
$</span>
<span class="wpr-price-number">
10</span>
<span class="wpr-price-decimal">
.00</span>',
	]
];
