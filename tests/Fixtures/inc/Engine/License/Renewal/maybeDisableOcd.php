<?php

return [
	'shouldReturnSameWhenNotOCD' => [
		'config' => [
			'auto_renew' => false,
			'expired' => false,
			'expire_date' => strtotime( 'now + 7 days' ),
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
	'shouldReturnSameWhenAutoRenew' => [
		'config' => [
			'auto_renew' => true,
			'expired' => false,
			'expire_date' => strtotime( 'now + 7 days' ),
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
			'auto_renew' => false,
			'expired' => false,
			'expire_date' => strtotime( 'now + 7 days' ),
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
	'shouldReturnSameWhenExpiredRecently' => [
		'config' => [
			'auto_renew' => false,
			'expired' => true,
			'expire_date' => strtotime( 'now - 7 days' ),
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
			'auto_renew' => false,
			'expired' => true,
			'expire_date' => strtotime( 'now - 20 days' ),
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
