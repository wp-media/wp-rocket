<?php

return [
	'testShouldDisplayNothingWhenNotLiveSite'             => [
		'config'   => [
			'is_live_site' => false,
		],
		'expected' => false,
	],

	'testShouldNotDisplayNoticeWhenPlanIsPaid'            => [
		'config'   => [
			'subscription_status'     => 'running',
			'plan_type'               => 'paid',
			'has_active_subscription' => true,
			'is_paid'                 => true,
		],
		'expected' => false,
	],

	'testShouldDisplayBigCTANoPromoWhenDefault'           => [
		'config'   => [
			'cta_hidden' => false,
			'pricing'    => [
				'is_discount_active'       => false,
				'discounted_price_monthly' => 5.99,
				'discounted_price_yearly'  => 59.0,
				'discount_campaign_name'   => '',
				'end_date'                 => '2019-11-03',
				'monthly_price'            => 7.99,
				'annual_price'             => 79.99,
			],
		],
		'expected' => [
			'container_class'    => '',
			'promotion_campaign' => '',
			'nopromo_variant'    => '--no-promo',
		],
	],

	'testShouldDisplayBigCTANoPromoWhenDiscountNotActive' => [
		'config'   => [
			'cta_hidden' => false,
			'pricing'    => [
				'is_discount_active'       => false,
				'discounted_price_monthly' => 5.99,
				'discounted_price_yearly'  => 59.0,
				'discount_campaign_name'   => 'halloween',
				'end_date'                 => '2022-11-03',
				'monthly_price'            => 7.99,
				'annual_price'             => 79.99,
			],
		],
		'expected' => [
			'container_class'    => '',
			'promotion_campaign' => '',
			'nopromo_variant'    => '--no-promo',
		],
	],

	'testShouldDisplayBigCTANoPromoWhenAfterEndDate'      => [
		'config'   => [
			'cta_hidden' => false,
			'pricing'    => [
				'is_discount_active'       => true,
				'discounted_price_monthly' => 5.99,
				'discounted_price_yearly'  => 59.0,
				'discount_campaign_name'   => 'halloween',
				'end_date'                 => '2019-11-03',
				'monthly_price'            => 7.99,
				'annual_price'             => 79.99,
			],
		],
		'expected' => [
			'container_class'    => '',
			'promotion_campaign' => '',
			'nopromo_variant'    => '--no-promo',
		],
	],

	'testShouldDisplaySmallCTAWhenBigHidden'              => [
		'config'   => [
			'cta_hidden' => true,
			'pricing'    => [
				'is_discount_active'       => false,
				'discounted_price_monthly' => 5.99,
				'discounted_price_yearly'  => 59.0,
				'discount_campaign_name'   => '',
				'end_date'                 => '2019-11-03',
				'monthly_price'            => 7.99,
				'annual_price'             => 79.99,
			],
		],
		'expected' => [
			'container_class'    => 'wpr-isHidden',
			'promotion_campaign' => '',
			'nopromo_variant'    => '--no-promo',
		],
	],

	'testShouldDisplayBigCTAPromoWhenPromoActive'         => [
		'config'   => [
			'cta_hidden' => false,
			'pricing'    => [
				'is_discount_active'       => true,
				'discounted_price_monthly' => 5.99,
				'discounted_price_yearly'  => 59.99,
				'discount_campaign_name'   => 'Launch',
				'end_date'                 => date( 'Y-m-d', strtotime( 'tomorrow' ) ),
				'monthly_price'            => 7.99,
				'annual_price'             => 79.99,
			],
		],
		'expected' => [
			'container_class'    => '',
			'promotion_campaign' => 'Launch',
			'nopromo_variant'    => '',
		],
	],

	'testShouldDisplayErrorMessageWhenPricingAPINotAvailable' => [
		'config'   => [
			'cta_hidden'       => false,
			'pricing_is_error' => true,
		],
		'expected' => [
			'container_class' => '',
			'nopromo_variant' => '--no-promo',
			'error'           => true,
		],
	],
];
