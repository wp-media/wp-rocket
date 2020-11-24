<?php

// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet

return [

	'shouldReturnGivenHTMLWhenNoRelevantTags' => [
		'given' => <<<GIVEN
<!doctype html>
<html>
	<head>
		<title>Sample Page</title>
		<link rel="stylesheet" id="dt-web-fonts-css" href="https://fonts.googleapis.com/css?family=some+previous+api+spec" type="text/css" media="all" />
	</head>
	<body>
	</body>
</html>
GIVEN
		,
		'expected' => <<<EXPECTED
<!doctype html>
<html>
	<head>
		<title>Sample Page</title>
		<link rel="stylesheet" id="dt-web-fonts-css" href="https://fonts.googleapis.com/css?family=some+previous+api+spec" type="text/css" media="all" />
	</head>
	<body>
	</body>
</html>
EXPECTED
	],

	'shouldReturnTagWithFontDisplayWhenSingleTagGiven' => [
		'given' => <<<GIVEN
<!doctype html>
<html>
	<head>
		<title>Sample Page</title>
		<link rel="stylesheet" id="dt-web-fonts-css" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450" type="text/css" media="all" />
	</head>
	<body>
	</body>
</html>
GIVEN
		,
		'expected' => <<<EXPECTED
<!doctype html>
<html>
	<head>
		<title>Sample Page</title>
		<link rel="stylesheet" id="dt-web-fonts-css" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&display=swap" type="text/css" media="all" />
	</head>
	<body>
	</body>
</html>
EXPECTED
	],

	'shouldNotCombineMultipleTagsWithTextParam' => [
		'given' => <<<GIVEN
<!doctype html>
<html>
	<head>
		<title>Sample Page</title>
		<link rel="stylesheet" id="dt-web-fonts-css" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450" type="text/css" media="all" />
		<link rel="stylesheet" id="dt-more-fonts-css" href="https://fonts.googleapis.com/css2?family=Comfortaa&text=Hello" type="text/css" media="all" />
	</head>
	<body>
	</body>
</html>
GIVEN
		,
		'expected' => <<<EXPECTED
<!doctype html>
<html>
	<head>
		<title>Sample Page</title><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&display=swap" />
		<link rel="stylesheet" id="dt-more-fonts-css" href="https://fonts.googleapis.com/css2?family=Comfortaa&text=Hello" type="text/css" media="all" />
	</head>
	<body>
	</body>
</html>
EXPECTED
	],

	'shouldCombineMultipleTags' => [
		'given' => <<<GIVEN
<!doctype html>
<html>
	<head>
		<title>Sample Page</title>
		<link rel="stylesheet" id="dt-web-fonts-css" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450" type="text/css" media="all" />
		<link rel="stylesheet" id="dt-more-fonts-css" href="https://fonts.googleapis.com/css2?family=Comfortaa" type="text/css" media="all" />
	</head>
	<body>
	</body>
</html>
GIVEN
		,
		'expected' => <<<EXPECTED
<!doctype html>
<html>
	<head>
		<title>Sample Page</title><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@450&family=Comfortaa&display=swap" />
	</head>
	<body>
	</body>
</html>
EXPECTED
	],

	'shouldCombineMultipleTagsWithMultipleFamiliesInTag' => [
		'given' => <<<GIVEN
<!doctype html>
<html>
	<head>
		<title>Sample Page</title>
		<link rel="preconnect" href="https://fonts.gstatic.com">
		<link href="https://fonts.googleapis.com/css2?family=Goldman:wght@700&family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&display=swap" rel="stylesheet">
		<link rel="stylesheet" id="dt-more-fonts-css" href="https://fonts.googleapis.com/css2?family=Comfortaa" type="text/css" media="all" />
	</head>
	<body>
	</body>
</html>
GIVEN
		,
		'expected' => <<<EXPECTED
<!doctype html>
<html>
	<head>
		<title>Sample Page</title><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Goldman:wght@700&family=Roboto:ital,wght@0,100;0,400;0,500;1,500;1,900&family=Comfortaa&display=swap" />
		<link rel="preconnect" href="https://fonts.gstatic.com">
	</head>
	<body>
	</body>
</html>
EXPECTED
	],

];