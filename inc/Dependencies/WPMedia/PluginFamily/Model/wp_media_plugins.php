<?php
/**
 * WP Media plugin family data.
 */

return [
	'optimize_performance' => [
		'title'   => 'Optimize Performance',
		'plugins' => [
			'wp-rocket/wp-rocket' => [
				'logo'  => [
					'file'  => 'logo-wp-rocket.svg',
					'width' => '50%',
				],
				'title' => 'Speed Up Your Website, Instantly',
				'desc'  => 'WP Rocket is the easiest way to make your WordPress website faster and boost your Google PageSpeed score. Get more traffic, better engagement, and higher conversions effortlessly.',
				'link'  => '',
			],
			'imagify/imagify'     => [
				'logo'  => [
					'file'  => 'logo-imagify.svg',
					'width' => '50%',
				],
				'title' => 'Speed Up Your Website With Lighter Images',
				'desc'  => 'Imagify is the easiest WordPress image optimizer. It automatically compresses images, converts them to WebP and AVIF formats, and lets you resize and optimize with just one click!',
				'link'  => 'https://imagify.io/',
			],
		],
	],
	'boost_traffic'        => [
		'title'   => 'Boost Traffic',
		'plugins' => [
			'seo-by-rank-math/rank-math' => [
				'logo'  => [
					'file'  => 'logo-rank-math.svg',
					'width' => '60%',
				],
				'title' => 'The Swiss Army Knife of SEO Tools',
				'desc'  => 'Rank Math SEO is the Best WordPress SEO plugin with the features of many SEO and AI SEO tools in a single package to help multiply your SEO traffic.',
				'link'  => 'https://rankmath.com/wordpress/plugin/seo-suite/',
			],
		],
	],
	'protect_secure'       => [
		'title'   => 'Protect & Secure',
		'plugins' => [
			'backwpup/backwpup'                   => [
				'logo'  => [
					'file'  => 'logo-backwpup.svg',
					'width' => '60%',
				],
				'title' => 'The Easiest Way to Protect Your Website',
				'desc'  => 'BackWPup is the most comprehensive and user-friendly backup & restore plugin for WordPress. Easily schedule automatic backups, securely store and restore with just a few clicks!',
				'link'  => 'https://backwpup.com/',
			],
			'uk-cookie-consent/uk-cookie-consent' => [
				'logo'  => [
					'file'  => 'logo-termly.svg',
					'width' => '50%',
				],
				'title' => 'GDPR/CCPA Cookie Consent Banner',
				'desc'  => 'One of the easiest, most comprehensive, and popular cookie consent plugins available. Google Gold Certified Partner to quickly comply with data privacy laws from around the world.',
				'link'  => 'https://termly.io/resources/articles/wordpress-cookies-guide/',
			],
		],
	],
];
