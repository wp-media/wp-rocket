<?php

return [

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
		'expected_onload' => [
			"this.onload=null;this.media='all'",
		],
		'expected_html'   => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="all" onload="this.onload=null;this.media='all'">
</head>
<body>Content here</body>
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
		'expected_onload' => [
			"this.media='all';this.onload=null",
		],
		'expected_html'   => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="https://example.org/file1.css" media="print" onload="this.media='all';this.onload=null">
</head>
<body>Content here</body>
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
		'expected_onload' => [
			"this.media='all';this.onload=null",
		],
		'expected_html'   => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link type="text/css" onload="this.media='all';this.onload=null" rel="stylesheet" href="https://example.org/file1.css">
</head>
<body>Content here</body>
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
		'expected_onload' => [
			"this.media='all';this.onload=null",
			"this.rel='stylesheet';this.onload=null;this.media='all'",
		],
		'expected_html'   => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link onload="this.media='all';this.onload=null" rel="stylesheet" type="text/css" href="https://example.org/file1.css">
	<link onload="this.rel='stylesheet';this.onload=null;this.media='all'" href="https://example.org/file2.css" type="text/css" rel="stylesheet">
</head>
<body>Content here</body>
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
	<link onload=" yetAnotherFunction(this, 0); this.media='all' " rel="stylesheet" type="text/css" href="https://example.org/file3.css">
	<!-- single quotes -->
	<link onload='  console.log("Hello"); this.media="all";  ' rel="stylesheet" type='text/css' href='https://example.org/file4.css'>
</head>
<body>Content here</body>
</html>
HTML
		,
		'expected_onload' => [
			"someFunction();this.onload=null;this.media='all'",
			"anotherFunction(this);this.onload=null;this.media='all'",
			"yetAnotherFunction(this, 0);this.media='all';this.onload=null",
			"console.log('Hello');this.media='all';this.onload=null",
		],
		'expected_html'   => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link onload="someFunction();this.onload=null;this.media='all'" rel="stylesheet" type="text/css" href="https://example.org/file1.css">
	<link onload="anotherFunction(this);this.onload=null;this.media='all'" rel="stylesheet" type="text/css" href="https://example.org/file2.css">
	<link onload="yetAnotherFunction(this, 0);this.media='all';this.onload=null" rel="stylesheet" type="text/css" href="https://example.org/file3.css">
	<!-- single quotes -->
	<link onload="console.log('Hello');this.media='all';this.onload=null" rel="stylesheet" type="text/css" href="https://example.org/file4.css">
</head>
<body>Content here</body>
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
		'expected_onload' => [
			"this.rel='stylesheet';this.onload=null;this.media='screen'",
			"this.rel='stylesheet';this.onload=null;this.media='screen and (max-width: 600px)'",
			"this.onload=null;this.media='all'",
			"this.rel='stylesheet';this.onload=null;this.media='screen and (max-width: 800px)'",
		],
		'expected_html'   => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<link rel="preload" type="text/css" href="https://example.org/file1.css" media="screen" onload="this.rel='stylesheet';this.onload=null;this.media='screen'">
	<link rel="preload" type="text/css" href="https://example.org/file2.css" media="screen and (max-width: 600px)" onload="this.rel='stylesheet';this.onload=null;this.media='screen and (max-width: 600px)'">
	<link rel="preload" type="text/css" href="https://example.org/file3.css" media="print" onload="this.onload=null;this.media='all'">
	<!-- single quotes -->
	<link rel="preload" type="text/css" href="https://example.org/file4.css" media="screen and (max-width: 800px)" onload="this.rel='stylesheet';this.onload=null;this.media='screen and (max-width: 800px)'">
</head>
<body>Content here</body>
</html>

HTML
		,
	],
];
