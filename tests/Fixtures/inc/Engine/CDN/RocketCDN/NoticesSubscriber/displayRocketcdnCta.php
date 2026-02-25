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
			<h3 class="notice-title">Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.</h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button>
		</div>
	</div>
</div>
<div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta">
	<section class="wpr-rocketcdn-cta-content--no-promo">
		<div class="wpr-flex">
			<div class="wpr-rocketcdn-card-left">
				<div class="wpr-rocketcdn-header">
					<h2 class="wpr-rocketcdn-header--title">Propel your Content at the Speed of Light!</h2>
					<p class="wpr-rocketcdn-header--subtitle">RocketCDN delivers your content from servers around the world for a faster website.</p>
				</div>
				<ul class="wpr-rocketcdn-features">
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">Unlimited Performance</h3>
							<p class="wpr-rocketcdn-feature--description">Experience blazing-fast content delivery through 120 edge locations with unlimited bandwidth.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">Pre-Tuned for Speed</h3>
							<p class="wpr-rocketcdn-feature--description">Enjoy pre-configured settings tailored for maximum speed and performance.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">Effortless Setup</h3>
							<p class="wpr-rocketcdn-feature--description">Benefit from automatic configuration of the CDN option in WP Rocket, making setup effortless.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-cta-footer">
						<div class="wpr-rocketcdn-cta-footer--cancel-notice">
							<span>You can cancel anytime!</span>
						</div>
					</li>
				</ul>
			</div>
			<div class="wpr-rocketcdn-pricing ">
				<div class="wpr-rocketcdn-pricing--logo">
					<img src="http://example.org/wp-content/plugins/wp-rocket/assets/img/rocketcdn-logo.svg" alt="Rocket Logo" class="wpr-rocketcdn-pricing--logo-icon">
					<img src="http://example.org/wp-content/plugins/wp-rocket/assets/img/rocketcdn-text.svg" alt="RocketCDN Text" class="wpr-rocketcdn-pricing--logo-text">
				</div>
				<div class="wpr-rocketcdn-pricing--content">
					<div class="wpr-rocketcdn-pricing--toggle">
						<label class="wpr-rocketcdn-toggle">
							<input type="checkbox" class="wpr-rocketcdn-toggle--input">
							<span class="wpr-rocketcdn-toggle--slider"></span>

							<span class="wpr-rocketcdn-pricing--toggle-label wpr-rocketcdn-pricing--toggle-label--inactive">Monthly</span>
							<span class="wpr-rocketcdn-pricing--toggle-label wpr-rocketcdn-pricing--toggle-label--active">Yearly</span>

							<div class="wpr-rocketcdn-pricing--badge">2 Months Free!</div>
						</label>
					</div>
					<div class="wpr-rocketcdn-pricing--price-container">
						<div class="wpr-rocketcdn-pricing--price">
							<span class="wpr-rocketcdn-pricing--currency">$</span>
							<span class="wpr-rocketcdn-pricing--amount wpr-rocketcdn-pricing--monthly">7</span>
							<span class="wpr-rocketcdn-pricing--cents wpr-rocketcdn-pricing--monthly">.99</span>

							<span class="wpr-rocketcdn-pricing--amount wpr-rocketcdn-pricing--annual wpr-isHidden">79</span>
							<span class="wpr-rocketcdn-pricing--cents wpr-rocketcdn-pricing--annual wpr-isHidden">.99</span>
						</div>
						<div class="wpr-rocketcdn-pricing--billing">
							<span class="wpr-rocketcdn-pricing--billing-period">per month, billed yearly</span>
							<span class="wpr-rocketcdn-pricing--billing-vat">(excl. VAT)</span>
						</div>
					</div>
					<button class="wpr-rocketcdn-pricing--cta wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button>
				</div>
			</div>
			<button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta">
				<span class="screen-reader-text">Reduce this banner</span>
			</button>
		</div>
	</section>
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
			<h3 class="notice-title">Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.</h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button>
		</div>
	</div>
</div>
<div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta">
	<section class="wpr-rocketcdn-cta-content--no-promo">
		<div class="wpr-flex">
			<div class="wpr-rocketcdn-card-left">
				<div class="wpr-rocketcdn-header">
					<h2 class="wpr-rocketcdn-header--title">Propel your Content at the Speed of Light!</h2>
					<p class="wpr-rocketcdn-header--subtitle">RocketCDN delivers your content from servers around the world for a faster website.</p>
				</div>
				<ul class="wpr-rocketcdn-features">
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">Unlimited Performance</h3>
							<p class="wpr-rocketcdn-feature--description">Experience blazing-fast content delivery through 120 edge locations with unlimited bandwidth.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">Pre-Tuned for Speed</h3>
							<p class="wpr-rocketcdn-feature--description">Enjoy pre-configured settings tailored for maximum speed and performance.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">Effortless Setup</h3>
							<p class="wpr-rocketcdn-feature--description">Benefit from automatic configuration of the CDN option in WP Rocket, making setup effortless.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-cta-footer">
						<div class="wpr-rocketcdn-cta-footer--cancel-notice">
							<span>You can cancel anytime!</span>
						</div>
					</li>
				</ul>
			</div>
			<div class="wpr-rocketcdn-pricing ">
				<div class="wpr-rocketcdn-pricing--content">
					<div class="wpr-rocketcdn-pricing--price-container">
						<div class="wpr-rocketcdn-pricing--price">
							<span class="wpr-rocketcdn-pricing--currency">$</span>
							<span class="wpr-rocketcdn-pricing--amount wpr-rocketcdn-pricing--monthly">7</span>
							<span class="wpr-rocketcdn-pricing--cents wpr-rocketcdn-pricing--monthly">.99</span>

							<span class="wpr-rocketcdn-pricing--amount wpr-rocketcdn-pricing--annual wpr-isHidden">79</span>
							<span class="wpr-rocketcdn-pricing--cents wpr-rocketcdn-pricing--annual wpr-isHidden">.99</span>
						</div>
						<div class="wpr-rocketcdn-pricing--billing">
							<span class="wpr-rocketcdn-pricing--billing-period">per month, billed yearly</span>
							<span class="wpr-rocketcdn-pricing--billing-vat">(excl. VAT)</span>
						</div>
					</div>
					<button class="wpr-rocketcdn-pricing--cta wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button>
				</div>
			</div>
			<button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta">
				<span class="screen-reader-text">Reduce this banner</span>
			</button>
		</div>
	</section>
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
				'annual_price'             => 79.99,
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
			'integration' => <<<HTML
<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small">
	<div class="wpr-flex">
		<section>
			<h3 class="notice-title">
				Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.
			</h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta">
				Learn More</button>
		</div>
	</div>
</div>
<div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta">
	<section class="wpr-rocketcdn-cta-content--no-promo">
		<div class="wpr-flex">
			<div class="wpr-rocketcdn-card-left">
				<div class="wpr-rocketcdn-header">
					<h2 class="wpr-rocketcdn-header--title">
						Propel your Content at the Speed of Light!</h2>
					<p class="wpr-rocketcdn-header--subtitle">
						RocketCDN delivers your content from servers around the world for a faster website.</p>
				</div>
				<ul class="wpr-rocketcdn-features">
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">
								Unlimited Performance</h3>
							<p class="wpr-rocketcdn-feature--description">
								Experience blazing-fast content delivery through 120 edge locations with unlimited bandwidth.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">
								Pre-Tuned for Speed</h3>
							<p class="wpr-rocketcdn-feature--description">
								Enjoy pre-configured settings tailored for maximum speed and performance.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">
								Effortless Setup</h3>
							<p class="wpr-rocketcdn-feature--description">
								Benefit from automatic configuration of the CDN option in WP Rocket, making setup effortless.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-cta-footer">
						<div class="wpr-rocketcdn-cta-footer--cancel-notice">
							<span>
								You can cancel anytime!</span>
						</div>
					</li>
				</ul>
			</div>
			<div class="wpr-rocketcdn-pricing ">
				<div class="wpr-rocketcdn-pricing--logo">
					<img src="http://example.org/wp-content/plugins/wp-rocket/assets/img/rocketcdn-logo.svg" alt="Rocket Logo" class="wpr-rocketcdn-pricing--logo-icon">
					<img src="http://example.org/wp-content/plugins/wp-rocket/assets/img/rocketcdn-text.svg" alt="RocketCDN Text" class="wpr-rocketcdn-pricing--logo-text">
				</div>
				<div class="wpr-rocketcdn-pricing--content">
					<div class="wpr-rocketcdn-pricing--toggle">
						<label class="wpr-rocketcdn-toggle">
							<input type="checkbox" class="wpr-rocketcdn-toggle--input">
							<span class="wpr-rocketcdn-toggle--slider"></span>

							<span class="wpr-rocketcdn-pricing--toggle-label wpr-rocketcdn-pricing--toggle-label--inactive">Monthly</span>
							<span class="wpr-rocketcdn-pricing--toggle-label wpr-rocketcdn-pricing--toggle-label--active">Yearly</span>

							<div class="wpr-rocketcdn-pricing--badge">2 Months Free!</div>
						</label>
					</div>
					<div class="wpr-rocketcdn-pricing--price-container">
						<div class="wpr-rocketcdn-pricing--price">
							<span class="wpr-rocketcdn-pricing--currency">$</span>
							<span class="wpr-rocketcdn-pricing--amount wpr-rocketcdn-pricing--monthly">7</span>
							<span class="wpr-rocketcdn-pricing--cents wpr-rocketcdn-pricing--monthly">.99</span>

							<span class="wpr-rocketcdn-pricing--amount wpr-rocketcdn-pricing--annual wpr-isHidden">79</span>
							<span class="wpr-rocketcdn-pricing--cents wpr-rocketcdn-pricing--annual wpr-isHidden">.99</span>
						</div>
						<div class="wpr-rocketcdn-pricing--billing">
							<span class="wpr-rocketcdn-pricing--billing-period">
								per month, billed yearly</span>
							<span class="wpr-rocketcdn-pricing--billing-vat">
								(excl. VAT)</span>
						</div>
					</div>
					<button class="wpr-rocketcdn-pricing--cta wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">
						Get Started</button>
				</div>
			</div>
			<button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta">
				<span class="screen-reader-text">Reduce this banner</span>
			</button>
		</div>
	</section>
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
				'annual_price'             => 79.99,
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
			'integration' => <<<HTML
<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small">
	<div class="wpr-flex">
		<section>
			<h3 class="notice-title">
				Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.
			</h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta">
				Learn More</button>
		</div>
	</div>
</div>
<div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta">
	<section class="wpr-rocketcdn-cta-content--no-promo">
		<div class="wpr-flex">
			<div class="wpr-rocketcdn-card-left">
				<div class="wpr-rocketcdn-header">
					<h2 class="wpr-rocketcdn-header--title">
						Propel your Content at the Speed of Light!</h2>
					<p class="wpr-rocketcdn-header--subtitle">
						RocketCDN delivers your content from servers around the world for a faster website.</p>
				</div>
				<ul class="wpr-rocketcdn-features">
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">
								Unlimited Performance</h3>
							<p class="wpr-rocketcdn-feature--description">
								Experience blazing-fast content delivery through 120 edge locations with unlimited bandwidth.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">
								Pre-Tuned for Speed</h3>
							<p class="wpr-rocketcdn-feature--description">
								Enjoy pre-configured settings tailored for maximum speed and performance.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">
								Effortless Setup</h3>
							<p class="wpr-rocketcdn-feature--description">
								Benefit from automatic configuration of the CDN option in WP Rocket, making setup effortless.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-cta-footer">
						<div class="wpr-rocketcdn-cta-footer--cancel-notice">
							<span>
								You can cancel anytime!</span>
						</div>
					</li>
				</ul>
			</div>
			<div class="wpr-rocketcdn-pricing ">
				<div class="wpr-rocketcdn-pricing--logo">
					<img src="http://example.org/wp-content/plugins/wp-rocket/assets/img/rocketcdn-logo.svg" alt="Rocket Logo" class="wpr-rocketcdn-pricing--logo-icon">
					<img src="http://example.org/wp-content/plugins/wp-rocket/assets/img/rocketcdn-text.svg" alt="RocketCDN Text" class="wpr-rocketcdn-pricing--logo-text">
				</div>
				<div class="wpr-rocketcdn-pricing--content">
					<div class="wpr-rocketcdn-pricing--toggle">
						<label class="wpr-rocketcdn-toggle">
							<input type="checkbox" class="wpr-rocketcdn-toggle--input">
							<span class="wpr-rocketcdn-toggle--slider"></span>

							<span class="wpr-rocketcdn-pricing--toggle-label wpr-rocketcdn-pricing--toggle-label--inactive">Monthly</span>
							<span class="wpr-rocketcdn-pricing--toggle-label wpr-rocketcdn-pricing--toggle-label--active">Yearly</span>

							<div class="wpr-rocketcdn-pricing--badge">2 Months Free!</div>
						</label>
					</div>
					<div class="wpr-rocketcdn-pricing--price-container">
						<div class="wpr-rocketcdn-pricing--price">
							<span class="wpr-rocketcdn-pricing--currency">$</span>
							<span class="wpr-rocketcdn-pricing--amount wpr-rocketcdn-pricing--monthly">7</span>
							<span class="wpr-rocketcdn-pricing--cents wpr-rocketcdn-pricing--monthly">.99</span>

							<span class="wpr-rocketcdn-pricing--amount wpr-rocketcdn-pricing--annual wpr-isHidden">79</span>
							<span class="wpr-rocketcdn-pricing--cents wpr-rocketcdn-pricing--annual wpr-isHidden">.99</span>
						</div>
						<div class="wpr-rocketcdn-pricing--billing">
							<span class="wpr-rocketcdn-pricing--billing-period">
								per month, billed yearly</span>
							<span class="wpr-rocketcdn-pricing--billing-vat">
								(excl. VAT)</span>
						</div>
					</div>
					<button class="wpr-rocketcdn-pricing--cta wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">
						Get Started</button>
				</div>
			</div>
			<button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta">
				<span class="screen-reader-text">Reduce this banner</span>
			</button>
		</div>
	</section>
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
				'annual_price'             => 79.99,
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
			'integration' => <<<HTML
<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small">
	<div class="wpr-flex">
		<section>
			<h3 class="notice-title">
				Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.
			</h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta">
				Learn More</button>
		</div>
	</div>
</div>
<div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta">
	<section class="wpr-rocketcdn-cta-content--no-promo">
		<div class="wpr-flex">
			<div class="wpr-rocketcdn-card-left">
				<div class="wpr-rocketcdn-header">
					<h2 class="wpr-rocketcdn-header--title">
						Propel your Content at the Speed of Light!</h2>
					<p class="wpr-rocketcdn-header--subtitle">
						RocketCDN delivers your content from servers around the world for a faster website.</p>
				</div>
				<ul class="wpr-rocketcdn-features">
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">
								Unlimited Performance</h3>
							<p class="wpr-rocketcdn-feature--description">
								Experience blazing-fast content delivery through 120 edge locations with unlimited bandwidth.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">
								Pre-Tuned for Speed</h3>
							<p class="wpr-rocketcdn-feature--description">
								Enjoy pre-configured settings tailored for maximum speed and performance.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">
								Effortless Setup</h3>
							<p class="wpr-rocketcdn-feature--description">
								Benefit from automatic configuration of the CDN option in WP Rocket, making setup effortless.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-cta-footer">
						<div class="wpr-rocketcdn-cta-footer--cancel-notice">
							<span>
								You can cancel anytime!</span>
						</div>
					</li>
				</ul>
			</div>
			<div class="wpr-rocketcdn-pricing ">
				<div class="wpr-rocketcdn-pricing--logo">
					<img src="http://example.org/wp-content/plugins/wp-rocket/assets/img/rocketcdn-logo.svg" alt="Rocket Logo" class="wpr-rocketcdn-pricing--logo-icon">
					<img src="http://example.org/wp-content/plugins/wp-rocket/assets/img/rocketcdn-text.svg" alt="RocketCDN Text" class="wpr-rocketcdn-pricing--logo-text">
				</div>
				<div class="wpr-rocketcdn-pricing--content">
					<div class="wpr-rocketcdn-pricing--toggle">
						<label class="wpr-rocketcdn-toggle">
							<input type="checkbox" class="wpr-rocketcdn-toggle--input">
							<span class="wpr-rocketcdn-toggle--slider"></span>

							<span class="wpr-rocketcdn-pricing--toggle-label wpr-rocketcdn-pricing--toggle-label--inactive">Monthly</span>
							<span class="wpr-rocketcdn-pricing--toggle-label wpr-rocketcdn-pricing--toggle-label--active">Yearly</span>

							<div class="wpr-rocketcdn-pricing--badge">2 Months Free!</div>
						</label>
					</div>
					<div class="wpr-rocketcdn-pricing--price-container">
						<div class="wpr-rocketcdn-pricing--price">
							<span class="wpr-rocketcdn-pricing--currency">$</span>
							<span class="wpr-rocketcdn-pricing--amount wpr-rocketcdn-pricing--monthly">7</span>
							<span class="wpr-rocketcdn-pricing--cents wpr-rocketcdn-pricing--monthly">.99</span>

							<span class="wpr-rocketcdn-pricing--amount wpr-rocketcdn-pricing--annual wpr-isHidden">79</span>
							<span class="wpr-rocketcdn-pricing--cents wpr-rocketcdn-pricing--annual wpr-isHidden">.99</span>
						</div>
						<div class="wpr-rocketcdn-pricing--billing">
							<span class="wpr-rocketcdn-pricing--billing-period">
								per month, billed yearly</span>
							<span class="wpr-rocketcdn-pricing--billing-vat">
								(excl. VAT)</span>
						</div>
					</div>
					<button class="wpr-rocketcdn-pricing--cta wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">
						Get Started</button>
				</div>
			</div>
			<button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta">
				<span class="screen-reader-text">Reduce this banner</span>
			</button>
		</div>
	</section>
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
				'annual_price'             => 79.99,
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
					'current_price_annual'      => 79.99,
				],
			],
			'integration' => <<<HTML
<div class="wpr-rocketcdn-cta-small notice-alt notice-warning " id="wpr-rocketcdn-cta-small">
	<div class="wpr-flex">
		<section>
			<h3 class="notice-title">
				Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.
			</h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta">
				Learn More</button>
		</div>
	</div>
</div>
<div class="wpr-rocketcdn-cta wpr-isHidden" id="wpr-rocketcdn-cta">
	<section class="wpr-rocketcdn-cta-content--no-promo">
		<div class="wpr-flex">
			<div class="wpr-rocketcdn-card-left">
				<div class="wpr-rocketcdn-header">
					<h2 class="wpr-rocketcdn-header--title">
						Propel your Content at the Speed of Light!</h2>
					<p class="wpr-rocketcdn-header--subtitle">
						RocketCDN delivers your content from servers around the world for a faster website.</p>
				</div>
				<ul class="wpr-rocketcdn-features">
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">
								Unlimited Performance</h3>
							<p class="wpr-rocketcdn-feature--description">
								Experience blazing-fast content delivery through 120 edge locations with unlimited bandwidth.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">
								Pre-Tuned for Speed</h3>
							<p class="wpr-rocketcdn-feature--description">
								Enjoy pre-configured settings tailored for maximum speed and performance.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">
								Effortless Setup</h3>
							<p class="wpr-rocketcdn-feature--description">
								Benefit from automatic configuration of the CDN option in WP Rocket, making setup effortless.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-cta-footer">
						<div class="wpr-rocketcdn-cta-footer--cancel-notice">
							<span>
								You can cancel anytime!</span>
						</div>
					</li>
				</ul>
			</div>
			<div class="wpr-rocketcdn-pricing ">
				<div class="wpr-rocketcdn-pricing--logo">
					<img src="http://example.org/wp-content/plugins/wp-rocket/assets/img/rocketcdn-logo.svg" alt="Rocket Logo" class="wpr-rocketcdn-pricing--logo-icon">
					<img src="http://example.org/wp-content/plugins/wp-rocket/assets/img/rocketcdn-text.svg" alt="RocketCDN Text" class="wpr-rocketcdn-pricing--logo-text">
				</div>
				<div class="wpr-rocketcdn-pricing--content">
					<div class="wpr-rocketcdn-pricing--toggle">
						<label class="wpr-rocketcdn-toggle">
							<input type="checkbox" class="wpr-rocketcdn-toggle--input">
							<span class="wpr-rocketcdn-toggle--slider"></span>

							<span class="wpr-rocketcdn-pricing--toggle-label wpr-rocketcdn-pricing--toggle-label--inactive">Monthly</span>
							<span class="wpr-rocketcdn-pricing--toggle-label wpr-rocketcdn-pricing--toggle-label--active">Yearly</span>

							<div class="wpr-rocketcdn-pricing--badge">2 Months Free!</div>
						</label>
					</div>
					<div class="wpr-rocketcdn-pricing--price-container">
						<div class="wpr-rocketcdn-pricing--price">
							<span class="wpr-rocketcdn-pricing--currency">$</span>
							<span class="wpr-rocketcdn-pricing--amount wpr-rocketcdn-pricing--monthly">7</span>
							<span class="wpr-rocketcdn-pricing--cents wpr-rocketcdn-pricing--monthly">.99</span>

							<span class="wpr-rocketcdn-pricing--amount wpr-rocketcdn-pricing--annual wpr-isHidden">79</span>
							<span class="wpr-rocketcdn-pricing--cents wpr-rocketcdn-pricing--annual wpr-isHidden">.99</span>
						</div>
						<div class="wpr-rocketcdn-pricing--billing">
							<span class="wpr-rocketcdn-pricing--billing-period">
								per month, billed yearly</span>
							<span class="wpr-rocketcdn-pricing--billing-vat">
								(excl. VAT)</span>
						</div>
					</div>
					<button class="wpr-rocketcdn-pricing--cta wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">
						Get Started</button>
				</div>
			</div>
			<button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta">
				<span class="screen-reader-text">Reduce this banner</span>
			</button>
		</div>
	</section>
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
				'discounted_price_yearly'  => 59.99,
				'discount_campaign_name'   => 'Launch',
				'end_date'                 => date( 'Y-m-d', strtotime( 'tomorrow', time() ) ),
				'monthly_price'            => 7.99,
				'annual_price'             => 79.99,
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
					'regular_price_annual'       => 79.99,
					'current_price_monthly'      => 5.99,
					'current_price_annual'      => 59.99,
				],
			],
			'integration' => '
<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small">
	<div class="wpr-flex">
		<section>
			<h3 class="notice-title">Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.</h3>
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
		<div class="wpr-flex">
			<div class="wpr-rocketcdn-card-left">
				<div class="wpr-rocketcdn-header">
					<h2 class="wpr-rocketcdn-header--title">Propel your Content at the Speed of Light!</h2>
					<p class="wpr-rocketcdn-header--subtitle">RocketCDN delivers your content from servers around the world for a faster website.</p>
				</div>
				<ul class="wpr-rocketcdn-features">
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">Unlimited Performance</h3>
							<p class="wpr-rocketcdn-feature--description">Experience blazing-fast content delivery through 120 edge locations with unlimited bandwidth.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">Pre-Tuned for Speed</h3>
							<p class="wpr-rocketcdn-feature--description">Enjoy pre-configured settings tailored for maximum speed and performance.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">
						<div class="wpr-rocketcdn-feature--content">
							<h3 class="wpr-rocketcdn-feature--title">Effortless Setup</h3>
							<p class="wpr-rocketcdn-feature--description">Benefit from automatic configuration of the CDN option in WP Rocket, making setup effortless.</p>
						</div>
					</li>
					<li class="wpr-rocketcdn-cta-footer">
						<div class="wpr-rocketcdn-cta-footer--cancel-notice">
							<span>You can cancel anytime!</span>
						</div>
					</li>
					<li class="wpr-rocketcdn-cta-promo-footer">*$5.99/month for 12 months then $7.99/month. You can cancel your subscription at any time.</li>
				</ul>
			</div>
			<div class="wpr-rocketcdn-pricing has-regular-price">
				<div class="wpr-rocketcdn-pricing--logo">
					<img src="http://example.org/wp-content/plugins/wp-rocket/assets/img/rocketcdn-logo.svg" alt="Rocket Logo" class="wpr-rocketcdn-pricing--logo-icon">
					<img src="http://example.org/wp-content/plugins/wp-rocket/assets/img/rocketcdn-text.svg" alt="RocketCDN Text" class="wpr-rocketcdn-pricing--logo-text">
				</div>
				<div class="wpr-rocketcdn-pricing--content">
					<div class="wpr-rocketcdn-pricing--toggle">
						<label class="wpr-rocketcdn-toggle">
							<input type="checkbox" class="wpr-rocketcdn-toggle--input">
							<span class="wpr-rocketcdn-toggle--slider"></span>

							<span class="wpr-rocketcdn-pricing--toggle-label wpr-rocketcdn-pricing--toggle-label--inactive">Monthly</span>
							<span class="wpr-rocketcdn-pricing--toggle-label wpr-rocketcdn-pricing--toggle-label--active">Yearly</span>

							<div class="wpr-rocketcdn-pricing--badge">2 Months Free!</div>
						</label>
					</div>
					<div class="wpr-rocketcdn-pricing--price-container">
						<h4 class="wpr-title2 wpr-rocketcdn-pricing-regular">
						<del>
							<span class="wpr-rocketcdn-pricing-regular-price wpr-rocketcdn-pricing-regular-price--monthly">$7.99</span>
							<span class="wpr-rocketcdn-pricing-regular-price wpr-rocketcdn-pricing-regular-price--yearly wpr-isHidden">$79.99</span>
						</del>
						</h4>
						<div class="wpr-rocketcdn-pricing--price">
							<span class="wpr-rocketcdn-pricing--currency">$</span>
							<span class="wpr-rocketcdn-pricing--amount wpr-rocketcdn-pricing--monthly">5</span>
							<span class="wpr-rocketcdn-pricing--cents wpr-rocketcdn-pricing--monthly">.99*</span>

							<span class="wpr-rocketcdn-pricing--amount wpr-rocketcdn-pricing--annual wpr-isHidden">59</span>
							<span class="wpr-rocketcdn-pricing--cents wpr-rocketcdn-pricing--annual wpr-isHidden">.99*</span>
						</div>
						<div class="wpr-rocketcdn-pricing--billing">
							<span class="wpr-rocketcdn-pricing--billing-period">per month, billed yearly</span>
							<span class="wpr-rocketcdn-pricing--billing-vat">(excl. VAT)</span>
						</div>
					</div>
					<button class="wpr-rocketcdn-pricing--cta wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button>
				</div>
			</div>
			<button class="wpr-rocketcdn-cta-close" id="wpr-rocketcdn-close-cta">
				<span class="screen-reader-text">Reduce this banner</span>
			</button>
		</div>
	</section>
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
            <h3 class="notice-title">Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.</h3>
        </section>
        <div>
            <button class="wpr-button" id="wpr-rocketcdn-open-cta">
                Learn More</button>
        </div>
    </div>
</div>
<div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta">
    <section class="wpr-rocketcdn-cta-content--no-promo">
        <div class="wpr-flex">
            <div class="wpr-rocketcdn-card-left">
                <div class="wpr-rocketcdn-header">
                    <h2 class="wpr-rocketcdn-header--title">
                    Propel your Content at the Speed of Light!</h2>
                    <p class="wpr-rocketcdn-header--subtitle">
                    RocketCDN delivers your content from servers around the world for a faster website.</p>
                </div>
                <ul class="wpr-rocketcdn-features">
                    <li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">
                        <div class="wpr-rocketcdn-feature--content">
                            <h3 class="wpr-rocketcdn-feature--title">
                            Unlimited Performance</h3>
                            <p class="wpr-rocketcdn-feature--description">
                            Experience blazing-fast content delivery through 120 edge locations with unlimited bandwidth.</p>
                        </div>
                    </li>
                    <li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">
                        <div class="wpr-rocketcdn-feature--content">
                            <h3 class="wpr-rocketcdn-feature--title">
                            Pre-Tuned for Speed</h3>
                            <p class="wpr-rocketcdn-feature--description">
                            Enjoy pre-configured settings tailored for maximum speed and performance.</p>
                        </div>
                    </li>
                    <li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">
                        <div class="wpr-rocketcdn-feature--content">
                            <h3 class="wpr-rocketcdn-feature--title">
                            Effortless Setup</h3>
                            <p class="wpr-rocketcdn-feature--description">
                            Benefit from automatic configuration of the CDN option in WP Rocket, making setup effortless.</p>
                        </div>
                    </li>
                    <li class="wpr-rocketcdn-cta-footer">
                        <div class="wpr-rocketcdn-cta-footer--cancel-notice">
                            <span>
                            You can cancel anytime!</span>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="wpr-rocketcdn-pricing ">
                <p>
                RocketCDN is not available at the moment. Please retry later.<a href="https://docs.wp-rocket.me/article/1608-error-notices-during-the-rocketcdn-subscription-process/?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="60ddc72d9e87cb3d01249270" rel="noopener noreferrer" target="_blank">
                More Info</a>
                </p>
            </div>
            <button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta">
                <span class="screen-reader-text">
                Reduce this banner</span>
            </button>
        </div>
    </section>
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
