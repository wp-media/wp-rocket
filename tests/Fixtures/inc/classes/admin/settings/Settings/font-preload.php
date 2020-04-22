<?php
return [
	// Empty WP Rocket options array.
	[
		[
		],
		[
			'preload_fonts' => [],
		],
	],
	// Empty textarea for preload fonts.
	[
		[
			'preload_fonts' => '',
		],
		[
			'preload_fonts' => [],
		],
	],
	// Textarea with various values as a string and duplicates.
	[
		[
			'preload_fonts' => "http://example.org/wp-content/file.ttf?foo=bar \nhttps://example.org/wp-content/file.ttf?foo=bar\n//example.org/wp-content/file.ttf?foo=bar\n \n/wp-content/file.ttf?foo=bar\n/wp-content/file.ttf?bar=baz\nhttp://example.org/wp-content/themes/this-theme/assets/font.svg\n//google.com/font.eot",
		],
		[
			'preload_fonts' => [
				'/wp-content/file.ttf',
				'/wp-content/themes/this-theme/assets/font.svg',
			],
		],
	],
	// Input as an array with duplicates.
	[
		[
			'preload_fonts' => [
				'http://example.org/wp-content/file.ttf?foo=bar',
				'https://example.org/wp-content/file.ttf?foo=bar',
				'//example.org/wp-content/file.ttf?foo=bar',
				' ',
				'/wp-content/file.ttf?foo=bar',
				'/wp-content/file.ttf?bar=baz',
				'http://example.org/wp-content/themes/this-theme/assets/font.svg',
				'//google.com/font.eot',
			],
		],
		[
			'preload_fonts' => [
				'/wp-content/file.ttf',
				'/wp-content/themes/this-theme/assets/font.svg',
			],
		],
	],
	// Only valid font formats.
	[
		[
			'preload_fonts' => [
				'http://example.org/wp-content/file.dfont',
				'http://example.org/wp-content/file.eot',
				'http://example.org/wp-content/file.otc',
				'http://example.org/wp-content/file.otf',
				'http://example.org/wp-content/file.ott',
				'http://example.org/wp-content/file.ttc',
				'http://example.org/wp-content/file.tte',
				'http://example.org/wp-content/file.ttf',
				'http://example.org/wp-content/file.svg',
				'http://example.org/wp-content/file.woff',
				'http://example.org/wp-content/file.woff2',
				'http://example.org/wp-content/file.css',
				'http://example.org/wp-content/file.js',
			],
		],
		[
			'preload_fonts' => [
				'/wp-content/file.otf',
				'/wp-content/file.ttf',
				'/wp-content/file.svg',
				'/wp-content/file.woff',
				'/wp-content/file.woff2',
			],
		],
	],
];
