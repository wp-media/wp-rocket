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
		<div class="wpr-Upgrade-Plus">
		<h3 class="wpr-upgrade-title">
		Plus</h3>
		<div class="wpr-upgrade-prices">
		<span class="wpr-upgrade-price-symbol">$</span> 50</div>
		<div class="wpr-upgrade-websites">
		3 websites</div>
		<a href="https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me%20/d89e18ee/plus/" class="wpr-upgrade-link" target="_blank" rel="noopener noreferrer">
		Upgrade to Plus</a>
		</div>
		<div class="wpr-Upgrade-Infinite">
		<h3 class="wpr-upgrade-title">
		Infinite</h3>
		<div class="wpr-upgrade-prices">
		<span class="wpr-upgrade-price-symbol">$</span> 200
		</div>
		<div class="wpr-upgrade-websites">Unlimited websites</div>
		<a href="https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me%20/d89e18ee/infinite/" class="wpr-upgrade-link" target="_blank" rel="noopener noreferrer">
		Upgrade to Infinite</a>
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
		<div class="wpr-Upgrade-Infinite">
		<h3 class="wpr-upgrade-title">
		Infinite</h3>
		<div class="wpr-upgrade-prices">
		<span class="wpr-upgrade-price-symbol">$</span> 150
		</div>
		<div class="wpr-upgrade-websites">Unlimited websites</div>
		<a href="https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me%20/d89e18ee/infinite/" class="wpr-upgrade-link" target="_blank" rel="noopener noreferrer">
		Upgrade to Infinite</a>
		</div>
		</div>
		</div>
		</div>',
	],
];
