<?php

use function WP_Rocket\Tests\Fixture\CriticalPath\content\get_html_as_string;

$base_html = <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
</head>
<body>Content here</body>
</html>
HTML;

$base_expected = <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
</head>
<body>Content here<script>const wprRemoveCPCSS = () => { \$elem = document.getElementById( "rocket-critical-css" ); if ( \$elem ) { \$elem.remove(); } }; if ( window.addEventListener ) { window.addEventListener( "load", wprRemoveCPCSS ); } else if ( window.attachEvent ) { window.attachEvent( "onload", wprRemoveCPCSS ); }</script></body>
</html>
HTML;


return [

	'vfs_dir'   => 'wp-content/cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'critical-css' => [
					'1' => [
						'.'                     => '',
						'..'                    => '',
						'home.css'              => '.home { color: red; }',
						'home-mobile.css'       => '.home { color: blue; }',
						'front_page.css'        => '.front_page { color: red; }',
						'front_page-mobile.css' => '.front_page { color: blue; }',
					],
				],
			],
		],
	],

	'default_config' => [
		'options'      => [
			'async_css' => 1,
		],
		'critical_css' => [
			'get_current_page_critical_css' => 'page.css',
			'get_exclude_async_css'         => [],
		],
	],

	'test_data' => [

		'shouldBailOutWhenAsyncCSSNotEnabled' => [
			'html'     => $base_html,
			'expected' => $base_html,
			'config'   => [
				'doesnotcreatedom' => true,
				'options'          => [ 'async_css' => 0 ],
			],
		],

		'shouldBailOutWhenCriticalCssReturnsNoCurrentPageCriticalCss' => [
			'html'     => $base_html,
			'expected' => $base_html,
			'config'   => [
				'doesnotcreatedom' => true,
				'options'          => [
					'async_css'    => 1,
					'critical_css' => '',
				],
				'critical_css'     => [ 'get_current_page_critical_css' => '' ],
			],
		],

		'shouldBailOutWhenNoCriticalCssOption' => [
			'html'     => $base_html,
			'expected' => $base_html,
			'config'   => [
				'doesnotcreatedom' => true,
				'options'          => [
					'async_css'    => 1,
					'critical_css' => '',
				],
				'critical_css'     => [ 'get_current_page_critical_css' => '' ],
			],
		],

		'shouldBailOutWhenPostExcludesAsyncCss' => [
			'html'     => $base_html,
			'expected' => $base_html,
			'config'   => [
				'doesnotcreatedom' => true,
				'options'          => [ 'async_css' => 1 ],
				'critical_css'     => [ 'get_current_page_critical_css' => 'something' ],
				'functions'        => [ 'is_rocket_post_excluded_option' => true ],
			],
		],

		'shouldBailOutAndReturnOriginalHTMLWhenNoCssLinksInHTML' => [
			'html'     => $base_html,
			'expected' => $base_expected,
		],

		'async-css onload: no "onload" - use defaults' => [
			'html'     => get_html_as_string( 'original/no-onload' ),
			'expected' => get_html_as_string( 'final/async-css/no-onload' ),
		],

		'async-css onload: preserve original this.media=all' => [
			'html'     => get_html_as_string( 'original/onload-media-all' ),
			'expected' => get_html_as_string( 'final/async-css/onload-media-all' ),
		],

		'async-css onload: use default when original this.media=print' => [
			'html'     => get_html_as_string( 'original/onload-media-print' ),
			'expected' => get_html_as_string( 'final/async-css/onload-media-print' ),
		],

		'async-css noscript: remove link "id" attribute' => [
			'html'     => get_html_as_string( 'original/noscript-remove-link-id' ),
			'expected' => get_html_as_string( 'final/async-css/noscript-remove-link-id' ),
		],

		'async-css: remove extra spaces in attribute value' => [
			'html'     => get_html_as_string( 'original/attribute-value-spaces-semicolon' ),
			'expected' => get_html_as_string( 'final/async-css/attribute-value-spaces-semicolon' ),
		],

		'async-css: preserve onload function' => [
			'html'     => get_html_as_string( 'original/onload-function' ),
			'expected' => get_html_as_string( 'final/async-css/onload-function' ),
		],

		'async-css: apply to stylesheets in head and body' => [
			'html'     => get_html_as_string( 'original/multiple-stylesheets' ),
			'expected' => get_html_as_string( 'final/async-css/multiple-stylesheets' ),
		],

		'async-css onload: escaped quotes in attribute value' => [
			'html'     => get_html_as_string( 'original/onload-escaped-quotes' ),
			'expected' => get_html_as_string( 'final/async-css/onload-escaped-quotes' ),
		],

		// Exclude CSS URLs.

		'bail out when the only stylesheet is excluded' => [
			'html'     => get_html_as_string( 'original/exclude' ),
			'expected' => get_html_as_string( 'final/async-css/exclude/exclude' ),
			'config'   => [
				'use_default'  => true,
				'critical_css' => [
					'get_exclude_async_css' => [
						'https://example.org/file1.css',
					],
				],
			],
		],

		'exclude: remove extra spaces in attribute value' => [
			'html'     => get_html_as_string( 'original/attribute-value-spaces-semicolon' ),
			'expected' => get_html_as_string( 'final/async-css/exclude/attribute-value-spaces-semicolon' ),
			'config'   => [
				'use_default'  => true,
				'critical_css' => [
					'get_exclude_async_css' => [
						'https://example.org/file2.css',
					],
				],
			],
		],

		'exclude: multiple stylesheets' => [
			'html'     => get_html_as_string( 'original/multiple-stylesheets' ),
			'expected' => get_html_as_string( 'final/async-css/exclude/multiple-stylesheets' ),
			'config'   => [
				'use_default'  => true,
				'critical_css' => [
					'get_exclude_async_css' => [
						'https://example.org/file2.css',
						'https://example.org/file4.css',
					],
				],
			],
		],

		// Get only <link> with rel="stylesheet".

		'get only links with rel="stylesheet"' => [
			'html'     => get_html_as_string( 'original/rel-preload-and-stylesheet' ),
			'expected' => get_html_as_string( 'final/async-css/rel-preload-and-stylesheet' ),
		],

		'shouldBailOutWhenCSSIsExcludedAndNoOtherLinksWithRelStylesheet' => [
			'html'     => <<<HTML
<!doctype html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="all">
	<link rel="preload" type="text/css" href="https://example.org/file2.css" media="all">
</head>
<body>
	<div>
		<h1>Testing</h1>
		<p>Testing excluding CSS links.</p>
	</div>
</body>
</html>
HTML
			,
			'expected' => <<<HTML
<!doctype html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="all">
	<link rel="preload" type="text/css" href="https://example.org/file2.css" media="all">
</head>
<body>
	<div>
		<h1>Testing</h1>
		<p>Testing excluding CSS links.</p>
	</div>
	<script>const wprRemoveCPCSS = () => { \$elem = document.getElementById( "rocket-critical-css" ); if ( \$elem ) { \$elem.remove(); } }; if ( window.addEventListener ) { window.addEventListener( "load", wprRemoveCPCSS ); } else if ( window.attachEvent ) { window.attachEvent( "onload", wprRemoveCPCSS ); }</script>
</body>
</html>
HTML
			,
			'config'   => [
				'use_default'  => true,
				'critical_css' => [
					'get_exclude_async_css' => [
						'https://example.org/file1.css',
					],
				],
			],
		],

		// Check for empty href.
		'shouldNotProcessWhenHrefIsEmpty'                                => [
			'html'     => <<<HTML
<!doctype html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="" media="all">
	<link rel="stylesheet" type="text/css" href=" " media="all">
	<link rel="stylesheet" type="text/css" href='' media="all">
	<link rel='stylesheet' type="text/css" media="all">
</head>
<body>
	<div>
		<h1>Testing</h1>
		<p>Testing when CSS links do not have a href or href is empty.</p>
	</div>
</body>
</html>
HTML
			,
			'expected' => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="" media="all">
	<link rel="stylesheet" type="text/css" href="" media="all">
	<link rel="stylesheet" type="text/css" href="" media="all">
	<link rel="stylesheet" type="text/css" media="all">
</head>
<body>
	<div>
		<h1>Testing</h1>
		<p>Testing when CSS links do not have a href or href is empty.</p>
	</div>
	<script>const wprRemoveCPCSS = () => { \$elem = document.getElementById( "rocket-critical-css" ); if ( \$elem ) { \$elem.remove(); } }; if ( window.addEventListener ) { window.addEventListener( "load", wprRemoveCPCSS ); } else if ( window.attachEvent ) { window.attachEvent( "onload", wprRemoveCPCSS ); }</script>
</body>
</html>
HTML
			,
		],

		// Malformed HTML
		'malformed: missing <head> tag' => [
			'html'     => get_html_as_string( 'original/malformed-opening-head' ),
			'expected' => get_html_as_string( 'final/async-css/malformed-opening-head' ),
		],

		'malformed: missing </head> tag' => [
			'html'     => get_html_as_string( 'original/malformed-closing-head' ),
			'expected' => get_html_as_string( 'final/async-css/malformed-closing-head' ),
		],

		'malformed: missing <body> tag' => [
			'html'     => get_html_as_string( 'original/malformed-opening-body' ),
			'expected' => get_html_as_string( 'final/async-css/malformed-opening-body' ),
		],

		// Note: the remove CPCSS script does not get applied because the </body> does not exist when that code runs.
		'malformed: missing </body> tag' => [
			'html'     => get_html_as_string( 'original/malformed-closing-body' ),
			'expected' => get_html_as_string( 'final/async-css/malformed-closing-body' ),
		],

		'malformed: missing <html> tag' => [
			'html'     => get_html_as_string( 'original/malformed-opening-html' ),
			'expected' => get_html_as_string( 'final/async-css/malformed-opening-html' ),
		],

		'malformed: missing </html> tag' => [
			'html'     => get_html_as_string( 'original/malformed-closing-html' ),
			'expected' => get_html_as_string( 'final/async-css/malformed-closing-html' ),
		],

		// Note: the remove CPCSS script does not get applied because the </body> does not exist when that code runs.
		'malformed: missing </body> and </html> tags' => [
			'html'     => get_html_as_string( 'original/malformed-closing-body-html' ),
			'expected' => get_html_as_string( 'final/async-css/malformed-closing-body-html' ),
		],

		// Preserve malformed and invalid content in the <head>.

		'preserve <head>: text is not contained in a tag' => [
			'html'     => get_html_as_string( 'original/head-text-not-in-tag' ),
			'expected' => get_html_as_string( 'final/async-css/head-text-not-in-tag' ),
		],

		'preserve <head>: non-allowable nodes' => [
			'html'     => get_html_as_string( 'original/head-nonallowed' ),
			'expected' => get_html_as_string( 'final/async-css/head-nonallowed' ),
		],

		'preserve <head>: <noscript>' => [
			'html'     => get_html_as_string( 'original/head-noscript' ),
			'expected' => get_html_as_string( 'final/async-css/head-noscript' ),
		],

		'preserve script template: type attribute and HTML closing tags' => [
			'html'     => get_html_as_string( 'original/script-template' ),
			'expected' => get_html_as_string( 'final/async-css/script-template' ),
		],

		// Test encoding.

		'encoding: ar' => [
			'html'     => get_html_as_string( 'original/encoding-ar' ),
			'expected' => get_html_as_string( 'final/async-css/encoding-ar' ),
		],

		'encoding: el' => [
			'html'     => get_html_as_string( 'original/encoding-el' ),
			'expected' => get_html_as_string( 'final/async-css/encoding-el' ),
		],

		'encoding: en-us' => [
			'html'     => get_html_as_string( 'original/encoding-en-us' ),
			'expected' => get_html_as_string( 'final/async-css/encoding-en-us' ),
		],

		'encoding: hr' => [
			'html'     => get_html_as_string( 'original/encoding-hr' ),
			'expected' => get_html_as_string( 'final/async-css/encoding-hr' ),
		],

		'encoding: ja' => [
			'html'     => get_html_as_string( 'original/encoding-ja' ),
			'expected' => get_html_as_string( 'final/async-css/encoding-ja' ),
		],

		'skip stylesheets inside of <noscript>' => [
			'html'     => get_html_as_string( 'original/link-inside-noscript' ),
			'expected' => get_html_as_string( 'final/async-css/link-inside-noscript' ),
		],
	],
];
