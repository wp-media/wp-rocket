<?php

return [

	'testShouldDisplayNothingWhenWhiteLabel' => [
		'rocketcdn_data' => [],

		'expected' => [
			'unit'        => null,
			'integration' => [
				'assertNotContains' => <<<HTML
<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small">
	<div class="wpr-flex">
		<section>
			<h3 class="notice-title">Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.</strong></h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button>
		</div>
	</div>
</div>
<div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta">
	<section class="wpr-rocketcdn-cta-content--no-promo">
		<h3 class="wpr-title2">RocketCDN</h3>
		<p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p>
		<div class="wpr-flex">
			<ul class="wpr-rocketcdn-features">
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">High performance Content Delivery Network (CDN) with <strong>unlimited bandwidth</strong></li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">Easy configuration: the <strong>best CDN settings</strong> are automatically applied</li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin</li>
				<li class="wpr-rocketcdn-cta-footer">
					<a href="https://wp-rocket.me/rocketcdn/" target="_blank" rel="noopener noreferrer">Learn more about RocketCDN</a>
				</li>
			</ul>
			<div class="wpr-rocketcdn-pricing">
				<h4 class="wpr-rocketcdn-pricing-current"><span class="wpr-title1">$7.99</span> / month</h4>
				<p class="wpr-rocketcdn-cta-billing-detail">Billed monthly</p>
				<button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button>
			</div>
		</div>
	</section>
	<button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta">
		<span class="screen-reader-text">Reduce this banner</span>
	</button>
</div>
HTML
				,
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
				'assertNotContains' => <<<HTML
<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small">
	<div class="wpr-flex">
		<section>
			<h3 class="notice-title">Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.</strong></h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button>
		</div>
	</div>
</div>
<div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta">
	<section class="wpr-rocketcdn-cta-content--no-promo">
		<h3 class="wpr-title2">RocketCDN</h3>
		<p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p>
		<div class="wpr-flex">
			<ul class="wpr-rocketcdn-features">
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">High performance Content Delivery Network (CDN) with <strong>unlimited bandwidth</strong></li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">Easy configuration: the <strong>best CDN settings</strong> are automatically applied</li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin</li>
				<li class="wpr-rocketcdn-cta-footer">
					<a href="https://wp-rocket.me/rocketcdn/" target="_blank" rel="noopener noreferrer">Learn more about RocketCDN</a>
				</li>
			</ul>
			<div class="wpr-rocketcdn-pricing">
				<h4 class="wpr-rocketcdn-pricing-current"><span class="wpr-title1">$7.99</span> / month</h4>
				<p class="wpr-rocketcdn-cta-billing-detail">Billed monthly</p>
				<button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button>
			</div>
		</div>
	</section>
	<button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta">
		<span class="screen-reader-text">Reduce this banner</span>
	</button>
</div>
HTML
				,
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
					'<div class="wpr-rocketcdn-cta-small',
					'<div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta">',
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
				'annual_price'             => 79.0,
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
					'regular_price'      => '',
					'current_price'      => 7.99,
				],
			],
			'integration' => <<<HTML
<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small">
	<div class="wpr-flex">
		<section>
			<h3 class="notice-title">Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.</strong></h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button>
		</div>
	</div>
</div>
<div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta">
	<section class="wpr-rocketcdn-cta-content--no-promo">
		<h3 class="wpr-title2">RocketCDN</h3>
		<p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p>
		<div class="wpr-flex">
			<ul class="wpr-rocketcdn-features">
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">High performance Content Delivery Network (CDN) with <strong>unlimited bandwidth</strong></li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">Easy configuration: the <strong>best CDN settings</strong> are automatically applied</li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin</li>
				<li class="wpr-rocketcdn-cta-footer">
					<a href="https://wp-rocket.me/rocketcdn/" target="_blank" rel="noopener noreferrer">Learn more about RocketCDN</a>
				</li>
			</ul>
			<div class="wpr-rocketcdn-pricing">
				<h4 class="wpr-rocketcdn-pricing-current">
					<span class="wpr-rocketcdn-cta-currency-minor">$</span>
					<span class="wpr-rocketcdn-cta-currency-major">7</span>
					<span class="wpr-rocketcdn-cta-currency-minor">.99</span>
				</h4>
				<p class="wpr-rocketcdn-cta-billing-detail">Billed monthly</p>
				<button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button>
			</div>
		</div>
	</section>
	<button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta">
		<span class="screen-reader-text">Reduce this banner</span>
	</button>
</div>
HTML
			,
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
				'annual_price'             => 79.0,
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
					'regular_price'      => '',
					'current_price'      => 7.99,
				],
			],
			'integration' => <<<HTML
<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small">
	<div class="wpr-flex">
		<section>
			<h3 class="notice-title">Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.</strong></h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button>
		</div>
	</div>
</div>
<div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta">
	<section class="wpr-rocketcdn-cta-content--no-promo">
		<h3 class="wpr-title2">RocketCDN</h3>
		<p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p>
		<div class="wpr-flex">
			<ul class="wpr-rocketcdn-features">
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">High performance Content Delivery Network (CDN) with <strong>unlimited bandwidth</strong></li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">Easy configuration: the <strong>best CDN settings</strong> are automatically applied</li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin</li>
				<li class="wpr-rocketcdn-cta-footer">
					<a href="https://wp-rocket.me/rocketcdn/" target="_blank" rel="noopener noreferrer">Learn more about RocketCDN</a>
				</li>
			</ul>
			<div class="wpr-rocketcdn-pricing">
				<h4 class="wpr-rocketcdn-pricing-current">
					<span class="wpr-rocketcdn-cta-currency-minor">$</span>
					<span class="wpr-rocketcdn-cta-currency-major">7</span>
					<span class="wpr-rocketcdn-cta-currency-minor">.99</span>
				</h4>
				<p class="wpr-rocketcdn-cta-billing-detail">Billed monthly</p>
				<button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button>
			</div>
		</div>
	</section>
	<button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta">
		<span class="screen-reader-text">Reduce this banner</span>
	</button>
</div>
HTML
			,
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
				'annual_price'             => 79.0,
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
					'regular_price'      => '',
					'current_price'      => 7.99,
				],
			],
			'integration' => <<<HTML
<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small">
	<div class="wpr-flex">
		<section>
			<h3 class="notice-title">Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.</strong></h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button>
		</div>
	</div>
</div>
<div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta">
	<section class="wpr-rocketcdn-cta-content--no-promo">
		<h3 class="wpr-title2">RocketCDN</h3>
		<p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p>
		<div class="wpr-flex">
			<ul class="wpr-rocketcdn-features">
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">High performance Content Delivery Network (CDN) with <strong>unlimited bandwidth</strong></li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">Easy configuration: the <strong>best CDN settings</strong> are automatically applied</li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin</li>
				<li class="wpr-rocketcdn-cta-footer">
					<a href="https://wp-rocket.me/rocketcdn/" target="_blank" rel="noopener noreferrer">Learn more about RocketCDN</a>
				</li>
			</ul>
			<div class="wpr-rocketcdn-pricing">
				<h4 class="wpr-rocketcdn-pricing-current">
					<span class="wpr-rocketcdn-cta-currency-minor">$</span>
					<span class="wpr-rocketcdn-cta-currency-major">7</span>
					<span class="wpr-rocketcdn-cta-currency-minor">.99</span>
				</h4>
				<p class="wpr-rocketcdn-cta-billing-detail">Billed monthly</p>
				<button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button>
			</div>
		</div>
	</section>
	<button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta">
		<span class="screen-reader-text">Reduce this banner</span>
	</button>
</div>
HTML
			,
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
				'annual_price'             => 79.0,
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
					'regular_price'      => '',
					'current_price'      => 7.99,
				],
			],
			'integration' => <<<HTML
<div class="wpr-rocketcdn-cta-small notice-alt notice-warning " id="wpr-rocketcdn-cta-small">
	<div class="wpr-flex">
		<section>
			<h3 class="notice-title">Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.</strong></h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button>
		</div>
	</div>
</div>
<div class="wpr-rocketcdn-cta wpr-isHidden" id="wpr-rocketcdn-cta">
	<section class="wpr-rocketcdn-cta-content--no-promo">
		<h3 class="wpr-title2">RocketCDN</h3>
		<p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p>
		<div class="wpr-flex">
			<ul class="wpr-rocketcdn-features">
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">High performance Content Delivery Network (CDN) with <strong>unlimited bandwidth</strong></li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">Easy configuration: the <strong>best CDN settings</strong> are automatically applied</li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin</li>
				<li class="wpr-rocketcdn-cta-footer">
					<a href="https://wp-rocket.me/rocketcdn/" target="_blank" rel="noopener noreferrer">Learn more about RocketCDN</a>
				</li>
			</ul>
			<div class="wpr-rocketcdn-pricing">
			<h4 class="wpr-rocketcdn-pricing-current">
					<span class="wpr-rocketcdn-cta-currency-minor">$</span>
					<span class="wpr-rocketcdn-cta-currency-major">7</span>
					<span class="wpr-rocketcdn-cta-currency-minor">.99</span>
				</h4>
				<p class="wpr-rocketcdn-cta-billing-detail">Billed monthly</p>
				<button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button>
			</div>
		</div>
	</section>
	<button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta">
		<span class="screen-reader-text">Reduce this banner</span>
	</button>
</div>
HTML
			,
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
				'discounted_price_yearly'  => 59.0,
				'discount_campaign_name'   => 'Launch',
				'end_date'                 => date( 'Y-m-d', strtotime( 'tomorrow', time() ) ),
				'monthly_price'            => 7.99,
				'annual_price'             => 79.0,
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
					'regular_price'      => 7.99,
					'current_price'      => 5.99,
				],
			],
			'integration' => '
<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small">
	<div class="wpr-flex">
		<section>
			<h3 class="notice-title">Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.</strong></h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button>
		</div>
	</div>
</div>
<div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta">
	<div class="wpr-flex wpr-rocketcdn-promo">
		<h3 class="wpr-rocketcdn-promo-title">Launch</h3>
		<p class="wpr-title2 wpr-rocketcdn-promo-date">Valid until '. date( 'Y-m-d', strtotime( 'tomorrow', time() ) ) .' only!</p>
	</div>
	<section class="wpr-rocketcdn-cta-content">
		<h3 class="wpr-title2">RocketCDN</h3>
		<p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p>
		<div class="wpr-flex">
			<ul class="wpr-rocketcdn-features">
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">High performance Content Delivery Network (CDN) with <strong>unlimited bandwidth</strong></li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">Easy configuration: the <strong>best CDN settings</strong> are automatically applied</li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin</li>
				<li class="wpr-rocketcdn-cta-footer">
					<a href="https://wp-rocket.me/rocketcdn/" target="_blank" rel="noopener noreferrer">Learn more about RocketCDN</a>
				</li>
				<li class="wpr-rocketcdn-cta-promo-footer">*$5.99/month for 12 months then $7.99/month. You can cancel your subscription at any time.</li>
			</ul>
			<div class="wpr-rocketcdn-pricing">
				<h4 class="wpr-title2 wpr-rocketcdn-pricing-regular"><del>$7.99</del></h4>
				<h4 class="wpr-rocketcdn-pricing-current">
					<span class="wpr-rocketcdn-cta-currency-minor">$</span>
					<span class="wpr-rocketcdn-cta-currency-major">5</span>
					<span class="wpr-rocketcdn-cta-currency-minor">.99*</span>
				</h4>
				<p class="wpr-rocketcdn-cta-billing-detail">Billed monthly</p>
				<button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button>
			</div>
		</div>
	</section>
	<button class="wpr-rocketcdn-cta-close" id="wpr-rocketcdn-close-cta">
		<span class="screen-reader-text">Reduce this banner</span>
	</button>
</div>'
			,
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
			'integration' => <<<HTML
<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small">
	<div class="wpr-flex">
		<section>
			<h3 class="notice-title">Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.</strong></h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button>
		</div>
	</div>
</div>
<div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta">
	<section class="wpr-rocketcdn-cta-content--no-promo">
		<h3 class="wpr-title2">RocketCDN</h3>
		<p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p>
		<div class="wpr-flex">
			<ul class="wpr-rocketcdn-features">
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">High performance Content Delivery Network (CDN) with <strong>unlimited bandwidth</strong></li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">Easy configuration: the <strong>best CDN settings</strong> are automatically applied</li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin</li>
				<li class="wpr-rocketcdn-cta-footer">
					<a href="https://wp-rocket.me/rocketcdn/" target="_blank" rel="noopener noreferrer">Learn more about RocketCDN</a>
				</li>
			</ul>
			<div class="wpr-rocketcdn-pricing">
				<p>RocketCDN is not available at the moment. Please retry later. <a href="https://docs.wp-rocket.me/article/1608-error-notices-during-the-rocketcdn-subscription-process/?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="60ddc72d9e87cb3d01249270" rel="noopener noreferrer" target="_blank">More Info</a></p>
			</div>
		</div>
	</section>
	<button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta">
		<span class="screen-reader-text">Reduce this banner</span>
	</button>
</div>
HTML
			,
		],

		'config' => [
			'rocket_rocketcdn_cta_hidden' => false,
			'is_wp_error'                 => true,
		],
	],
];
