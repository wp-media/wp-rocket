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
	'testShouldOutputAddonLicenseStatusWhenActive' => [
		'config' => [
			'is_live_site' => true,
			'date_format' => 'F j, Y',
			'customer_data' => (new UserDataGenerator())
				->with_rocket_insights_active_sku('perf-monitor-advanced')
				->with_rocket_insights_expiration(1756841100)
				->with_reseller_status(0)
				->generate()
		],
		'expected' => <<<HTML
		<div>
			<span class="wpr-title3">Next Billing Date</span>
			<span class="wpr-infoAccount wpr-isValid">September 2, 2025</span>
		</div>
HTML
	],
];
