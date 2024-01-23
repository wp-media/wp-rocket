<?php

return [
	'shouldReturnSameWhenNotOCD' => [
		'config' => [
			'expired' => false,
			'expire_date' => strtotime( 'now + 7 days' ),
			'auto_renew' => false,
			'ocd' => true,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'value' => 1,
			'container_class' => [],
			'input_attr' => [],
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'value' => 1,
			'container_class' => [],
			'input_attr' => [],
		],
	],
	'shouldReturnSameWhenLicenseNotExpired' => [
		'config' => [
			'expired' => false,
			'expire_date' => strtotime( 'now + 7 days' ),
			'auto_renew' => false,
			'ocd' => true,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'value' => 1,
			'container_class' => [],
			'input_attr' => [],
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'value' => 1,
			'container_class' => [],
			'input_attr' => [],
		],
	],
	'shouldReturnSameWhenAutoRenewAndExpiredLessThan4Days' => [
		'config' => [
			'expired' => true,
			'expire_date' => strtotime( 'now - 3 days' ),
			'auto_renew' => true,
			'ocd' => true,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'value' => 1,
			'container_class' => [],
			'input_attr' => [],
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'value' => 1,
			'container_class' => [],
			'input_attr' => [],
		],
	],
	'shouldReturnSameWhenExpiredRecentlyAndOCDDisabled' => [
		'config' => [
			'expired' => true,
			'expire_date' => strtotime( 'now - 7 days' ),
			'auto_renew' => false,
			'ocd' => true,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'value' => 1,
			'container_class' => [],
			'input_attr' => [],
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'value' => 1,
			'container_class' => [],
			'input_attr' => [],
		],
	],
	'shouldReturnSameWhenExpiredRecentlyAndOCDDisabled' => [
		'config' => [
			'expired' => true,
			'expire_date' => strtotime( 'now - 7 days' ),
			'auto_renew' => false,
			'ocd' => false,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'value' => 1,
			'container_class' => [],
			'input_attr' => [],
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'value' => 1,
			'container_class' => [],
			'input_attr' => [],
		],
	],
	'shouldReturnDisabledWhenExpiredLonger' => [
		'config' => [
			'expired' => true,
			'expire_date' => strtotime( 'now - 20 days' ),
			'auto_renew' => false,
			'ocd' => true,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'value' => 1,
			'container_class' => [],
			'input_attr' => [],
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'value' => 0,
			'container_class' => [
				'wpr-isDisabled'
			],
			'input_attr' => [
				'disabled' => 1,
			],
		],
	],
];
