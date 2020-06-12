<?php
return [
	// Empty WP Rocket options array.
	[
		[
		],
		[
			'exclude_css' => [],
		],
	],
	// Empty textarea for Exclude CSS.
	[
		[
			'exclude_css' => '',
		],
		[
			'exclude_css' => [],
		],
	],
	// Textarea with various values as a string and duplicates.
	[
		[
			'exclude_css' => "http://example.org/wp-content/themes/style.css\nhttp://example.org/wp-content/themes/style.css\n",
		],
		[
			'exclude_css' => [
				'/wp-content/themes/style.css',
			],
		],
	],
	// Input as an array with duplicates & internal + external CSS.
	[
		[
			'exclude_css' => [
				'http://example.org/wp-content/themes/style.css',
				'http://example.org/wp-content/themes/style1.css',
				'example.org/style1.css',
				' ',
				'http://external.org/style.css',
				'http://www.external.org/style.css',
				'http://www.external.org/style.js',
			],
		],
		[
			'exclude_css' => [
				'/wp-content/themes/style.css',
				'/wp-content/themes/style1.css',
				'example.org/style1.css',
				'external.org/style.css',
				'www.external.org/style.css',
				false,
			],
		],
	],
];
