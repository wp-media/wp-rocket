<?php

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
		'config'   => [
			'options'      => [ 'async_css' => 1 ],
			'critical_css' => [ 'get_current_page_critical_css' => 'something' ],
			'functions'    => [ 'is_rocket_post_excluded_option' => false ],
		],
	],

	'shouldSetDefaultsWhenNoOnload' => [
		'html'     => <<<HTML
<!doctype html>
<html lang="en-US">
<head>
	<meta charset="UTF-8" />
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
	<link rel="preload" type="text/css" href="https://example.org/file1.css" media="print" as="style" onload="this.onload=null;this.media='all';this.rel='stylesheet'">
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
	<link rel="preload" type="text/css" href="https://example.org/file1.css" media="print" onload="this.media='all';this.onload=null;this.rel='stylesheet'" as="style">
</head>
<body>Content here
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
	<link type="text/css" onload="this.media='all';this.onload=null;this.rel='stylesheet'" rel="preload" href="https://example.org/file1.css" as="style" media="print">
</head>
<body>Content here
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
	<link onload="this.media='all';this.onload=null;this.rel='stylesheet'" rel="preload" type="text/css" href="https://example.org/file1.css" as="style" media="print">
	<link onload="this.rel='stylesheet';this.onload=null;this.media='all'" href="https://example.org/file2.css" type="text/css" rel="preload" as="style" media="print">
</head>
<body>Content here
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
	<meta charset="UTF-8" />
	<link onload="someFunction();" rel="stylesheet" type="text/css" href="https://example.org/file1.css">
	<link onload=" anotherFunction(this) " rel="stylesheet" type="text/css" href="https://example.org/file2.css">
	<link onload=" yetAnotherFunction(this, 0); this.media='all' " rel="stylesheet" type="text/css" href="https://example.org/file3.css">
	<!-- single quotes -->
	<link rel="stylesheet" type='text/css' href='https://example.org/file4.css' onload='  console.log("Hello");  ' media="screen">
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
	<link onload="someFunction();this.onload=null;this.media='all';this.rel='stylesheet'" rel="preload" type="text/css" href="https://example.org/file1.css" as="style" media="print">
	<link onload="anotherFunction(this);this.onload=null;this.media='all';this.rel='stylesheet'" rel="preload" type="text/css" href="https://example.org/file2.css" as="style" media="print">
	<link onload="yetAnotherFunction(this, 0);this.media='all';this.onload=null;this.rel='stylesheet'" rel="preload" type="text/css" href="https://example.org/file3.css" as="style" media="print">
	<!-- single quotes -->
	<link rel="preload" type="text/css" href="https://example.org/file4.css" onload="console.log('Hello');this.onload=null;this.media='screen';this.rel='stylesheet'" media="print" as="style">
</head>
<body>Content here
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

	'shouldGetOriginalMediaAttribute' => [
		'html'     => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="preload" type="text/css" href="https://example.org/file1.css" media="screen" onload="this.rel='stylesheet'">
	<link rel="preload" type="text/css" href="https://example.org/file2.css" media="screen and (max-width: 600px)" onload="this.rel='stylesheet'">
	<link rel="preload" type="text/css" href="https://example.org/file3.css" media="print">
	<!-- single quotes -->
	<link rel='preload' type='text/css' href='https://example.org/file4.css' media='screen and (max-width: 800px)' onload='this.rel="stylesheet"'>
<body>Content here</body>
</html>
HTML
		,
		'expected' => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="preload" type="text/css" href="https://example.org/file1.css" media="print" onload="this.rel='stylesheet';this.onload=null;this.media='screen'" as="style">
	<link rel="preload" type="text/css" href="https://example.org/file2.css" media="print" onload="this.rel='stylesheet';this.onload=null;this.media='screen and (max-width: 600px)'" as="style">
	<link rel="preload" type="text/css" href="https://example.org/file3.css" media="print" as="style" onload="this.onload=null;this.media='all';this.rel='stylesheet'">
	<!-- single quotes -->
	<link rel="preload" type="text/css" href="https://example.org/file4.css" media="print" onload="this.rel='stylesheet';this.onload=null;this.media='screen and (max-width: 800px)'" as="style">
</head>
<body>Content here
<noscript>
	<link rel="preload" type="text/css" href="https://example.org/file1.css" media="screen" onload="this.rel='stylesheet'">
	<link rel="preload" type="text/css" href="https://example.org/file2.css" media="screen and (max-width: 600px)" onload="this.rel='stylesheet'">
	<link rel="preload" type="text/css" href="https://example.org/file3.css" media="print">
	<link rel="preload" type="text/css" href="https://example.org/file4.css" media="screen and (max-width: 800px)" onload='this.rel="stylesheet"'>
</noscript>
</body>
</html>
HTML
		,
	],
];
