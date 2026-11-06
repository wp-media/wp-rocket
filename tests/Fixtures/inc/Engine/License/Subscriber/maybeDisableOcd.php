<?php

return [
	'shouldReturnSameWhenNotOCD' => [
		'config' => [
			'transient' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now + 7 days' ),
				'is_auto_renew' => false,
			] ) ),
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
			'transient' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now + 7 days' ),
				'is_auto_renew' => false,
			] ) ),
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
	'shouldReturnSameWhenAutoRenewAndExpiredSinceLessThan4Days' => [
		'config' => [
			'transient' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 3 days' ),
				'is_auto_renew' => true,
			] ) ),
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
	'shouldReturnSameWhenExpiredRecently' => [
		'config' => [
			'transient' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 7 days' ),
				'is_auto_renew' => false,
			] ) ),
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
			'transient' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 7 days' ),
				'is_auto_renew' => false,
			] ) ),
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
			'transient' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 20 days' ),
				'is_auto_renew' => false,
			] ) ),
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
