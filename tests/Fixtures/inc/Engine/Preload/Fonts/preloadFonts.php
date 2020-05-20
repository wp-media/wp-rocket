<?php

return [

	'emptyPreloadFontsOption'             => [
		'settings' => [
			'preload_fonts' => [],
			'cdn'           => false,
			'cdn_cnames'    => [],
		],
		'expected' => '',
	],
	'invalidPreloadFontsOptionExtensions' => [
		'settings' => [
			'preload_fonts' => [
				'/wp-content/style.css',
				'/wp-content/style.js',
				'/test.eot',
			],
			'cdn'           => false,
			'cdn_cnames'    => [],
		],
		'expected' => '',
	],
	'validPreloadFontsOptions'            => [
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
		'expected' => <<<HTML
<link rel="preload" as="font" href="http://example.org/wp-content/file.otf" crossorigin>
<link rel="preload" as="font" href="http://example.org/wp-content/file.ttf" crossorigin>
<link rel="preload" as="font" href="http://example.org/wp-content/file.svg" crossorigin>
<link rel="preload" as="font" href="http://example.org/wp-content/file.woff?v=4.4.0" crossorigin>
<link rel="preload" as="font" href="http://example.org/wp-content/file.woff2" crossorigin>
<link rel="preload" as="font" href="http://example.org/wp-content/themes/paperback/inc/fontawesome/fonts/fontawesome-webfont.woff2?v=4.4.0" crossorigin>
<link rel="preload" as="font" href="http://example.org/wp-content/themes/paperback/inc/fontawesome/fonts/fontawesome-webfont.woff2#123" crossorigin>
HTML
		,
	],
	'validPreloadFontsOptionsWithCDN'     => [
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
		'expected' => <<<HTML
<link rel="preload" as="font" href="https://123456.rocketcdn.me/wp-content/file.otf" crossorigin>
<link rel="preload" as="font" href="https://123456.rocketcdn.me/wp-content/file.ttf" crossorigin>
<link rel="preload" as="font" href="https://123456.rocketcdn.me/wp-content/file.svg" crossorigin>
<link rel="preload" as="font" href="https://123456.rocketcdn.me/wp-content/file.woff" crossorigin>
<link rel="preload" as="font" href="https://123456.rocketcdn.me/wp-content/file.woff2" crossorigin>
HTML
		,
	],
];
