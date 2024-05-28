<?php

return [
	'shouldReturnGivenHTMLWhenNoRelevantTags' => [
		'given' =>
			'<!doctype html>
			<html>
				<head>
					<title>Sample Page</title>
					<link rel="stylesheet" id="dt-web-fonts-css" href="https://example.org/path-to-font" type="text/css" media="all" />
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
					<link rel="stylesheet" id="dt-web-fonts-css" href="https://example.org/path-to-font" type="text/css" media="all" />
				</head>
				<body>
				</body>
			</html>'
	],
	'shouldReturnTagWithFontDisplayWhenSingleTagGiven' => [
		'given' =>
			'<!doctype html>
			<html>
				<head>
					<title>Sample Page</title>
					<link rel="stylesheet" id="dt-web-fonts-css" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450" type="text/css" media="all" />
				</head>
				<body>
				</body>
			</html>'
		,
		'expected' =>
			'<!doctype html>
			<html>
				<head>
					<title>Sample Page</title><link rel="preload" rocket-preload as="style" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;display=swap" />
					<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;display=swap" media="print" onload="this.media=\'all\'" />
					<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;display=swap" /></noscript>
				</head>
				<body>
				</body>
			</html>'
	],
	'shouldNotCombineMultipleTagsWithTextParam' => [
		'given' =>
			'<!doctype html>
			<html>
				<head>
					<title>Sample Page</title>
					<link rel="stylesheet" id="dt-web-fonts-css" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450" type="text/css" media="all" />
					<link rel="stylesheet" id="dt-more-fonts-css" href="https://fonts.googleapis.com/css2?family=Comfortaa&text=Hello" type="text/css" media="all" />
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
					<link rel="preload" rocket-preload as="style" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;display=swap" /></noscript>
					<link rel="stylesheet" id="dt-more-fonts-css" href="https://fonts.googleapis.com/css2?family=Comfortaa&text=Hello" type="text/css" media="all" />
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
					<link rel="stylesheet" id="dt-web-fonts-css" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450" type="text/css" media="all" />
					<link rel="stylesheet" id="dt-more-fonts-css" href="https://fonts.googleapis.com/css2?family=Comfortaa" type="text/css" media="all" />
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
					<link rel="preload" rocket-preload as="style" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;family=Comfortaa&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;family=Comfortaa&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&#038;family=Comfortaa&#038;display=swap" /></noscript>
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
					<link rel="stylesheet" id="dt-more-fonts-css" href="https://fonts.googleapis.com/css2?family=Comfortaa" type="text/css" media="all" />
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
					<link rel="preload" rocket-preload as="style" href="https://fonts.googleapis.com/css2?family=Goldman:wght@700&#038;family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&#038;family=Comfortaa&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Goldman:wght@700&#038;family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&#038;family=Comfortaa&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Goldman:wght@700&#038;family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&#038;family=Comfortaa&#038;display=swap" /></noscript>
					<link rel="preconnect" href="https://fonts.gstatic.com">
				</head>
				<body>
				</body>
			</html>'
	],
	'shouldReplaceAnotherFontDisplayValueWithSwap' => [
		'given' =>
			'<!doctype html>
			<html>
			<head>
			<title>Sample Page</title>
			<link rel="preconnect" href="https://fonts.gstatic.com">
			<link href="https://fonts.googleapis.com/css2?family=Goldman:wght@700&family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&display=auto" rel="stylesheet">
			<link rel="stylesheet" id="dt-more-fonts-css" href="https://fonts.googleapis.com/css2?family=Comfortaa" type="text/css" media="all" />
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
			<link rel="preload" rocket-preload as="style" href="https://fonts.googleapis.com/css2?family=Goldman:wght@700&#038;family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&#038;family=Comfortaa&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Goldman:wght@700&#038;family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&#038;family=Comfortaa&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Goldman:wght@700&#038;family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&#038;family=Comfortaa&#038;display=swap" /></noscript>
			<link rel="preconnect" href="https://fonts.gstatic.com">
			</head>
			<body>
			</body>
			</html>'
	],
	'shouldReplaceDisplayValueWithFilteredValue' => [
		'given' =>
			'<!doctype html>
			<html>
			<head>
			<title>Sample Page</title>
			<link rel="preconnect" href="https://fonts.gstatic.com">
			<link href="https://fonts.googleapis.com/css2?family=Goldman:wght@700&family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&display=auto" rel="stylesheet">
			<link rel="stylesheet" id="dt-more-fonts-css" href="https://fonts.googleapis.com/css2?family=Comfortaa" type="text/css" media="all" />
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
			<link rel="preload" rocket-preload as="style" href="https://fonts.googleapis.com/css2?family=Goldman:wght@700&#038;family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&#038;family=Comfortaa&#038;display=optional" /><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Goldman:wght@700&#038;family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&#038;family=Comfortaa&#038;display=optional" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Goldman:wght@700&#038;family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&#038;family=Comfortaa&#038;display=optional" /></noscript>
			<link rel="preconnect" href="https://fonts.gstatic.com">
			</head>
			<body>
			</body>
			</html>'
		,
		'filtered' => 'optional'
	],
];
