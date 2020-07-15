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

		'shouldSetDefaultsWhenNoOnload' => [
			'html'     => <<<HTML
<!doctype html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="all">
</head>
<body>Content here</body>
</html>
HTML
			,
			'expected' => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="preload" href="https://example.org/file1.css" as="style">
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="print" onload="this.onload=null;this.media='all'">
</head>
<body>Content here
<script>const wprRemoveCPCSS = () => { \$elem = document.getElementById( "rocket-critical-css" ); if ( \$elem ) { \$elem.remove(); } }; if ( window.addEventListener ) { window.addEventListener( "load", wprRemoveCPCSS ); } else if ( window.attachEvent ) { window.attachEvent( "onload", wprRemoveCPCSS ); }</script>
<noscript>
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="all">
</noscript>
</body>
</html>
HTML
			,
		],

		'shouldIncludeOriginalMedia' => [
			'html'     => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="print" onload="this.media='all'">
</head>
<body>Content here</body>
</html>
HTML
			,
			'expected' => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="preload" href="https://example.org/file1.css" as="style">
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="print" onload="this.media='all';this.onload=null">
</head>
<body>Content here
<script>const wprRemoveCPCSS = () => { \$elem = document.getElementById( "rocket-critical-css" ); if ( \$elem ) { \$elem.remove(); } }; if ( window.addEventListener ) { window.addEventListener( "load", wprRemoveCPCSS ); } else if ( window.attachEvent ) { window.attachEvent( "onload", wprRemoveCPCSS ); }</script>
<noscript>
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="print" onload="this.media='all'">
</noscript>
</body>
</html>
HTML
			,
		],

		'shouldUseDefaultMediaWhenOriginalIsPrint' => [
			'html'     => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link type="text/css" onload="this.media='print'" rel="stylesheet" href="https://example.org/file1.css">
</head>
<body>Content here</body>
</html>
HTML
			,
			'expected' => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="preload" href="https://example.org/file1.css" as="style">
	<link type="text/css" onload="this.media='all';this.onload=null" rel="stylesheet" href="https://example.org/file1.css" media="print">
</head>
<body>Content here
<script>const wprRemoveCPCSS = () => { \$elem = document.getElementById( "rocket-critical-css" ); if ( \$elem ) { \$elem.remove(); } }; if ( window.addEventListener ) { window.addEventListener( "load", wprRemoveCPCSS ); } else if ( window.attachEvent ) { window.attachEvent( "onload", wprRemoveCPCSS ); }</script>
<noscript>
	<link type="text/css" onload="this.media='print'" rel="stylesheet" href="https://example.org/file1.css">
</noscript>
</body>
</html>
HTML
			,
		],

		'shouldRemoveIDAttributeInNoScript' => [
			'html'     => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link id="stylesheet1" type="text/css" onload="this.media='print'" rel="stylesheet" href="https://example.org/file1.css">
</head>
<body>Content here</body>
</html>
HTML
			,
			'expected' => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="preload" href="https://example.org/file1.css" as="style">
	<link id="stylesheet1" type="text/css" onload="this.media='all';this.onload=null" rel="stylesheet" href="https://example.org/file1.css" media="print">
</head>
<body>Content here
	<script>const wprRemoveCPCSS = () => { \$elem = document.getElementById( "rocket-critical-css" ); if ( \$elem ) { \$elem.remove(); } }; if ( window.addEventListener ) { window.addEventListener( "load", wprRemoveCPCSS ); } else if ( window.attachEvent ) { window.attachEvent( "onload", wprRemoveCPCSS ); }</script>
	<noscript>
		<link type="text/css" onload="this.media='print'" rel="stylesheet" href="https://example.org/file1.css">
	</noscript>
</body>
</html>
HTML
			,
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
<body>Content here</body>
</html>
HTML
			,
			'expected' => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="preload" href="https://example.org/file1.css" as="style">
	<link onload="this.media='all';this.onload=null" rel="preload" type="text/css" href="https://example.org/file1.css"  media="print">
	<link rel="preload" href="https://example.org/file2.css" as="style">
	<link onload="this.rel='stylesheet';this.onload=null;this.media='all'" href="https://example.org/file2.css" type="text/css" rel="stylesheet" media="print">
</head>
<body>Content here
<script>const wprRemoveCPCSS = () => { \$elem = document.getElementById( "rocket-critical-css" ); if ( \$elem ) { \$elem.remove(); } }; if ( window.addEventListener ) { window.addEventListener( "load", wprRemoveCPCSS ); } else if ( window.attachEvent ) { window.attachEvent( "onload", wprRemoveCPCSS ); }</script>
<noscript>
	<link onload="  this.media = 'all'; this.onload = null; " rel="stylesheet" type="text/css" href="https://example.org/file1.css">
	<link onload=" this.rel = 'stylesheet'; " href="https://example.org/file2.css" type="text/css" rel="stylesheet">
</noscript>
</body>
</html>
HTML
			,
		],

		'shouldRetainFunctions' => [
			'html'     => <<<HTML
<!doctype html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link onload="someFunction();" rel="stylesheet" type="text/css" href="https://example.org/file1.css">
	<link onload=" anotherFunction(this) " rel="stylesheet" type="text/css" href="https://example.org/file2.css">
</head>
<body>
	<link onload=" yetAnotherFunction(this, 0); this.media='all' " rel="stylesheet" type="text/css" href="https://example.org/file3.css">
	<div>
		<h1>Testing</h1>
		<!-- single quotes -->
		<link rel="stylesheet" type='text/css' href='https://example.org/file4.css' onload='  console.log("Hello");  ' media="screen">
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
	<link onload="someFunction();this.onload=null;this.media='all'" rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="print">
	<link rel="preload" href="https://example.org/file2.css" as="style">
	<link onload="anotherFunction(this);this.onload=null;this.media='all'" rel="stylesheet" type="text/css" href="https://example.org/file2.css" media="print">
</head>
<body>
	<link rel="preload" href="https://example.org/file3.css" as="style">
	<link onload="yetAnotherFunction(this, 0);this.media='all';this.onload=null" rel="stylesheet" type="text/css" href="https://example.org/file3.css" media="print">
	<div>
		<h1>Testing</h1>
		<!-- single quotes -->
		<link rel="preload" href="https://example.org/file4.css" as="style">
		<link rel="stylesheet" type="text/css" href="https://example.org/file4.css" onload="console.log('Hello');this.onload=null;this.media='screen'" media="print">
		<p>Hello World</p>
	</div>
	<script>const wprRemoveCPCSS = () => { \$elem = document.getElementById( "rocket-critical-css" ); if ( \$elem ) { \$elem.remove(); } }; if ( window.addEventListener ) { window.addEventListener( "load", wprRemoveCPCSS ); } else if ( window.attachEvent ) { window.attachEvent( "onload", wprRemoveCPCSS ); }</script>
	<noscript>
		<link onload="someFunction();" rel="stylesheet" type="text/css" href="https://example.org/file1.css">
		<link onload=" anotherFunction(this) " rel="stylesheet" type="text/css" href="https://example.org/file2.css">
		<link onload=" yetAnotherFunction(this, 0); this.media='all' " rel="stylesheet" type="text/css" href="https://example.org/file3.css">
		<link rel="stylesheet" type="text/css" href="https://example.org/file4.css" onload='  console.log("Hello");  ' media="screen">
	</noscript>
</body>
</html>
HTML
			,
		],

		'shouldGetAllLinksInHeadAndBody' => [
			'html'     => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="screen" onload="this.rel='stylesheet'">
	<link rel="stylesheet" type="text/css" href="https://example.org/file2.css" media="screen and (max-width: 600px)" onload="this.rel='stylesheet'">
	<link rel="stylesheet" type="text/css" href="https://example.org/file3.css" media="print">
<body>
	<div>
		<!-- single quotes -->
		<link rel='stylesheet' type='text/css' href='https://example.org/file4.css' media='screen and (max-width: 800px)' onload='this.rel="stylesheet"'>
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
	<link rel="preload" href="https://example.org/file2.css" as="style">
	<link rel="stylesheet" type="text/css" href="https://example.org/file2.css" media="print" onload="this.rel='stylesheet';this.onload=null;this.media='screen and (max-width: 600px)'">
	<link rel="preload" href="https://example.org/file3.css" as="style">
	<link rel="stylesheet" type="text/css" href="https://example.org/file3.css" media="print" onload="this.onload=null;this.media='all'">
</head>
<body>
	<div>
		<!-- single quotes -->
		<link rel="preload" href="https://example.org/file4.css" as="style">
		<link rel="stylesheet" type="text/css" href="https://example.org/file4.css" media="print" onload="this.rel='stylesheet';this.onload=null;this.media='screen and (max-width: 800px)'">
		<h1>Testing</h1>
		<p>Hello World</p>
	</div>
	<script>const wprRemoveCPCSS = () => { \$elem = document.getElementById( "rocket-critical-css" ); if ( \$elem ) { \$elem.remove(); } }; if ( window.addEventListener ) { window.addEventListener( "load", wprRemoveCPCSS ); } else if ( window.attachEvent ) { window.attachEvent( "onload", wprRemoveCPCSS ); }</script>
	<noscript>
		<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="screen" onload="this.rel='stylesheet'">
		<link rel="stylesheet" type="text/css" href="https://example.org/file2.css" media="screen and (max-width: 600px)" onload="this.rel='stylesheet'">
		<link rel="stylesheet" type="text/css" href="https://example.org/file3.css" media="print">
		<link rel="stylesheet" type="text/css" href="https://example.org/file4.css" media="screen and (max-width: 800px)" onload='this.rel="stylesheet"'>
	</noscript>
</body>
</html>
HTML
			,
		],

		// Handle escaped quotes in "onload" attribute.

		'shouldHandleEscapedQuotesInOnloadAttribute' => [
			'html'     => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="all" baz="boo" onload="this.baz=\'test\'">
	<link rel="stylesheet" type="text/css" href="https://example.org/file2.css" media="all" baz="boo" onload='this.baz=\"test\"'>
</head>
<body>
	<div>
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
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="print" baz="boo" onload="this.baz='test';this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/file2.css" as="style">
	<link rel="stylesheet" type="text/css" href="https://example.org/file2.css" media="print" baz="boo" onload="this.baz='test';this.onload=null;this.media='all'">
</head>
<body>
	<div>
		<h1>Testing</h1>
		<p>Hello World</p>
	</div>
	<script>const wprRemoveCPCSS = () => { \$elem = document.getElementById( "rocket-critical-css" ); if ( \$elem ) { \$elem.remove(); } }; if ( window.addEventListener ) { window.addEventListener( "load", wprRemoveCPCSS ); } else if ( window.attachEvent ) { window.attachEvent( "onload", wprRemoveCPCSS ); }</script>
	<noscript>
		<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="all" baz="boo" onload="this.baz=\'test\'">
		<link rel="stylesheet" type="text/css" href="https://example.org/file2.css" media="all" baz="boo" onload='this.baz=\"test\"'>
	</noscript>
</body>
</html>
HTML
			,
		],

		// Malformed HTML

		'shouldSetDefaultsWhenNoOnload_whenHTMLIsMalformed' => [
			'html'     => <<<HTML
<!doctype html>
<html lang="en-US">
<head>
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="all">

<body>Content here
</html>
HTML
			,
			'expected' => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<link rel="preload" href="https://example.org/file1.css" as="style">
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="print" onload="this.onload=null;this.media='all'">
</head>
<body>Content here
<noscript>
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="all">
</noscript>
</body>
</html>
HTML
			,
		],

		'shouldIncludeOriginalMedia_whenHTMLIsMalformed' => [
			'html'     => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<link rel="stylesheet" huh type="text/css" href="https://example.org/file1.css" media="print" abc='123' onload="this.media='all'" />
</head>
<body>
	<div>
		<h1>Testing</h1>
		<p>Nested<p>Paragrah</p></p>
</body>
HTML
			,
			'expected' => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<link rel="preload" href="https://example.org/file1.css" as="style">
	<link rel="stylesheet" huh type="text/css" href="https://example.org/file1.css" media="print" abc="123" onload="this.media='all';this.onload=null">
</head>
<body>
	<div>
		<h1>Testing</h1>
		<p>Nested</p>
		<p>Paragrah</p>
		<script>const wprRemoveCPCSS = () => { \$elem = document.getElementById( "rocket-critical-css" ); if ( \$elem ) { \$elem.remove(); } }; if ( window.addEventListener ) { window.addEventListener( "load", wprRemoveCPCSS ); } else if ( window.attachEvent ) { window.attachEvent( "onload", wprRemoveCPCSS ); }</script>
	</div>
	<noscript>
		<link rel="stylesheet" huh type="text/css" href="https://example.org/file1.css" media="print" abc="123" onload="this.media='all'">
	</noscript>
</body>
</html>
HTML
			,
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
	<script>const wprRemoveCPCSS = () => { \$elem = document.getElementById( "rocket-critical-css" ); if ( \$elem ) { \$elem.remove(); } }; if ( window.addEventListener ) { window.addEventListener( "load", wprRemoveCPCSS ); } else if ( window.attachEvent ) { window.attachEvent( "onload", wprRemoveCPCSS ); }</script>
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
	<script>const wprRemoveCPCSS = () => { \$elem = document.getElementById( "rocket-critical-css" ); if ( \$elem ) { \$elem.remove(); } }; if ( window.addEventListener ) { window.addEventListener( "load", wprRemoveCPCSS ); } else if ( window.attachEvent ) { window.attachEvent( "onload", wprRemoveCPCSS ); }</script>
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
	<script>const wprRemoveCPCSS = () => { \$elem = document.getElementById( "rocket-critical-css" ); if ( \$elem ) { \$elem.remove(); } }; if ( window.addEventListener ) { window.addEventListener( "load", wprRemoveCPCSS ); } else if ( window.attachEvent ) { window.attachEvent( "onload", wprRemoveCPCSS ); }</script>
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

