<?php
/**
 * WP Media plugin family data.
 */

return [
	'optimize_performance' => [
		'title'   => __( 'Optimize Performance', 'rocket' ),
		'plugins' => [
			'wp-rocket/wp-rocket' => [
				'logo'  => [
					'file'  => 'logo-wp-rocket.svg',
					'width' => '50%',
				],
				'title' => __( 'Speed Up Your Website, Instantly', 'rocket' ),
				'desc'  => __( 'WP Rocket is the easiest way to make your WordPress website faster and boost your Google PageSpeed score. Get more traffic, better engagement, and higher conversions effortlessly.', 'rocket' ),
				'link'  => '',
			],
			'imagify/imagify'     => [
				'logo'  => [
					'file'  => 'logo-imagify.svg',
					'width' => '50%',
				],
				'title' => __( 'Speed Up Your Website With Lighter Images', 'rocket' ),
				'desc'  => __( 'Imagify is the easiest WordPress image optimizer. It automatically compresses images, converts them to WebP and AVIF formats, and lets you resize and optimize with just one click!', 'rocket' ),
				'link'  => 'https://imagify.io/',
			],
		],
	],
	'boost_traffic'        => [
		'title'   => __( 'Boost Traffic', 'rocket' ),
		'plugins' => [
			'seo-by-rank-math/rank-math' => [
				'logo'  => [
					'file'  => 'logo-rank-math.svg',
					'width' => '60%',
				],
				'title' => __( 'The Swiss Army Knife of SEO Tools', 'rocket' ),
				'desc'  => __( 'Rank Math SEO is the Best WordPress SEO plugin with the features of many SEO and AI SEO tools in a single package to help multiply your SEO traffic.', 'rocket' ),
				'link'  => 'https://rankmath.com/wordpress/plugin/seo-suite/',
			],
		],
	],
	'protect_secure'       => [
		'title'   => __( 'Protect & Secure', 'rocket' ),
		'plugins' => [
			'backwpup/backwpup'                   => [
				'logo'  => [
					'file'  => 'logo-backwpup.svg',
					'width' => '60%',
				],
				'title' => __( 'The Easiest Way to Protect Your Website', 'rocket' ),
				'desc'  => __( 'BackWPup is the most comprehensive and user-friendly backup & restore plugin for WordPress. Easily schedule automatic backups, securely store and restore with just a few clicks!', 'rocket' ),
				'link'  => 'https://backwpup.com/',
			],
			'uk-cookie-consent/uk-cookie-consent' => [
				'logo'  => [
					'file'  => 'logo-termly.svg',
					'width' => '50%',
				],
				'title' => __( 'GDPR/CCPA Cookie Consent Banner', 'rocket' ),
				'desc'  => __( 'One of the easiest, most comprehensive, and popular cookie consent plugins available. Google Gold Certified Partner to quickly comply with data privacy laws from around the world.', 'rocket' ),
				'link'  => 'https://termly.io/resources/articles/wordpress-cookies-guide/',
			],
		],
	],
];
