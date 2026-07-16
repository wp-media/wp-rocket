<?php

return [

	'testShouldReturnEarlyWhenNotResellerAccount' => [
		'config'   => [
			'is_reseller' => false,
			'cta_data'    => [
				'limit_reached' => true,
			],
		],
		'expected' => null,
	],

	'testShouldRenderHiddenBannerWhenLimitNotReached' => [
		'config'   => [
			'is_reseller' => true,
			'cta_data'    => [
				'limit_reached' => false,
			],
		],
		'expected' => [
			'template'    => 'cta-reseller-limit',
			'heading'     => 'Nice work!  You\'re using RocketCDN on all available pages.',
			'description' => 'RocketCDN covers up to 3 pages, and you\'re all set.',
			'is_hidden'   => true,
		],
	],

	'testShouldRenderBannerWhenResellerAndLimitReached' => [
		'config'   => [
			'is_reseller' => true,
			'cta_data'    => [
				'limit_reached' => true,
			],
		],
		'expected' => [
			'template'    => 'cta-reseller-limit',
			'heading'     => 'Nice work!  You\'re using RocketCDN on all available pages.',
			'description' => 'RocketCDN covers up to 3 pages, and you\'re all set.',
			'is_hidden'   => false,
		],
	],

];
