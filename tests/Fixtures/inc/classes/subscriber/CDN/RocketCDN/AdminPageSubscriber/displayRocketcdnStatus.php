<?php

return [
	'vfs_dir'   => 'public/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'plugins' => [
				'wp-rocket' => [
					'views' => [
						'settings' => [
							'rocketcdn' => [
								'dashboard-status.php' => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/settings/rocketcdn/dashboard-status.php' ),
							],
						],
					],
				],
			],
		],
	],
	'test_data' => [
		'testShouldDisplayNothingWhenNotLiveSite' => [
			// Subscription data.
			[
				'is_active'                     => false,
				'subscription_status'           => 'cancelled',
				'subscription_next_date_update' => '2020-01-01',
			],
			// Expected.
			[
				'unit'        => [
					'is_live_site'    => false,
					'container_class' => ' wpr-flex--egal',
					'label'           => '',
					'status_class'    => ' wpr-isInvalid',
					'status_text'     => 'No Subscription',
					'is_active'       => false,
				],
				'integration' => '<div class="wpr-optionHeader">
					<h3 class="wpr-title2">RocketCDN</h3>
				</div>
				<div class="wpr-field wpr-field-account">
					<span class="wpr-infoAccount wpr-isInvalid">RocketCDN is unavailable on local domains and staging sites.</span>
				</div>',
			],
			// Configuration.
			[
				'home_url'   => 'http://localhost',
				'get_option' => '',
				'date_i18n'  => '',
			],
		],

		'testShouldOutputNoSubscriptionWhenInactive' => [
			// Subscription data.
			[
				'is_active'                     => false,
				'subscription_status'           => 'cancelled',
				'subscription_next_date_update' => '2020-01-01',
			],
			// Expected.
			[
				'unit'        => [
					'is_live_site'    => true,
					'container_class' => ' wpr-flex--egal',
					'label'           => '',
					'status_class'    => ' wpr-isInvalid',
					'status_text'     => 'No Subscription',
					'is_active'       => false,
				],
				'integration' => '<div class="wpr-optionHeader">
					<h3 class="wpr-title2">RocketCDN</h3>
				</div>
				<div class="wpr-field wpr-field-account">
					<div class="wpr-flex wpr-flex--egal">
						<div>
							<span class="wpr-title3"></span>
							<span class="wpr-infoAccount wpr-isInvalid">No Subscription</span>
						</div>
						<div>
							<a href="#page_cdn" class="wpr-button">Get RocketCDN</a>
						</div>
					</div>
				</div>',
			],
			// Configuration.
			[
				'home_url'   => 'http://example.org',
				'get_option' => '',
				'date_i18n'  => '',
			],
		],

		'testShouldOutputSubscriptionDataWhenActive' => [
			// Subscription data.
			[
				'is_active'                     => true,
				'subscription_status'           => 'running',
				'subscription_next_date_update' => '2020-01-01',
			],
			// Expected.
			[
				'unit'        => [
					'is_live_site'    => true,
					'container_class' => '',
					'label'           => 'Next Billing Date',
					'status_class'    => ' wpr-isValid',
					'status_text'     => '2020-01-01',
					'is_active'       => true,
				],
				'integration' => '<div class="wpr-optionHeader">
					<h3 class="wpr-title2">RocketCDN</h3>
				</div>
				<div class="wpr-field wpr-field-account">
					<div class="wpr-flex">
						<div>
							<span class="wpr-title3">Next Billing Date</span>
							<span class="wpr-infoAccount wpr-isValid">2020-01-01</span>
						</div>
					</div>
				</div>',
			],
			// Configuration.
			[
				'home_url'   => 'http://example.org',
				'get_option' => 'Y-m-d',
				'date_i18n'  => '2020-01-01',
			],
		],
	],
];
