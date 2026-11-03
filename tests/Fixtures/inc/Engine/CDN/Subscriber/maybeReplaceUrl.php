<?php
return [
	// Relative URL, nothing to do.
	'shouldDoNothingWithRelativeUrl' => [
		'config' => [
			'cdn_type' => true,
			'original' => '/wp-content/plugins/hello-dolly/style.css',
			'zones' => [
				'all',
				'css',
				'css_and_js',
			],
			'cdn_urls' => [
				'https://123456.rocketcdn.me',
			],
			'site_url' => 'http://example.org',
		],
		'expected' => '/wp-content/plugins/hello-dolly/style.css',
	],
	// CDN URL with no cname, site_url = home_url.
	'shouldDonNothingWithNoCnameAndSiteUrlIsHomeUrl' => [
		'config' => [
			'cdn_type' => true,
			'original' => 'https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/style.css',
			'zones' => [],
			'cdn_urls' => [],
			'site_url' => 'http://example.org',
		],
		'expected' => 'https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/style.css',
	],
	// CDN URL with cname not corresponding to the zones, site_url = home_url.
	'shouldDonNothingWithCnameNotCorrespondingZones' => [
		'config' => [
			'cdn_type' => true,
			'original' => 'https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/style.css',
			'zones' => [
				'all',
				'css',
				'css_and_js',
			],
			'cdn_urls' => [],
			'site_url' => 'http://example.org',
		],
		'expected' => 'https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/style.css',
	],
	// CDN URL with corresponding cnames in option, site_url = home_url.
	'testWithCnameCorrespondingZones' => [
		'config' => [
			'cdn_type' => true,
			'original' => 'https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/style.css',
			'zones' => [
				'all',
				'css',
				'css_and_js',
			],
			'cdn_urls' => [
				'https://123456.rocketcdn.me',
			],
			'site_url' => 'http://example.org',
		],
		'expected' => 'http://example.org/wp-content/plugins/hello-dolly/style.css',
	],
	// CDN URL with corresponding cnames in option, no protocol, site_url = home_url.
	'testWithCnameCorrespondingZonesWithNoProtocol' => [
		'config' => [
			'cdn_type' => true,
			'original' => 'http://123456.rocketcdn.me/wp-content/plugins/hello-dolly/style.css',
			'zones' => [
				'all',
				'css',
				'css_and_js',
			],
			'cdn_urls' => [
				'123456.rocketcdn.me',
			],
			'site_url' => 'http://example.org',
		],
		'expected' => 'http://example.org/wp-content/plugins/hello-dolly/style.css',
	],
	// CDN URL with corresponding cnames in option, site_url != home_url.
	'testWithCnameCorrespondingZonesWithSiteUrlDoesnotEqualHomeUrl' => [
		'config' => [
			'cdn_type' => true,
			'original' => 'https://123456.rocketcdn.me/wordpress/wp-content/plugins/hello-dolly/style.css',
			'zones' => [
				'all',
				'css',
				'css_and_js',
			],
			'cdn_urls' => [
				'https://123456.rocketcdn.me',
			],
			'site_url' => 'http://example.org/wordpress',
		],
		'expected' => 'http://example.org/wordpress/wp-content/plugins/hello-dolly/style.css',
	],
	// CDN URL with subdirectory, with corresponding cnames in option, site_url = home_url.
	'testWithSubDirectory' => [
		'config' => [
			'cdn_type' => true,
			'original' => 'https://123456.rocketcdn.me/cdnpath/wp-content/plugins/hello-dolly/style.css',
			'zones' => [
				'all',
				'css',
				'css_and_js',
			],
			'cdn_urls' => [
				'https://123456.rocketcdn.me/cdnpath',
			],
			'site_url' => 'http://example.org',
		],
		'expected' => 'http://example.org/wp-content/plugins/hello-dolly/style.css',
	],
	// CDN URL with subdirectory, with corresponding cnames in option, site_url != home_url.
	'testWithSubDirectorySiteUrlDoesNotEqualHomeUrl' => [
		'config' => [
			'cdn_type' => true,
			'original' => 'https://123456.rocketcdn.me/cdnpath/wordpress/wp-content/plugins/hello-dolly/style.css',
			'zones' => [
				'all',
				'css',
				'css_and_js',
			],
			'cdn_urls' => [
				'https://123456.rocketcdn.me/cdnpath',
			],
			'site_url' => 'http://example.org/wordpress',
		],
		'expected' => 'http://example.org/wordpress/wp-content/plugins/hello-dolly/style.css',
	],
];
