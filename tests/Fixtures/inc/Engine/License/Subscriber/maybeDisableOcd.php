<?php

return [
	'shouldReturnSameWhenNotOCD' => [
		'config' => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'now + 7 days' ),
		] ) ),
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
		'config' => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'now + 7 days' ),
		] ) ),
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
		'config' => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'now - 7 days' ),
		] ) ),
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
		'config' => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'now - 20 days' ),
		] ) ),
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
