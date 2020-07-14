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
		'functions'    => [ 'is_rocket_post_excluded_option' => false ],
	],

	'test_data' => [

		'shouldBailOutWhenAsyncCSSNotEnabled' => [
			'html'     => $base_html,
			'expected' => null,
			'config'   => [
				'options' => [ 'async_css' => 0 ],
			],
		],

		'shouldBailOutWhenCriticalCssReturnsNoCurrentPageCriticalCss' => [
			'html'     => $base_html,
			'expected' => null,
			'config'   => [
				'options'      => [
					'async_css'    => 1,
					'critical_css' => '',
				],
				'critical_css' => [ 'get_current_page_critical_css' => '' ],
			],
		],

		'shouldBailOutWhenNoCriticalCssOption' => [
			'html'     => $base_html,
			'expected' => null,
			'config'   => [
				'options'      => [
					'async_css'    => 1,
					'critical_css' => '',
				],
				'critical_css' => [ 'get_current_page_critical_css' => '' ],
			],
		],

		'shouldBailOutWhenPostExcludesAsyncCss' => [
			'html'     => $base_html,
			'expected' => null,
			'config'   => [
				'options'      => [ 'async_css' => 1 ],
				'critical_css' => [ 'get_current_page_critical_css' => 'something' ],
				'functions'    => [ 'is_rocket_post_excluded_option' => true ],
			],
		],

		'shouldBailOutAndReturnOriginalHTMLWhenNoCssLinksInHTML' => [
			'html'     => $base_html,
			'expected' => $base_html,
		],

		'async-css onload: no "onload" - use defaults' => [
			'html'     => get_html_as_string( 'original/no-onload' ),
			'expected' => get_html_as_string( 'final/modify-html/no-onload' ),
		],

		'async-css onload: preserve original this.media=all' => [
			'html'     => get_html_as_string( 'original/onload-media-all' ),
			'expected' => get_html_as_string( 'final/modify-html/onload-media-all' ),
		],

		'async-css onload: use default when original this.media=print' => [
			'html'     => get_html_as_string( 'original/onload-media-all' ),
			'expected' => get_html_as_string( 'final/modify-html/onload-media-all' ),
		],

		'async-css noscript: remove link "id" attribute' => [
			'html'     => get_html_as_string( 'original/noscript-remove-link-id' ),
			'expected' => get_html_as_string( 'final/modify-html/noscript-remove-link-id' ),
		],

		'async-css: remove extra spaces in attribute value' => [
			'html'     => get_html_as_string( 'original/attribute-value-spaces-semicolon' ),
			'expected' => get_html_as_string( 'final/modify-html/attribute-value-spaces-semicolon' ),
		],

		'async-css: preserve onload function' => [
			'html'     => get_html_as_string( 'original/onload-function' ),
			'expected' => get_html_as_string( 'final/modify-html/onload-function' ),
		],

		'async-css: apply to stylesheets in head and body' => [
			'html'     => get_html_as_string( 'original/multiple-stylesheets' ),
			'expected' => get_html_as_string( 'final/modify-html/multiple-stylesheets' ),
		],

		'async-css onload: escaped quotes in attribute value' => [
			'html'     => get_html_as_string( 'original/onload-escaped-quotes' ),
			'expected' => get_html_as_string( 'final/modify-html/onload-escaped-quotes' ),
		],

		// Exclude CSS URLs.

		'shouldBailOutWhenCSSIsExcluded' => [
			'html'     => <<<HTML
<!doctype html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="all">
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
			'config'   => [
				'use_default'  => true,
				'critical_css' => [
					'get_exclude_async_css' => [
						'https://example.org/file1.css',
					],
				],
			],
		],

		'shouldHandleWhitespaceAndEndingSemicolon' => [
			'html'     => <<<HTML
<!doctype html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link onload="  this.media = 'all'; this.onload = null; " rel="stylesheet" type="text/css" href="https://example.org/file1.css">
	<link onload=" this.rel = 'stylesheet'; " href="https://example.org/file2.css" type="text/css" rel="stylesheet">
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
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="preload" href="https://example.org/file1.css" as="style">
	<link onload="this.media='all';this.onload=null" rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="print">
	<link onload=" this.rel = 'stylesheet'; " href="https://example.org/file2.css" type="text/css" rel="stylesheet">
</head>
<body>
	<div>
		<h1>Testing</h1>
		<p>Testing excluding CSS links.</p>
	</div>
	<noscript>
		<link onload="  this.media = 'all'; this.onload = null; " rel="stylesheet" type="text/css" href="https://example.org/file1.css">
	</noscript>
</body>
</html>
HTML
			,
			'config'   => [
				'use_default'  => true,
				'critical_css' => [
					'get_exclude_async_css' => [
						'https://example.org/file2.css',
					],
				],
			],
		],

		'shouldGetAllLinksInHeadAndBody_butExclude' => [
			'html'     => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="screen" onload="this.rel='stylesheet'">
	<link rel="stylesheet" type="text/css" href="https://example.org/file2.css" media="screen and (max-width: 600px)">
	<link rel="stylesheet" type="text/css" href="https://example.org/file3.css" media="print">

<body>
	<div>
		<!-- single quotes -->
		<link rel='stylesheet' type='text/css' href='https://example.org/file4.css' media='screen and (max-width: 800px)'>
		<h1>Testing</h1>
		<p>Hello World</p>
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
	<link rel="preload" href="https://example.org/file1.css" as="style">
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="print" onload="this.rel='stylesheet';this.onload=null;this.media='screen'">
	<link rel="stylesheet" type="text/css" href="https://example.org/file2.css" media="screen and (max-width: 600px)">
	<link rel="preload" href="https://example.org/file3.css" as="style">
	<link rel="stylesheet" type="text/css" href="https://example.org/file3.css" media="print" onload="this.onload=null;this.media='all'">
</head>
<body>
	<div>
		<!-- single quotes -->
		<link rel="stylesheet" type="text/css" href="https://example.org/file4.css" media="screen and (max-width: 800px)">
		<h1>Testing</h1>
		<p>Hello World</p>
	</div>
	<noscript>
		<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="screen" onload="this.rel='stylesheet'">
		<link rel="stylesheet" type="text/css" href="https://example.org/file3.css" media="print">
	</noscript>
</body>
</html>
HTML
			,
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

		'shouldGetOnlyLinksWithRelStylesheet' => [
			'html'     => <<<HTML
<!doctype html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="preload" type="text/css" href="https://example.org/file1.css" media="screen" onload="this.rel='stylesheet'">
	<link rel="stylesheet" type="text/css" href="https://example.org/file2.css" media="screen and (max-width: 600px)">
	<link rel="stylesheet" type="text/css" href="https://example.org/file3.css" media="print">
</head>
<body>
	<div>
		<!-- single quotes -->
		<link rel='preload' type='text/css' href='https://example.org/file4.css' media='screen and (max-width: 800px)'>
		<link rel='stylesheet' type='text/css' href='https://example.org/file5.css' media='all' onload="console.log('I am one.');">
		<h1>Testing</h1>
		<p>Testing excluding CSS links.</p>
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
	<link rel="preload" type="text/css" href="https://example.org/file1.css" media="screen" onload="this.rel='stylesheet'">
	<link rel="preload" href="https://example.org/file2.css" as="style">
	<link rel="stylesheet" type="text/css" href="https://example.org/file2.css" media="print" onload="this.onload=null;this.media='screen and (max-width: 600px)'">
	<link rel="preload" href="https://example.org/file3.css" as="style">
	<link rel="stylesheet" type="text/css" href="https://example.org/file3.css" media="print" onload="this.onload=null;this.media='all'">
</head>
<body>
	<div>
		<!-- single quotes -->
		<link rel="preload" type="text/css" href="https://example.org/file4.css" media="screen and (max-width: 800px)">
		<link rel="preload" href="https://example.org/file5.css" as="style">
		<link rel="stylesheet" type="text/css" href="https://example.org/file5.css" media="print" onload="console.log('I am one.');this.onload=null;this.media='all'">
		<h1>Testing</h1>
		<p>Testing excluding CSS links.</p>
	</div>
	<noscript>
		<link rel="stylesheet" type="text/css" href="https://example.org/file2.css" media="screen and (max-width: 600px)">
		<link rel="stylesheet" type="text/css" href="https://example.org/file3.css" media="print">
		<link rel="stylesheet" type="text/css" href="https://example.org/file5.css" media="all" onload="console.log('I am one.');">
	</noscript>
</body>
</html>
HTML
			,
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
</body>
</html>
HTML
			,
		],

		// Malformed HTML
		'malformed: missing <head> tag' => [
			'html'     => get_html_as_string( 'original/malformed-opening-head' ),
			'expected' => get_html_as_string( 'final/modify-html/malformed-opening-head' ),
		],

		'malformed: missing </head> tag' => [
			'html'     => get_html_as_string( 'original/malformed-closing-head' ),
			'expected' => get_html_as_string( 'final/modify-html/malformed-closing-head' ),
		],

		'malformed: missing <body> tag' => [
			'html'     => get_html_as_string( 'original/malformed-opening-body' ),
			'expected' => get_html_as_string( 'final/modify-html/malformed-opening-body' ),
		],

		'malformed: missing </body> tag' => [
			'html'     => get_html_as_string( 'original/malformed-closing-body' ),
			'expected' => get_html_as_string( 'final/modify-html/malformed-closing-body' ),
		],

		'malformed: missing <html> tag' => [
			'html'     => get_html_as_string( 'original/malformed-opening-html' ),
			'expected' => get_html_as_string( 'final/modify-html/malformed-opening-html' ),
		],

		'malformed: missing </html> tag' => [
			'html'     => get_html_as_string( 'original/malformed-closing-html' ),
			'expected' => get_html_as_string( 'final/modify-html/malformed-closing-html' ),
		],

		'malformed: missing </body> and </html> tags' => [
			'html'     => get_html_as_string( 'original/malformed-closing-body-html' ),
			'expected' => get_html_as_string( 'final/modify-html/malformed-closing-body-html' ),
		],

		// Preserve malformed and invalid content in the <head>.

		'preserve <head>: text is not contained in a tag' => [
			'html'     => get_html_as_string( 'original/head-text-not-in-tag' ),
			'expected' => get_html_as_string( 'final/modify-html/head-text-not-in-tag' ),
		],

		'preserve <head>: non-allowable nodes' => [
			'html'     => get_html_as_string( 'original/head-nonallowed' ),
			'expected' => get_html_as_string( 'final/modify-html/head-nonallowed' ),
		],

		'preserve <head>: <noscript>' => [
			'html'     => get_html_as_string( 'original/head-noscript' ),
			'expected' => get_html_as_string( 'final/modify-html/head-noscript' ),
		],

		'preserve script template: type attribute and HTML closing tags' => [
			'html'     => get_html_as_string( 'original/script-template' ),
			'expected' => get_html_as_string( 'final/modify-html/script-template' ),
		],

		'preserve: placeholders' => [
			'html'     => get_html_as_string( 'original/placeholder' ),
			'expected' => get_html_as_string( 'final/modify-html/placeholder' ),
		],

		'preserve: conditional comments' => [
			'html'     => get_html_as_string( 'original/conditional-comments' ),
			'expected' => get_html_as_string( 'final/modify-html/conditional-comments' ),
		],

		'shouldHandleLargerWebPages' => [
			'html'     => get_html_as_string( 'original/twentyseventeen' ),
			'expected' => get_html_as_string( 'final/modify-html/twentyseventeen' ),
		],

		// Test encoding.

		'encoding: ar' => [
			'html'     => get_html_as_string( 'original/encoding-ar' ),
			'expected' => get_html_as_string( 'final/modify-html/encoding-ar' ),
		],

		'encoding: el' => [
			'html'     => get_html_as_string( 'original/encoding-el' ),
			'expected' => get_html_as_string( 'final/modify-html/encoding-el' ),
		],

		'encoding: en-us' => [
			'html'     => get_html_as_string( 'original/encoding-en-us' ),
			'expected' => get_html_as_string( 'final/modify-html/encoding-en-us' ),
		],

		'encoding: hr' => [
			'html'     => get_html_as_string( 'original/encoding-hr' ),
			'expected' => get_html_as_string( 'final/modify-html/encoding-hr' ),
		],

		'encoding: ja' => [
			'html'     => get_html_as_string( 'original/encoding-ja' ),
			'expected' => get_html_as_string( 'final/modify-html/encoding-ja' ),
		],

		'skip stylesheets inside of <noscript>' => [
			'html'     => get_html_as_string( 'original/link-inside-noscript' ),
			'expected' => get_html_as_string( 'final/modify-html/link-inside-noscript' ),
		],
	],
];
