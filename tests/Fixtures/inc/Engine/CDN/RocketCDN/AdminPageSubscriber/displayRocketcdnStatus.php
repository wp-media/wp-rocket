<?php

return [

	'testShouldReturnEarlyWhenWhiteLabelAccount' => [
		'rocketcdn_status' => [
			'is_active'                     => false,
			'subscription_status'           => 'cancelled',
			'subscription_next_date_update' => '2020-01-01',
			'plan_type'                     => 'free',
		],
		'expected'         => [
			'unit'        => [],
			'integration' => '',
		],

		'config' => [
			'white_label' => true,
			'is_live_site' => false,
			'home_url'    => 'http://localhost',
			'get_option'  => '',
			'date_i18n'   => '',
		],
	],

	'testShouldReturnEarlyWhenResellerAccount' => [
		'rocketcdn_status' => [
			'is_active'                     => false,
			'subscription_status'           => 'cancelled',
			'subscription_next_date_update' => '2020-01-01',
			'plan_type'                     => 'free',
		],
		'expected'         => [
			'unit'        => [],
			'integration' => '',
		],

		'config' => [
			'is_reseller' => true,
			'is_live_site' => false,
			'home_url'    => 'http://localhost',
			'get_option'  => '',
			'date_i18n'   => '',
		],
	],

	'testShouldDisplayNothingWhenNotLiveSite' => [
		'rocketcdn_status' => [
			'is_active'                     => false,
			'subscription_status'           => 'cancelled',
			'subscription_next_date_update' => '2020-01-01',
			'plan_type'                     => 'free',
		],
		'expected' => [
			'unit'        => [
				'is_live_site'    => false,
				'container_class' => ' wpr-flex--egal',
				'is_active'       => false,
				'items'           => [
					[
						'label' => '',
						'value' => 'No RocketCDN Pro Subscription',
						'class' => ' wpr-isInvalid',
					],
				],
			],
			'integration' => <<<HTML
<div class="wpr-account-section">
	<div class="wpr-account-header">
		<h3 class="wpr-account-title">
			RocketCDN Account
		</h3>
	</div>
	<div class="wpr-account-card">
		<span class="wpr-account-value wpr-isInvalid">RocketCDN is unavailable on local domains and staging sites.</span>
	</div>
</div>
HTML
			,
		],

		'config' => [
			'is_live_site' => false,
			'home_url'   => 'http://localhost',
			'get_option' => '',
			'date_i18n'  => '',
		],
	],

	'testShouldRenderNoSubscriptionHTMLWhenCancelled' => [
		'rocketcdn_status' => [
			'is_active'                     => false,
			'subscription_status'           => 'cancelled',
			'subscription_next_date_update' => '2020-01-01',
			'plan_type'                     => 'free',
		],

		'expected' => [
			'unit'        => [
				'is_live_site'    => true,
				'container_class' => ' wpr-flex--egal',
				'is_active'       => false,
				'items'           => [
					[
						'label' => '',
						'value' => 'No RocketCDN Pro Subscription',
						'class' => ' wpr-isInvalid',
					],
				],
			],
			'integration' => <<<HTML
<div class="wpr-account-section">
	<div class="wpr-account-header">
		<h3 class="wpr-account-title">
			RocketCDN Account
		</h3>
	</div>
	<div class="wpr-account-card">
		<div class="wpr-account-row wpr-account-row--split wpr-flex wpr-flex--egal">
			<div class="wpr-account-plan wpr-dashboard-plans">
				<div class="wpr-account-planItem">
					<span class="wpr-account-planLabel">License</span>
					<span class="wpr-account-planValue wpr-isInvalid">No RocketCDN Pro Subscription</span>
				</div>
			</div>
			<div class="wpr-account-actions">
				<a href="#page_cdn" class="wpr-account-button">Get RocketCDN Pro</a>
			</div>
		</div>
	</div>
</div>
HTML
			,
		],

		'config' => [
			'is_live_site' => true,
			'home_url'   => 'http://example.org',
			'get_option' => '',
			'date_i18n'  => '',
		],
	],

	'testShouldRenderNoSubscriptionHTMLWhenFreeRunningPlan' => [
		'rocketcdn_status' => [
			'is_active'                     => true,
			'subscription_status'           => 'running',
			'subscription_next_date_update' => '2020-01-01',
			'plan_type'                     => 'free',
		],

		'expected' => [
			'unit'        => [
				'is_live_site'    => true,
				'container_class' => ' wpr-flex--egal',
				'is_active'       => false,
				'items'           => [
					[
						'label' => '',
						'value' => 'No RocketCDN Pro Subscription',
						'class' => ' wpr-isInvalid',
					],
				],
			],
			'integration' => <<<HTML
<div class="wpr-account-section">
	<div class="wpr-account-header">
		<h3 class="wpr-account-title">
			RocketCDN Account
		</h3>
	</div>
	<div class="wpr-account-card">
		<div class="wpr-account-row wpr-account-row--split wpr-flex wpr-flex--egal">
			<div class="wpr-account-plan wpr-dashboard-plans">
				<div class="wpr-account-planItem">
					<span class="wpr-account-planLabel">License</span>
					<span class="wpr-account-planValue wpr-isInvalid">No RocketCDN Pro Subscription</span>
				</div>
			</div>
			<div class="wpr-account-actions">
				<a href="#page_cdn" class="wpr-account-button">Get RocketCDN Pro</a>
			</div>
		</div>
	</div>
</div>
HTML
			,
		],

		'config' => [
			'is_live_site' => true,
			'home_url'   => 'http://example.org',
			'get_option' => '',
			'date_i18n'  => '',
		],
	],

	'testShouldOutputSubscriptionDataWhenPaidPlanActive' => [
		'rocketcdn_status' => [
			'is_active'                     => true,
			'subscription_status'           => 'running',
			'subscription_next_date_update' => '2020-01-01',
			'plan_type'                     => 'paid',
		],

		'expected' => [
			'unit'        => [
				'is_live_site'    => true,
				'container_class' => '',
				'is_active'       => true,
				'items'           => [
					[
						'label' => 'Plan',
						'value' => 'RocketCDN Pro',
						'class' => ' wpr-isValid wpr-no-icon',
					],
					[
						'label' => 'Next Billing Date',
						'value' => '2020-01-01',
						'class' => ' wpr-isValid',
					],
				],
			],
			'integration' => <<<HTML
<div class="wpr-account-section">
	<div class="wpr-account-header">
		<h3 class="wpr-account-title">
			RocketCDN Account
		</h3>
	</div>
	<div class="wpr-account-card">
		<div class="wpr-account-row wpr-account-row--split wpr-flex">
			<div class="wpr-account-plan wpr-dashboard-plans">
				<div class="wpr-account-planItem">
					<span class="wpr-account-planLabel">Plan</span>
					<span class="wpr-account-planValue wpr-isValid wpr-no-icon">RocketCDN Pro</span>
				</div>
				<div class="wpr-account-planItem">
					<span class="wpr-account-planLabel">Next Billing Date</span>
					<span class="wpr-account-planValue wpr-isValid">2020-01-01</span>
				</div>
			</div>
		</div>
	</div>
</div>
HTML
			,
		],

		'config' => [
			'is_live_site' => true,
			'home_url'   => 'http://example.org',
			'get_option' => 'Y-m-d',
			'date_i18n'  => '2020-01-01',
		],
	],
];
