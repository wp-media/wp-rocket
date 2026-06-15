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
				'label'           => '',
				'status_class'    => ' wpr-isInvalid',
				'status_text'     => 'No RocketCDN Pro Subscription',
				'is_active'       => false,
			],
			'integration' => '<div class="wpr-optionHeader">
					<h3 class="wpr-title2">RocketCDN</h3>
				</div>
				<div class="wpr-field wpr-field-account">
					<span class="wpr-infoAccount wpr-isInvalid">RocketCDN is unavailable on local domains and staging sites.</span>
				</div>',
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
				'label'           => '',
				'status_class'    => ' wpr-isInvalid',
				'status_text'     => 'No RocketCDN Pro Subscription',
				'is_active'       => false,
			],
			'integration' => '<div class="wpr-optionHeader">
					<h3 class="wpr-title2">RocketCDN</h3>
				</div>
				<div class="wpr-field wpr-field-account">
					<div class="wpr-flex wpr-flex--egal">
						<div>
							<span class="wpr-title3"></span>
							<span class="wpr-infoAccount wpr-isInvalid">No RocketCDN Pro Subscription</span>
						</div>
						<div>
							<a href="#page_cdn" class="wpr-button">Get RocketCDN Pro</a>
						</div>
					</div>
				</div>',
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
				'label'           => '',
				'status_class'    => ' wpr-isInvalid',
				'status_text'     => 'No RocketCDN Pro Subscription',
				'is_active'       => false,
			],
			'integration' => '<div class="wpr-optionHeader">
					<h3 class="wpr-title2">RocketCDN</h3>
				</div>
				<div class="wpr-field wpr-field-account">
					<div class="wpr-flex wpr-flex--egal">
						<div>
							<span class="wpr-title3"></span>
							<span class="wpr-infoAccount wpr-isInvalid">No RocketCDN Pro Subscription</span>
						</div>
						<div>
							<a href="#page_cdn" class="wpr-button">Get RocketCDN Pro</a>
						</div>
					</div>
				</div>',
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
				'label'           => 'Plan RocketCDN Pro',
				'status_class'    => ' wpr-isValid',
				'status_text'     => 'Next Billing Date 2020-01-01',
				'is_active'       => true,
			],
			'integration' => <<<HTML
<div class="wpr-optionHeader">
	<h3 class="wpr-title2">RocketCDN</h3>
</div>
<div class="wpr-field wpr-field-account">
	<div class="wpr-flex">
		<div>
			<span class="wpr-title3">Plan RocketCDN Pro</span>
			<span class="wpr-infoAccount wpr-isValid">Next Billing Date 2020-01-01</span>
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
