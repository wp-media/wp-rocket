<?php

$html = <<<HTML
<html>
	<head>
		<title>
			WP Rocket
		</title>
	</head>
	<body>
	</body>
</html>
HTML;

$valid_preload_fonts_options = <<<HTML
<html>
	<head>
		<title>
			WP Rocket
		</title>
<link crossorigin as="font" href="http://example.org/wp-content/file.otf" rel="preload" data-rocket-preload>
<link crossorigin as="font" href="http://example.org/wp-content/file.ttf" rel="preload" data-rocket-preload>
<link crossorigin as="font" href="http://example.org/wp-content/file.svg" rel="preload" data-rocket-preload>
<link crossorigin as="font" href="http://example.org/wp-content/file.woff?v=4.4.0" rel="preload" data-rocket-preload>
<link crossorigin as="font" href="http://example.org/wp-content/file.woff2" rel="preload" data-rocket-preload>
<link crossorigin as="font" href="http://example.org/wp-content/themes/paperback/inc/fontawesome/fonts/fontawesome-webfont.woff2?v=4.4.0" rel="preload" data-rocket-preload>
<link crossorigin as="font" href="http://example.org/wp-content/themes/paperback/inc/fontawesome/fonts/fontawesome-webfont.woff2#123" rel="preload" data-rocket-preload>
	</head>
	<body>
	</body>
</html>
HTML;

$valid_preload_fonts_options_wit_cdn = <<<HTML
<html>
	<head>
		<title>
			WP Rocket
		</title>
<link crossorigin as="font" href="https://123456.rocketcdn.me/wp-content/file.otf" rel="preload" data-rocket-preload>
<link crossorigin as="font" href="https://123456.rocketcdn.me/wp-content/file.ttf" rel="preload" data-rocket-preload>
<link crossorigin as="font" href="https://123456.rocketcdn.me/wp-content/file.svg" rel="preload" data-rocket-preload>
<link crossorigin as="font" href="https://123456.rocketcdn.me/wp-content/file.woff" rel="preload" data-rocket-preload>
<link crossorigin as="font" href="https://123456.rocketcdn.me/wp-content/file.woff2" rel="preload" data-rocket-preload>
	</head>
	<body>
	</body>
</html>
HTML;

return [
	'NowprocketQueryString'               => [
		'buffer'   => $html,
		'bypass'   => true,
		'filter'   => false,
		'settings' => [
			'preload_fonts' => [],
			'cdn'           => false,
			'cdn_cnames'    => [],
		],
		'expected' => $html,
	],

	'DoNothingWhenFilterIsTrue'           => [
		'buffer'   => $html,
		'bypass'   => false,
		'filter'   => true,
		'settings' => [
			'preload_fonts' => [],
			'cdn'           => false,
			'cdn_cnames'    => [],
		],
		'expected' => $html,
	],

	'emptyPreloadFontsOption'             => [
		'buffer'   => $html,
		'bypass'   => false,
		'filter'   => false,
		'settings' => [
			'preload_fonts' => [],
			'cdn'           => false,
			'cdn_cnames'    => [],
		],
		'expected' => $html,
	],
	'invalidPreloadFontsOptionExtensions' => [
		'buffer'   => $html,
		'bypass'   => false,
		'filter'   => false,
		'settings' => [
			'preload_fonts' => [
				'/wp-content/style.css',
				'/wp-content/style.js',
				'/test.eot',
			],
			'cdn'           => false,
			'cdn_cnames'    => [],
		],
		'expected' => $html,
	],
	'validPreloadFontsOptions'            => [
		'buffer'   => $html,
		'bypass'   => false,
		'filter'   => false,
		'settings' => [
			'preload_fonts' => [
				'/wp-content/file.dfont',
				'',
				'/wp-content/file.eot',
				'/wp-content/file.otc',
				'/wp-content/file.otf',
				'/wp-content/file.ott',
				'/wp-content/file.ttc',
				'/wp-content/file.tte',
				'/wp-content/file.ttf',
				'/wp-content/file.svg',
				'/wp-content/file.woff?v=4.4.0',
				'/wp-content/file.woff2',
				'/wp-content/file.woff2',
				'/wp-content/file.css',
				'/wp-content/file.js',
				'/wp-content/themes/paperback/inc/fontawesome/fonts/fontawesome-webfont.woff2?v=4.4.0',
				'/wp-content/themes/paperback/inc/fontawesome/fonts/fontawesome-webfont.woff2#123',
			],
			'cdn'           => false,
			'cdn_cnames'    => [],
		],
		'expected' => [
			'html' => $valid_preload_fonts_options,
			'fonts' => [
				'http://example.org/wp-content/file.otf',
				'http://example.org/wp-content/file.ttf',
				'http://example.org/wp-content/file.svg',
				'http://example.org/wp-content/file.woff?v=4.4.0',
				'http://example.org/wp-content/file.woff2',
				'http://example.org/wp-content/themes/paperback/inc/fontawesome/fonts/fontawesome-webfont.woff2?v=4.4.0',
				'http://example.org/wp-content/themes/paperback/inc/fontawesome/fonts/fontawesome-webfont.woff2#123',
			],
		],
	],
	'validPreloadFontsOptionsWithCDN'     => [
		'buffer'   => $html,
		'bypass'   => false,
		'filter'   => false,
		'settings' => [
			'preload_fonts' => [
				'/wp-content/file.otf',
				'/wp-content/file.ttf',
				'/wp-content/file.svg',
				'/wp-content/file.woff',
				'/wp-content/file.woff2',
				'/wp-content/file.woff2',
			],
			'cdn'           => true,
			'cdn_cnames'    => [
				'https://123456.rocketcdn.me',
			],
		],
		'expected' => [
			'html' => $valid_preload_fonts_options_wit_cdn,
			'fonts' => [
				'https://123456.rocketcdn.me/wp-content/file.otf',
				'https://123456.rocketcdn.me/wp-content/file.ttf',
				'https://123456.rocketcdn.me/wp-content/file.svg',
				'https://123456.rocketcdn.me/wp-content/file.woff',
				'https://123456.rocketcdn.me/wp-content/file.woff2',
			],
		],
	],
];
