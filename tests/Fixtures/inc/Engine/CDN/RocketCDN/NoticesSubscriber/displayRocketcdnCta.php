<?php

return [
	'testShouldDisplayNothingWhenWhiteLabel' => [
		'rocketcdn_data' => [],

		'expected' => [
			'unit'        => null,
			'integration' => [
				'assertNotContains' => 'id="wpr-rocketcdn-cta"',
			],
		],

		'config' => [
			'white_label' => true,
		],
	],

	'testShouldDisplayNothingWhenNotLiveSite' => [
		'rocketcdn_data' => [],

		'expected' => [
			'unit'        => null,
			'integration' => [
				'assertNotContains' => 'id="wpr-rocketcdn-cta"',
			],
		],

		'config' => [
			'home_url'  => 'http://localhost',
			'live_site' => false,
		],
	],

	'testShouldNotDisplayNoticeWhenActive' => [

		'rocketcdn_data' => [
			'rocketcdn_status'  => [
				'subscription_status' => 'running',
			],
			'rocketcdn_pricing' => [],
		],

		'expected' => [
			'unit'        => [
				'cta-small' => [],
				'cta-big'   => [],
			],
			'integration' => [
				'not_expected' => [
					'id="wpr-rocketcdn-cta"',
				],
			],
		],

		'config' => [],
	],

	'testShouldDisplayBigCTANoPromoWhenDefault' => [

		'rocketcdn_data' => [
			'rocketcdn_status'  => [
				'subscription_status' => 'cancelled',
			],
			'rocketcdn_pricing' => [
				'is_discount_active'       => false,
				'discounted_price_monthly' => 5.99,
				'discounted_price_yearly'  => 59.0,
				'discount_campaign_name'   => '',
				'end_date'                 => '2019-11-03',
				'monthly_price'            => 7.99,
				'annual_price'             => 79.99,
			],
			'cta_data' => [
				'cta_heading'     => '<strong>Want full-site Content Delivery coverage?</strong> Extend RocketCDN to all your pages with unlimited bandwidth.',
				'cta_description' => '',
			],
		],

		'expected' => [
			'unit'        => [
				'cta-small' => [
					'container_class' => 'wpr-isHidden',
				],
				'cta-big'   => [
					'container_class'    => '',
					'promotion_campaign' => '',
					'promotion_end_date' => '',
					'nopromo_variant'    => '--no-promo',
					'regular_price_monthly'      => '',
					'regular_price_annual'       => '',
					'current_price_monthly'      => 7.99,
					'current_price_annual'      => 79.99,
				],
			],
			'integration' => [
				'<div class="wpr-rocketcdn-cta wpr-rocketcdn-cta--collapsed " id="wpr-rocketcdn-cta">',
				'<div class="wpr-rocketcdn-cta-toggle" role="button" tabindex="0" aria-controls="wpr-rocketcdn-cta-expandable" aria-expanded="true">',
				'<strong>Want full-site Content Delivery coverage?</strong> Extend RocketCDN to all your pages with unlimited bandwidth.',
			],
		],

		'config' => [
			'rocket_rocketcdn_cta_hidden' => false,
			'is_wp_error'                 => false,
		],
	],

	'testShouldDisplayBigCTANoPromoWhenDiscountNotActive' => [

		'rocketcdn_data' => [
			'rocketcdn_status'  => [
				'subscription_status' => 'cancelled',
			],
			'rocketcdn_pricing' => [
				'is_discount_active'       => false,
				'discounted_price_monthly' => 5.99,
				'discounted_price_yearly'  => 59.0,
				'discount_campaign_name'   => 'halloween',
				'end_date'                 => '2022-11-03',
				'monthly_price'            => 7.99,
				'annual_price'             => 79.99,
			],
			'cta_data' => [
				'cta_heading'     => '<strong>Want full-site Content Delivery coverage?</strong> Extend RocketCDN to all your pages with unlimited bandwidth.',
				'cta_description' => '',
			],
		],

		'expected' => [
			'unit'        => [
				'cta-small' => [
					'container_class' => 'wpr-isHidden',
				],
				'cta-big'   => [
					'container_class'    => '',
					'promotion_campaign' => '',
					'promotion_end_date' => '',
					'nopromo_variant'    => '--no-promo',
					'regular_price_monthly'      => '',
					'regular_price_annual'       => '',
					'current_price_monthly'      => 7.99,
					'current_price_annual'      => 6.67,
				],
			],
			'integration' => [
				'<div class="wpr-rocketcdn-cta wpr-rocketcdn-cta--collapsed " id="wpr-rocketcdn-cta">',
				'<div class="wpr-rocketcdn-cta-toggle" role="button" tabindex="0" aria-controls="wpr-rocketcdn-cta-expandable" aria-expanded="true">',
				'<strong>Want full-site Content Delivery coverage?</strong> Extend RocketCDN to all your pages with unlimited bandwidth.',
			],
		],

		'config' => [
			'rocket_rocketcdn_cta_hidden' => false,
			'is_wp_error'                 => false,
		],
	],

	'testShouldDisplayBigCTANoPromoWhenAfterEndDate' => [

		'rocketcdn_data' => [
			'rocketcdn_status'  => [
				'subscription_status' => 'cancelled',
			],
			'rocketcdn_pricing' => [
				'is_discount_active'       => true,
				'discounted_price_monthly' => 5.99,
				'discounted_price_yearly'  => 59.0,
				'discount_campaign_name'   => 'halloween',
				'end_date'                 => '2019-11-03',
				'monthly_price'            => 7.99,
				'annual_price'             => 79.99,
			],
			'cta_data' => [
				'cta_heading'     => '<strong>Want full-site Content Delivery coverage?</strong> Extend RocketCDN to all your pages with unlimited bandwidth.',
				'cta_description' => '',
			],
		],

		'expected' => [
			'unit'        => [
				'cta-small' => [
					'container_class' => 'wpr-isHidden',
				],
				'cta-big'   => [
					'container_class'    => '',
					'promotion_campaign' => '',
					'promotion_end_date' => '',
					'nopromo_variant'    => '--no-promo',
					'regular_price_monthly'      => '',
					'regular_price_annual'       => '',
					'current_price_monthly'      => 7.99,
					'current_price_annual'      => 6.67,
				],
			],
			'integration' => [
				'<div class="wpr-rocketcdn-cta wpr-rocketcdn-cta--collapsed " id="wpr-rocketcdn-cta">',
				'<div class="wpr-rocketcdn-cta-toggle" role="button" tabindex="0" aria-controls="wpr-rocketcdn-cta-expandable" aria-expanded="true">',
				'<strong>Want full-site Content Delivery coverage?</strong> Extend RocketCDN to all your pages with unlimited bandwidth.',
			],
		],

		'config' => [
			'rocket_rocketcdn_cta_hidden' => false,
			'is_wp_error'                 => false,
		],
	],

	'testShouldDisplaySmallCTAWhenBigHidden' => [

		'rocketcdn_data' => [
			'rocketcdn_status'  => [
				'subscription_status' => 'cancelled',
			],
			'rocketcdn_pricing' => [
				'is_discount_active'       => false,
				'discounted_price_monthly' => 5.99,
				'discounted_price_yearly'  => 59.0,
				'discount_campaign_name'   => '',
				'end_date'                 => '2019-11-03',
				'monthly_price'            => 7.99,
				'annual_price'             => 79.99,
			],
			'cta_data' => [
				'cta_heading'     => '<strong>Want full-site Content Delivery coverage?</strong> Extend RocketCDN to all your pages with unlimited bandwidth.',
				'cta_description' => '',
			],
		],

		'expected' => [
			'unit'        => [
				'cta-small' => [
					'container_class' => '',
				],
				'cta-big'   => [
					'container_class'    => 'wpr-isHidden',
					'promotion_campaign' => '',
					'promotion_end_date' => '',
					'nopromo_variant'    => '--no-promo',
					'regular_price_monthly'      => '',
					'regular_price_annual'       => '',
					'current_price_monthly'      => 7.99,
					'current_price_annual'      => 6.67,
				],
			],
			'integration' => [
				'<div class="wpr-rocketcdn-cta wpr-rocketcdn-cta--collapsed wpr-isHidden" id="wpr-rocketcdn-cta">',
				'<div class="wpr-rocketcdn-cta-toggle" role="button" tabindex="0" aria-controls="wpr-rocketcdn-cta-expandable" aria-expanded="true">',
			],
		],

		'config' => [
			'rocket_rocketcdn_cta_hidden' => true,
			'is_wp_error'                 => false,
		],
	],

	'testShouldDisplayBigCTAPromoWhenPromoActive' => [
		'rocketcdn_data' => [
			'rocketcdn_status'  => [
				'subscription_status' => 'cancelled',
			],
			'rocketcdn_pricing' => [
				'is_discount_active'       => true,
				'discounted_price_monthly' => 5.99,
				'discounted_price_yearly'  => 59.99,
				'discount_campaign_name'   => 'Launch',
				'end_date'                 => date( 'Y-m-d', strtotime( 'tomorrow', time() ) ),
				'monthly_price'            => 7.99,
				'annual_price'             => 79.99,
			],
			'cta_data' => [
				'cta_heading'     => '<strong>Want full-site Content Delivery coverage?</strong> Extend RocketCDN to all your pages with unlimited bandwidth.',
				'cta_description' => '',
			],
		],

		'expected' => [
			'unit'        => [
				'cta-small' => [
					'container_class' => 'wpr-isHidden',
				],
				'cta-big'   => [
					'container_class'    => '',
					'promotion_campaign' => 'Launch',
					'promotion_end_date' => date( 'Y-m-d', strtotime( 'tomorrow', time() ) ),
					'nopromo_variant'    => '',
					'regular_price_monthly'      => 7.99,
					'regular_price_annual'       => 6.67,
					'current_price_monthly'      => 5.99,
					'current_price_annual'      => 5.00,
				],
			],
			'integration' => [
				'<div class="wpr-rocketcdn-cta wpr-rocketcdn-cta--collapsed " id="wpr-rocketcdn-cta">',
				'<h3 class="wpr-rocketcdn-promo-title">',
				'Launch',
				'Valid until ' . date( 'Y-m-d', strtotime( 'tomorrow', time() ) ) . ' only!',
			],
		],

		'config' => [
			'rocket_rocketcdn_cta_hidden' => false,
			'is_wp_error'                 => false,
		],
	],

	'testShouldDisplayErrorMessageWhenPricingAPINotAvailable' => [

		'rocketcdn_data' => [
			'rocketcdn_status'  => [
				'subscription_status' => 'cancelled',
			],
			'rocketcdn_pricing' => 'RocketCDN is not available at the moment. Please retry later.',
			'cta_data' => [
				'cta_heading'     => '<strong>Want full-site Content Delivery coverage?</strong> Extend RocketCDN to all your pages with unlimited bandwidth.',
				'cta_description' => '',
			],
		],

		'expected' => [
			'unit'        => [
				'cta-small' => [
					'container_class' => 'wpr-isHidden',
				],
				'cta-big'   => [
					'container_class' => '',
					'nopromo_variant' => '--no-promo',
					'error'           => true,
					'message'         => 'RocketCDN is not available at the moment. Please retry later. <a href="" data-beacon-article="" rel="noopener noreferrer" target="_blank">More Info</a>',
				],
			],
			'integration' => [
				'<div class="wpr-rocketcdn-cta wpr-rocketcdn-cta--collapsed " id="wpr-rocketcdn-cta">',
				'RocketCDN is not available at the moment. Please retry later.',
				'More Info',
			],
		],

		'config' => [
			'rocket_rocketcdn_cta_hidden' => false,
			'is_wp_error'                 => true,
		],
	],
];
