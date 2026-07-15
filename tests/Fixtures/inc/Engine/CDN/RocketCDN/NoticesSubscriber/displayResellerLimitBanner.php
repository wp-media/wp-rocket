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
			'heading'     => 'Nice work!  You\'re using RocketCDN on 3 key pages.',
			'description' => 'This is currently the free limit we set for our users. Thank you for using RocketCDN',
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
			'heading'     => 'Nice work!  You\'re using RocketCDN on 3 key pages.',
			'description' => 'This is currently the free limit we set for our users. Thank you for using RocketCDN',
			'is_hidden'   => false,
		],
	],

];
