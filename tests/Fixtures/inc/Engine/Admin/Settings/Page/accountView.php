<?php

$customer_data = [
	'license_expiration'       => '2030-01-01',
	'license_class'            => 'wpr-isValid',
	'is_from_one_dot_com'      => false,
	'can_update_plugin'        => true,
	'update_blocked_reason'    => '',
	'license_expiration_label' => 'Expiration date',
	'license_type'             => 'Plus',
];

return [
	'testShouldRenderLicenseCardWhenNotWhiteLabel' => [
		'config'   => [
			'white_label'   => false,
			'customer_data' => $customer_data,
		],
		'expected' => [
			'contains'     => [ 'wpr-account-card--license', 'Plus', 'Expiration date', 'wpr-plugin-updates-data', 'id="license-hook"', 'id="after-hook"' ],
			'not_contains' => [ 'One.com', 'wpr-isInvalid' ],
		],
	],
	'testShouldHideLicenseCardAndLicenseHookWhenWhiteLabel' => [
		'config'   => [
			'white_label'   => true,
			'customer_data' => $customer_data,
		],
		'expected' => [
			'contains'     => [ 'id="after-hook"' ],
			'not_contains' => [ 'wpr-account-card--license', 'id="license-hook"' ],
		],
	],
	'testShouldRenderTrialLabel' => [
		'config'   => [
			'white_label'   => false,
			'customer_data' => array_merge( $customer_data, [ 'license_expiration_label' => 'Trial end date' ] ),
		],
		'expected' => [
			'contains'     => [ 'Trial end date' ],
			'not_contains' => [],
		],
	],
	'testShouldRenderOneDotComPartner' => [
		'config'   => [
			'white_label'   => false,
			'customer_data' => array_merge( $customer_data, [ 'is_from_one_dot_com' => true ] ),
		],
		'expected' => [
			'contains'     => [ 'One.com', 'one-com-logo.svg' ],
			'not_contains' => [],
		],
	],
	'testShouldRenderBlockedReasonWhenPluginUpdatesNotAllowed' => [
		'config'   => [
			'white_label'   => false,
			'customer_data' => array_merge( $customer_data, [ 'can_update_plugin' => false, 'update_blocked_reason' => 'License expired' ] ),
		],
		'expected' => [
			'contains'     => [ 'License expired', 'wpr-isInvalid' ],
			'not_contains' => [ 'wpr-icon-check' ],
		],
	],
];
