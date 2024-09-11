<?php

return [
	'shouldReturnOptimizedTagWhenSingleTagGiven' => [
		'given' =>
			'<!doctype html>
			<html>
				<head>
					<title>Sample Page</title>
					<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450" type="text/css" media="all" />
					<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Josefin+Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica+One%3A400%2C600%2C700&#038;ver=7.3.2" type="text/css" media="all" />
					<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300|" type="text/css" media="all" />
					<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300" rel="stylesheet" property="stylesheet" type"text/css" media="all">
				</head>
				<body>
				</body>
			</html>'
		,
		'expected' =>
			'<!doctype html>
			<html>
				<head>
					<title>Sample Page</title>
					<link rel="preload" data-rocket-preload as="style" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /></noscript>
					<link rel="preload" data-rocket-preload as="style" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;display=swap" /></noscript>
				</head>
				<body>
				</body>
			</html>'
	],
	'shouldUseFilteredDisplayValue' => [
		'given' =>
			'<!doctype html>
			<html>
				<head>
					<title>Sample Page</title>
					<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450" type="text/css" media="all" />
					<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Josefin+Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica+One%3A400%2C600%2C700&#038;ver=7.3.2" type="text/css" media="all" />
					<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300|" type="text/css" media="all" />
					<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300" rel="stylesheet" property="stylesheet" type"text/css" media="all">
				</head>
				<body>
				</body>
			</html>'
		,
		'expected' =>
			'<!doctype html>
			<html>
				<head>
					<title>Sample Page</title>
					<link rel="preload" data-rocket-preload as="style" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=optional" /><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=optional" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=optional" /></noscript>
					<link rel="preload" data-rocket-preload as="style" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;display=optional" /><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;display=optional" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;display=optional" /></noscript>
				</head>
				<body>
				</body>
			</html>'
		,
		'filtered' => 'optional',
	],
	'shouldNotCombineMultipleTagsWithTextParam' => [
		'given' =>
			'<!doctype html>
			<html>
				<head>
					<title>Sample Page</title>
					<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450" type="text/css" media="all" />
					<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Comfortaa&text=Hello" type="text/css" media="all" />
					<link rel="stylesheet"  href="//fonts.googleapis.com/css?family=Josefin+Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica+One%3A400%2C600%2C700&#038;ver=7.3.2" type="text/css" media="all" />
					<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300|" type="text/css" media="all" />
					<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300" rel="stylesheet" property="stylesheet" type"text/css" media="all">
				</head>
				<body>
				</body>
			</html>'
		,
		'expected' =>
			'<!doctype html>
			<html>
				<head>
					<title>Sample Page</title>
					<link rel="preload" data-rocket-preload as="style" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /></noscript>
					<link rel="preload" data-rocket-preload as="style" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;display=swap" /></noscript>
					<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Comfortaa&text=Hello" type="text/css" media="all" />
				</head>
				<body>
				</body>
			</html>'
	],
	'shouldCombineMultipleTags' => [
		'given' =>
			'<!doctype html>
			<html>
				<head>
					<title>Sample Page</title>
					<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450" type="text/css" media="all" />
					<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Comfortaa" type="text/css" media="all" />
					<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Josefin+Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica+One%3A400%2C600%2C700&#038;ver=7.3.2" type="text/css" media="all" />
					<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300|" type="text/css" media="all" />
					<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300" rel="stylesheet" property="stylesheet" type"text/css" media="all">
				</head>
				<body>
				</body>
			</html>'
		,
		'expected' =>
			'<!doctype html>
			<html>
				<head>
					<title>Sample Page</title>
					<link rel="preload" data-rocket-preload as="style" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /></noscript>
					<link rel="preload" data-rocket-preload as="style" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;family=Comfortaa&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;family=Comfortaa&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;family=Comfortaa&#038;display=swap" /></noscript>
				</head>
				<body>
				</body>
			</html>'
	],
	'shouldCombineMultipleTagsWithMultipleFamiliesInTag' => [
		'given' =>
			'<!doctype html>
			<html>
				<head>
					<title>Sample Page</title>
					<link rel="preconnect" href="https://fonts.gstatic.com">
					<link href="https://fonts.googleapis.com/css2?family=Goldman:wght@700&family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&display=swap" rel="stylesheet">
					<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Comfortaa" type="text/css" media="all" />
					<link rel="stylesheet"  href="//fonts.googleapis.com/css?family=Josefin+Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica+One%3A400%2C600%2C700&#038;ver=7.3.2" type="text/css" media="all" />
					<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300|" type="text/css" media="all" />
					<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300" rel="stylesheet" property="stylesheet" type"text/css" media="all">
				</head>
				<body>
				</body>
			</html>'
		,
		'expected' =>
			'<!doctype html>
			<html>
				<head>
					<title>Sample Page</title>
					<link rel="preload" data-rocket-preload as="style" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /></noscript>
					<link rel="preload" data-rocket-preload as="style" href="https://fonts.googleapis.com/css2?family=Goldman:wght@700&#038;family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&#038;family=Comfortaa&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Goldman:wght@700&#038;family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&#038;family=Comfortaa&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Goldman:wght@700&#038;family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&#038;family=Comfortaa&#038;display=swap" /></noscript>
					<link rel="preconnect" href="https://fonts.gstatic.com">
				</head>
				<body>
				</body>
			</html>'
	],
	'shouldRemovePreconnectWhenNoGoogleFontsPresentOnPage' => [
		'given' =>
			'<!doctype html>
			<html>
				<head>
					<meta charset="UTF-8" />
					<title>Sample Page</title>
					<link href=\'https://fonts.gstatic.com\' crossorigin rel=\'preconnect\' />
					<link rel="prefetch" href="https://my-cdn.com" crossorigin>
					<style>h1 { color: red; }</style>
					</head>
				<body></body>
			</html>'
		,
		'expected' =>
			'<!doctype html>
			<html>
				<head>
					<meta charset="UTF-8" />
					<title>Sample Page</title>
					<link rel="prefetch" href="https://my-cdn.com" crossorigin>
					<style>h1 { color: red; }</style>
					</head>
				<body></body>
			</html>'
		,
	]
];
