<?php

$default_customer_data = [
	'license_expiration'       => 'Unavailable',
	'license_class'            => 'wpr-isInvalid',
	'is_from_one_dot_com'      => false,
	'can_update_plugin'        => false,
	'update_blocked_reason'    => '',
	'license_expiration_label' => 'Expiration date',
	'license_type'             => 'Unknown',
];

return [
	'testShouldRegisterWithDefaultCustomerDataWhenNoUserData' => [
		'config'   => [
			'user_data' => false,
		],
		'expected' => [
			'title'            => 'Account',
			'menu_description' => 'License & billing',
			'customer_data'    => $default_customer_data,
		],
	],
];
