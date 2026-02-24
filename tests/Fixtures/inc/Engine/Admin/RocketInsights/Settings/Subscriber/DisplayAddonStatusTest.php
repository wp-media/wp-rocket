<?php


use WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator;

return [
	'testShouldRenderFreeVersionHTMLWhenNotActive' => [
		'config' => [
			'is_live_site' => true,
			'customer_data' => (new UserDataGenerator())->with_reseller_status(0)->generate()
		],
		'expected' => <<<HTML
<span class="wpr-infoAccount wpr-isInvalid">No Subscription</span>
HTML
	],
	'testShouldNotDisplayWhenRocketInsightsDisabled' => [
		'config' => [
			'is_live_site' => true,
			'rocket_insights_enabled' => false,
			'customer_data' => (new UserDataGenerator())
				->with_rocket_insights_active_sku('perf-monitor-advanced')
				->with_rocket_insights_expiration(1756841100)
				->with_reseller_status(0)
				->generate()
		],
		'expected' => '',
	],
];
