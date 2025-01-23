<?php

return [
	'testShouldReturnNullWhenLicenseIsInfinite' => [
		'user'   => json_decode( json_encode( [
			'licence_account'    => -1,
			'licence_expiration' => strtotime( 'next year' ),
		] ) ),
		'pricing' => json_decode( json_encode( [] ) ),
		'expected' => '',
	],
	'testShouldReturnNullWhenLicenseIsExpired' => [
		'user'   => json_decode( json_encode( [
			'licence_account'    => 1,
			'licence_expiration' => strtotime( 'last month' ),
		] ) ),
		'pricing' => json_decode( json_encode( [] ) ),
		'expected' => '',
	],
	'testShouldDisplayPopInWhenLicenseIsSingle' => [
		'user'   => json_decode( json_encode( [
			'licence_account'       => 1,
			'licence_expiration'    => strtotime( 'next year' ),
			'upgrade_plus_url'      => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me /d89e18ee/plus/',
			'upgrade_infinite_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me /d89e18ee/infinite/',
			'licence' => [
				'prices' => [
					'upgrades' => [
						(object) [
							'name' => 'Growth',
							'slug' => 'growth',
							'saving' => "40",
							'upgrade_url' => "https://growthupgradeurl.com/",
							'regular_price' => "50",
							'websites' => "3",
							'stack' => false,
						],
						(object) [
							'name' => 'Multi',
							'slug' => 'multi10',
							'saving' => "180",
							'upgrade_url' => "https://multi10upgradeurl.com/",
							'regular_price' => "200",
							'websites' => "Unlimited",
							'stack' => true,
						],
						(object) [
							'name' => 'Multi',
							'slug' => 'multi50',
							'saving' => "300",
							'upgrade_url' => "https://multi50upgradeurl.com/",
							'regular_price' => "350",
							'websites' => "Unlimited",
							'stack' => true,
						],
					],
				],
			],
		] ) ),
		'pricing' => json_decode( json_encode( [
			'licenses' => [
				'single'   => [
					'websites' => 1,
				],
				'plus'     => [
					'prices'      => [
						'from_single' => [
							'regular' => 50,
						],
					],
					'websites'    => 3,

				],
				'infinite' => [
					'prices'       => [
						'from_single' => [
							'regular' => 200,
						],
					],
					'websites'    => -1,
				],
			],
			'promo' => [
				'name' => 'Halloween',
				'discount_percent' => 20,
				'start_date' => strtotime( 'next week' ),
				'end_date' => strtotime( 'next month' ),
			],
		] ) ),
		'expected' => '<div class="wpr-Popin wpr-Popin-Upgrade">
		<div class="wpr-Popin-header">
		<h2 class="wpr-title1">
		Speed Up More Websites</h2>
		<button class="wpr-Popin-close wpr-Popin-Upgrade-close wpr-icon-close">
		</button>
		</div>
		<div class="wpr-Popin-content">
		<p>
		You can use WP Rocket on more websites by upgrading your license. To upgrade, simply pay the<strong>
		price difference</strong>
		between your current and new licenses, as shown below.</p>
		<p>
		<strong>
		N.B.</strong>
		: Upgrading your license does not change your expiration date</p>
		<div class="wpr-Popin-flex">
		<div class="wpr-upgrade-item wpr-Upgrade-Growth">
		<h3 class="wpr-upgrade-title">
		Growth</h3>
		<div class="wpr-upgrade-prices">
		<span class="wpr-upgrade-price-symbol">$</span> <span class="wpr-upgrade-price-value">50</span></div>
		<div class="wpr-upgrade-websites
			notstacked">
		3 websites</div>
		<a href="https://growthupgradeurl.com/" class="wpr-upgrade-link" target="_blank" rel="noopener noreferrer">
		Upgrade to Growth</a>
		</div>
		<div class="wpr-upgrade-item wpr-Upgrade-Multi">
		<h3 class="wpr-upgrade-title">
		Multi</h3>
		<div class="wpr-upgrade-prices">
		<span class="wpr-upgrade-price-symbol">$</span> <span class="wpr-upgrade-price-value">
200</span>
		</div>
		<div class="wpr-upgrade-websites
	">
<div class="custom-select" id="rocket_stacked_select">
<button class="select-button" role="combobox" aria-label="select button" aria-haspopup="listbox" aria-expanded="false" aria-controls="select-dropdown">
<span class="selected-value has-style-bold">
Unlimited Websites</span>
<span class="custom-select-arrow">
</span>
</button>
<ul class="select-dropdown" role="listbox" id="select-dropdown">
<li role="option"
					data-name="Multi"
					data-price="200"
					data-url="https://multi10upgradeurl.com/"
									>
<input type="radio" id="plan_multi10" name="multi-plans"/>
<label for="multi50">
Unlimited Websites</label>
</li>
<li role="option"
					data-name="Multi"
					data-price="350"
					data-url="https://multi50upgradeurl.com/"
									>
<input type="radio" id="plan_multi50" name="multi-plans"/>
<label for="multi50">
Unlimited Websites</label>
</li>
</ul>
</div>
</div>
		<a href="https://multi10upgradeurl.com/" class="wpr-upgrade-link" target="_blank" rel="noopener noreferrer">
		Upgrade to Multi</a>
		</div>
		</div>
		</div>
		</div>',
	],
	'testShouldDisplayPopInWhenLicenseIsBetweenSingleAndPlus' => [
		'user'   => json_decode( json_encode( [
			'licence_account'       => 2,
			'licence_expiration'    => strtotime( 'next year' ),
			'upgrade_plus_url'      => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me /d89e18ee/plus/',
			'upgrade_infinite_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me /d89e18ee/infinite/',
			'licence' => [
				'prices' => [
					'upgrades' => [
						(object) [
							'name' => 'Growth',
							'slug' => 'growth',
							'saving' => "40",
							'upgrade_url' => "https://growthupgradeurl.com/",
							'regular_price' => "50",
							'websites' => "3",
							'stack' => false,
						],
						(object) [
							'name' => 'Multi',
							'slug' => 'multi10',
							'saving' => "180",
							'upgrade_url' => "https://multi10upgradeurl.com/",
							'regular_price' => "200",
							'websites' => "30",
							'stack' => true,
						],
						(object) [
							'name' => 'Multi',
							'slug' => 'multi50',
							'saving' => "300",
							'upgrade_url' => "https://multi10upgradeurl.com/",
							'regular_price' => "350",
							'websites' => "Unlimited",
							'stack' => true,
						],
					],
				],
			],
		] ) ),
		'pricing' => json_decode( json_encode( [
			'licenses' => [
				'single'   => [
					'websites' => 1,
				],
				'plus'     => [
					'prices'      => [
						'from_single' => [
							'regular' => 50,
						],
					],
					'websites'    => 3,

				],
				'infinite' => [
					'prices'       => [
						'from_single' => [
							'regular' => 200,
						],
					],
					'websites'    => -1,
				],
			],
			'promo' => [
				'name' => 'Halloween',
				'discount_percent' => 20,
				'start_date' => strtotime( 'next week' ),
				'end_date' => strtotime( 'next month' ),
			],
		] ) ),
		'expected' => '<div class="wpr-Popin wpr-Popin-Upgrade">
		<div class="wpr-Popin-header">
		<h2 class="wpr-title1">
		Speed Up More Websites</h2>
		<button class="wpr-Popin-close wpr-Popin-Upgrade-close wpr-icon-close">
		</button>
		</div>
		<div class="wpr-Popin-content">
		<p>
		You can use WP Rocket on more websites by upgrading your license. To upgrade, simply pay the<strong>
		price difference</strong>
		between your current and new licenses, as shown below.</p>
		<p>
		<strong>
		N.B.</strong>
		: Upgrading your license does not change your expiration date</p>
		<div class="wpr-Popin-flex">
		<div class="wpr-upgrade-item wpr-Upgrade-Growth">
		<h3 class="wpr-upgrade-title">
		Growth</h3>
		<div class="wpr-upgrade-prices">
		<span class="wpr-upgrade-price-symbol">$</span> <span class="wpr-upgrade-price-value">50</span></div>
		<div class="wpr-upgrade-websites
			notstacked">
		3 websites</div>
		<a href="https://growthupgradeurl.com/" class="wpr-upgrade-link" target="_blank" rel="noopener noreferrer">
		Upgrade to Growth</a>
		</div>
		<div class="wpr-upgrade-item wpr-Upgrade-Multi">
		<h3 class="wpr-upgrade-title">
		Multi</h3>
		<div class="wpr-upgrade-prices">
		<span class="wpr-upgrade-price-symbol">$</span> <span class="wpr-upgrade-price-value">
200</span>
		</div>
		<div class="wpr-upgrade-websites
	">
<div class="custom-select" id="rocket_stacked_select">
<button class="select-button" role="combobox" aria-label="select button" aria-haspopup="listbox" aria-expanded="false" aria-controls="select-dropdown">
<span class="selected-value has-style-bold">
30 Websites</span>
<span class="custom-select-arrow">
</span>
</button>
<ul class="select-dropdown" role="listbox" id="select-dropdown">
<li role="option"
					data-name="Multi"
					data-price="200"
					data-url="https://multi10upgradeurl.com/"
									>
<input type="radio" id="plan_multi10" name="multi-plans"/>
<label for="multi50">
30 Websites</label>
</li>
<li role="option"
					data-name="Multi"
					data-price="350"
					data-url="https://multi10upgradeurl.com/"
									>
<input type="radio" id="plan_multi50" name="multi-plans"/>
<label for="multi50">
Unlimited Websites</label>
</li>
</ul>
</div>
</div>
		<a href="https://multi10upgradeurl.com/" class="wpr-upgrade-link" target="_blank" rel="noopener noreferrer">
		Upgrade to Multi</a>
		</div>
		</div>
		</div>
		</div>',
	],
	'testShouldDisplayPopInWhenLicenseIsPlus' => [
		'user'   => json_decode( json_encode( [
			'licence_account'    => 3,
			'licence_expiration'    => strtotime( 'next year' ),
			'upgrade_plus_url'      => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me /d89e18ee/plus/',
			'upgrade_infinite_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me /d89e18ee/infinite/',
			'licence' => [
				'prices' => [
					'upgrades' => [
						(object) [
							'name' => 'Multi',
							'slug' => 'multi10',
							'saving' => "180",
							'upgrade_url' => "https://multi10upgradeurl.com/",
							'regular_price' => "200",
							'websites' => "Unlimited",
							'stack' => true,
						],
					],
				],
			],
		] ) ),
		'pricing' => json_decode( json_encode( [
			'licenses' => [
				'single'   => [
					'websites' => 1,
				],
				'plus'     => [
					'prices'      => [
						'from_single' => [
							'regular' => 50,
						],
					],
					'websites'    => 3,

				],
				'infinite' => [
					'prices'       => [
						'from_plus' => [
							'regular' => 150,
						],
					],
					'websites'    => -1,
				],
			],
			'promo' => [
				'name' => 'Halloween',
				'discount_percent' => 20,
				'start_date' => strtotime( 'next week' ),
				'end_date' => strtotime( 'next month' ),
			],
		] ) ),
		'expected' => '<div class="wpr-Popin wpr-Popin-Upgrade">
		<div class="wpr-Popin-header">
		<h2 class="wpr-title1">
		Speed Up More Websites</h2>
		<button class="wpr-Popin-close wpr-Popin-Upgrade-close wpr-icon-close">
		</button>
		</div>
		<div class="wpr-Popin-content">
		<p>
		You can use WP Rocket on more websites by upgrading your license. To upgrade, simply pay the<strong>
		price difference</strong>
		between your current and new licenses, as shown below.</p>
		<p>
		<strong>
		N.B.</strong>
		: Upgrading your license does not change your expiration date</p>
		<div class="wpr-Popin-flex">
		<div class="wpr-upgrade-item wpr-Upgrade-Multi">
		<h3 class="wpr-upgrade-title">
		Multi</h3>
		<div class="wpr-upgrade-prices">
		<span class="wpr-upgrade-price-symbol">$</span> <span class="wpr-upgrade-price-value">
200</span>
		</div>
		<div class="wpr-upgrade-websites
	">
Unlimited websites
</div>
		<a href="https://multi10upgradeurl.com/" class="wpr-upgrade-link" target="_blank" rel="noopener noreferrer">
		Upgrade to Multi</a>
		</div>
		</div>
		</div>
		</div>',
	],
];
