<?php

return [
	'testShouldCombineGoogleFontsWithoutSubsets' => [
		'html' => '<html>
			<head>
				<title>Sample Page</title>
				<link rel="stylesheet" id="dt-web-fonts-css"  href="//fonts.googleapis.com/css?family=Josefin+Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica+One%3A400%2C600%2C700&#038;ver=7.3.2" type="text/css" media="all" />
				<link rel="stylesheet" id="ultimate-google-fonts-css"  href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300|" type="text/css" media="all" />
				<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300" rel="stylesheet" property="stylesheet" type"text/css" media="all">
			</head>
			<body>
			</body>
		</html>',
		'expected' => '<html>
			<head>
				<title>Sample Page</title><link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /></noscript>
			</head>
			<body>
			</body>
		</html>',
	],
	'testShouldUseFilteredDisplayValue' => [
		'html' => '<html>
			<head>
				<title>Sample Page</title>
				<link rel="stylesheet" id="dt-web-fonts-css"  href="//fonts.googleapis.com/css?family=Josefin+Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica+One%3A400%2C600%2C700&#038;ver=7.3.2" type="text/css" media="all" />
				<link rel="stylesheet" id="ultimate-google-fonts-css"  href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300|" type="text/css" media="all" />
				<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300" rel="stylesheet" property="stylesheet" type"text/css" media="all">
			</head>
			<body>
			</body>
		</html>',
		'expected' => '<html>
			<head>
				<title>Sample Page</title><link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=optional" /><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=optional" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=optional" /></noscript>
			</head>
			<body>
			</body>
		</html>',
		'filtered' => 'optional',
	],
	'testShouldCombineGoogleFontsWithSubsets' => [
		'html' => '<html>
			<head>
				<title>Sample Page</title>
				<link rel="stylesheet" id="dt-web-fonts-css"  href="https://fonts.googleapis.com/css?family=Lato:100,300,400,600,700,900%7COpen+Sans:700,300,600,400%7CRaleway:900%7CPlayfair+Display%7C&#038;ver=9adfdbd43f39a234a09de4319e14b851" type="text/css" media="all" />
				<link rel="stylesheet" id="ultimate-google-fonts-css"  href="https://fonts.googleapis.com/css?family=Open+Sans%7CJockey+One:400&#038;subset=latin&#038;ver=1549273751" type="text/css" media="all" />
				<link href="https://fonts.googleapis.com/css?family=Abril+Fatface:regular&#038;ver=9adfdbd43f39a234a09de4319e14b851" rel="stylesheet" property="stylesheet" type="text/css" media="all">
			</head>
			<body>
			</body>
		</html>',
		'expected' => '<html>
			<head>
				<title>Sample Page</title><link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Lato%3A100%2C300%2C400%2C600%2C700%2C900%7COpen%20Sans%3A700%2C300%2C600%2C400%7CRaleway%3A900%7CPlayfair%20Display%7COpen%20Sans%7CJockey%20One%3A400%7CAbril%20Fatface%3Aregular&#038;subset=latin&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato%3A100%2C300%2C400%2C600%2C700%2C900%7COpen%20Sans%3A700%2C300%2C600%2C400%7CRaleway%3A900%7CPlayfair%20Display%7COpen%20Sans%7CJockey%20One%3A400%7CAbril%20Fatface%3Aregular&#038;subset=latin&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato%3A100%2C300%2C400%2C600%2C700%2C900%7COpen%20Sans%3A700%2C300%2C600%2C400%7CRaleway%3A900%7CPlayfair%20Display%7COpen%20Sans%7CJockey%20One%3A400%7CAbril%20Fatface%3Aregular&#038;subset=latin&#038;display=swap" /></noscript>
			</head>
			<body>
			</body>
		</html>',
	],
	'testShouldCombineGoogleFontsWithoutSubsetsAndNoEnding|' => [
		'html' => '<html>
			<head>
				<title>Sample Page</title>
				<link rel="stylesheet" id="dt-web-fonts-css"  href="//fonts.googleapis.com/css?family=Josefin+Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica+One%3A400%2C600%2C700&#038;ver=7.3.2" type="text/css" media="all" />
				<link rel="stylesheet" id="ultimate-google-fonts-css"  href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300" type="text/css" media="all" />
				<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300" rel="stylesheet" property="stylesheet" type"text/css" media="all">
			</head>
			<body>
			</body>
		</html>',
		'expected' => '<html>
			<head>
				<title>Sample Page</title><link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /></noscript>
			</head>
			<body>
			</body>
		</html>',
	],
	'testShouldCombineGoogleFontsWithoutSubsetsWhenMalformedURL' => [
		'html' => '<html>
			<head>
				<title>Sample Page</title>
				<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300" type="text/css" media="all" />
				<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:regular,300" type="text/css" media="all" />
				<link href="https://fonts.googleapis.com/css?fam=Error" rel="stylesheet" property="stylesheet" type"text/css" media="all">
			</head>
			<body>
			</body>
		</html>',
		'expected' => '<html>
			<head>
				<title>Sample Page</title><link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3Aregular%2C300%7CLato%3Aregular%2C300&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3Aregular%2C300%7CLato%3Aregular%2C300&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3Aregular%2C300%7CLato%3Aregular%2C300&#038;display=swap" /></noscript>
			</head>
			<body>
			</body>
		</html>',
	],
	'testShouldCombineGoogleFontsWithSubsetsWhenMalformedURL' => [
		'html' => '<html>
			<head>
				<title>Sample Page</title>
				<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300" type="text/css" media="all" />
				<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:regular,300" type="text/css" media="all" />
				<link href="https://fonts.googleapis.com/css?" rel="stylesheet" property="stylesheet" type"text/css" media="all">
			</head>
			<body>
			</body>
		</html>',
		'expected' => '<html>
			<head>
			<title>Sample Page</title>
			<link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3Aregular%2C300%7CLato%3Aregular%2C300&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3Aregular%2C300%7CLato%3Aregular%2C300&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3Aregular%2C300%7CLato%3Aregular%2C300&#038;display=swap" /></noscript>
			<link href="https://fonts.googleapis.com/css?" rel="stylesheet" property="stylesheet" type"text/css" media="all">
			</head>
			<body>
			</body>
		</html>',
	],
	'testShouldOptimizeSingleGoogleFontsWhenNoParam' => [
		'html' => '<html>
			<head>
				<title>Sample Page</title>
				<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300" rel="stylesheet" property="stylesheet" type"text/css" media="all">
			</head>
			<body>
			</body>
		</html>',
		// Expected: Combined HTML.
		'expected' => '<html>
			<head>
				<title>Sample Page</title><link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A300&#038;display=swap" />
				<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A300&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A300&#038;display=swap" /></noscript>
			</head>
			<body>
			</body>
		</html>',
	],
	'testShouldOptimizeSingleGoogleFontsWhenParam' => [
		'html' => '<html>
			<head>
				<title>Sample Page</title>
				<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300&#038;display=auto" rel="stylesheet" property="stylesheet" type"text/css" media="all">
			</head>
			<body>
			</body>
		</html>',
		'expected' => '<html>
			<head>
				<title>Sample Page</title><link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A300&#038;display=swap" />
				<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A300&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A300&#038;display=swap" /></noscript>
			</head>
			<body>
			</body>
		</html>',
	],
	'testShouldOptimizeSingleGoogleFontsWhenInvalidParam' => [
		'html' => '<html>
			<head>
				<title>Sample Page</title>
				<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300&display=invalid" rel="stylesheet" property="stylesheet" type"text/css" media="all">
			</head>
			<body>
			</body>
		</html>',
		'expected' => '<html>
			<head>
				<title>Sample Page</title><link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A300&#038;display=swap" />
				<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A300&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A300&#038;display=swap" /></noscript>
			</head>
			<body>
			</body>
		</html>',
	],
	'testShouldOptimizeSingleGoogleFontsWhenEncodedParam' => [
		'html' => '<html>
			<head>
				<title>Sample Page</title>
				<link rel="stylesheet" id="dt-web-fonts-css"  href="//fonts.googleapis.com/css?family=Josefin+Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica+One%3A400%2C600%2C700&#038;ver=7.3.2" type="text/css" media="all" />
			</head>
			<body>
			</body>
		</html>',
		'expected' => '<html>
			<head>
				<title>Sample Page</title><link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700&#038;display=swap" />
				<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700&#038;display=swap" /></noscript>
			</head>
			<body>
			</body>
		</html>',
	],
	'testShouldCombineGoogleFontsWhenMultipleTitleTags' => [
		'html' => '<html>
			<head>
				<title>Sample Page</title>
				<link rel="stylesheet" id="dt-web-fonts-css"  href="//fonts.googleapis.com/css?family=Josefin+Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica+One%3A400%2C600%2C700&#038;ver=7.3.2" type="text/css" media="all" />
				<link rel="stylesheet" id="ultimate-google-fonts-css"  href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300|" type="text/css" media="all" />
				<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300" rel="stylesheet" property="stylesheet" type"text/css" media="all">
				<title>Sample Title 2</title>
			</head>
			<body>
			</body>
		</html>',
		'expected' => '<html>
			<head>
				<title>Sample Page</title><link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /></noscript>
				<title>Sample Title 2</title>
			</head>
			<body>
			</body>
		</html>',
	],
	'testShouldCombineGoogleFontsWhenTitleTagInsideBody' =>  [
		'html' => '<html>
			<head>
				<title>Sample Page</title>
				<link rel="stylesheet" id="dt-web-fonts-css"  href="//fonts.googleapis.com/css?family=Josefin+Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica+One%3A400%2C600%2C700&#038;ver=7.3.2" type="text/css" media="all" />
				<link rel="stylesheet" id="ultimate-google-fonts-css"  href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300|" type="text/css" media="all" />
				<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300" rel="stylesheet" property="stylesheet" type"text/css" media="all">
			</head>
			<body>
				<title>Sample Title 2</title>
			</body>
		</html>',
		'expected' => '<html>
			<head>
				<title>Sample Page</title><link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /></noscript>
			</head>
			<body>
				<title>Sample Title 2</title>
			</body>
		</html>',
	],
	'testShouldCombineGoogleFontsWhenTitleTagInsideSvgTag' => [
		'html' => '<html>
			<head>
				<title>Sample Page</title>
				<link rel="stylesheet" id="dt-web-fonts-css"  href="//fonts.googleapis.com/css?family=Josefin+Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica+One%3A400%2C600%2C700&#038;ver=7.3.2" type="text/css" media="all" />
				<link rel="stylesheet" id="ultimate-google-fonts-css"  href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300|" type="text/css" media="all" />
				<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300" rel="stylesheet" property="stylesheet" type"text/css" media="all">
			</head>
			<body>
				<!-- JetMenu Menu markup -->
				<div id="jet-mobile-menu-5e9eda6a03ac6" class="jet-mobile-menu jet-mobile-menu-widget" data-menu-id="3" data-menu-options=\'{"menuUniqId":"5e9eda6a03ac6","menuId":"3","mobileMenuId":false,"menuLocation":false,"menuLayout":"slide-out","togglePosition":"fixed-right","menuPosition":"right","headerTemplate":"3001","beforeTemplate":"0","afterTemplate":"3050","toggleClosedIcon":"<svg xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" viewBox=\"0 0 173.61 108.44\"><defs><linearGradient id=\"a\" x1=\"63.95\" y1=\"60.58\" x2=\"-114.86\" y2=\"62.47\" gradientUnits=\"userSpaceOnUse\"><stop offset=\"0.13\" stop-color=\"#fdc700\"></stop><stop offset=\"0.34\" stop-color=\"#f08f34\"></stop><stop offset=\"0.63\" stop-color=\"#e86031\"></stop><stop offset=\"0.69\" stop-color=\"#e64729\"></stop><stop offset=\"0.77\" stop-color=\"#e42b1f\"></stop><stop offset=\"0.84\" stop-color=\"#e31618\"></stop><stop offset=\"0.92\" stop-color=\"#e20a14\"></stop><stop offset=\"0.99\" stop-color=\"#e20613\"></stop></linearGradient><linearGradient id=\"b\" x1=\"63.94\" y1=\"59.65\" x2=\"-114.87\" y2=\"61.54\" xlink:href=\"#a\"></linearGradient></defs><title>logo-cacahuete</title><path d=\"M57,71.39c-6.63-12.14,1-22.57,1-22.57h0A28.47,28.47,0,1,0,4.51,35.16,28.35,28.35,0,0,0,8,48.82H8l0,0c.26.49.56,1,.85,1.42A21.75,21.75,0,0,1,9,71.39a1.4,1.4,0,0,0-.09.17,28.47,28.47,0,1,0,48.4.3C57.18,71.7,57.13,71.56,57,71.39Z\" transform=\"translate(112.17 -6.69)\" fill=\"url(#a)\"></path><path d=\"M-21,61A28.5,28.5,0,0,0-5.11,35.4,28.52,28.52,0,0,0-33.63,6.88a28.48,28.48,0,0,0-25,14.81,28.51,28.51,0,0,0-25-14.81A28.52,28.52,0,0,0-112.17,35.4,28.51,28.51,0,0,0-96.26,61,28.47,28.47,0,0,0-112.17,86.5,28.53,28.53,0,0,0-83.65,115a28.52,28.52,0,0,0,25-14.82,28.49,28.49,0,0,0,25,14.82A28.53,28.53,0,0,0-5.11,86.5,28.47,28.47,0,0,0-21,61Z\" transform=\"translate(112.17 -6.69)\" fill=\"url(#b)\"></path></svg>","toggleOpenedIcon":"","closeIcon":"<i class=\"mdi mdi-toggle-switch\"></i>","backIcon":"<i class=\"mdi mdi-toggle-switch-off\"></i>","dropdownIcon":"<i class=\"far fa-circle\"></i>","useBreadcrumb":true,"breadcrumbIcon":"","toggleText":"MENU","toggleLoader":true,"backText":"RETOUR","itemIconVisible":"true","itemBadgeVisible":"true","itemDescVisible":"false","loaderColor":"#FCC800","subTrigger":"item"}\'><MobileMenu :menu-options="menuOptions"></MobileMenu></div>
			</body>
		</html>',
		'expected' => '<html>
			<head>
				<title>Sample Page</title><link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" media="print" onload="this.media=\'all\'" /><noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" /></noscript>
			</head>
			<body>
				<!-- JetMenu Menu markup -->
				<div id="jet-mobile-menu-5e9eda6a03ac6" class="jet-mobile-menu jet-mobile-menu-widget" data-menu-id="3" data-menu-options=\'{"menuUniqId":"5e9eda6a03ac6","menuId":"3","mobileMenuId":false,"menuLocation":false,"menuLayout":"slide-out","togglePosition":"fixed-right","menuPosition":"right","headerTemplate":"3001","beforeTemplate":"0","afterTemplate":"3050","toggleClosedIcon":"<svg xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" viewBox=\"0 0 173.61 108.44\"><defs><linearGradient id=\"a\" x1=\"63.95\" y1=\"60.58\" x2=\"-114.86\" y2=\"62.47\" gradientUnits=\"userSpaceOnUse\"><stop offset=\"0.13\" stop-color=\"#fdc700\"></stop><stop offset=\"0.34\" stop-color=\"#f08f34\"></stop><stop offset=\"0.63\" stop-color=\"#e86031\"></stop><stop offset=\"0.69\" stop-color=\"#e64729\"></stop><stop offset=\"0.77\" stop-color=\"#e42b1f\"></stop><stop offset=\"0.84\" stop-color=\"#e31618\"></stop><stop offset=\"0.92\" stop-color=\"#e20a14\"></stop><stop offset=\"0.99\" stop-color=\"#e20613\"></stop></linearGradient><linearGradient id=\"b\" x1=\"63.94\" y1=\"59.65\" x2=\"-114.87\" y2=\"61.54\" xlink:href=\"#a\"></linearGradient></defs><title>logo-cacahuete</title><path d=\"M57,71.39c-6.63-12.14,1-22.57,1-22.57h0A28.47,28.47,0,1,0,4.51,35.16,28.35,28.35,0,0,0,8,48.82H8l0,0c.26.49.56,1,.85,1.42A21.75,21.75,0,0,1,9,71.39a1.4,1.4,0,0,0-.09.17,28.47,28.47,0,1,0,48.4.3C57.18,71.7,57.13,71.56,57,71.39Z\" transform=\"translate(112.17 -6.69)\" fill=\"url(#a)\"></path><path d=\"M-21,61A28.5,28.5,0,0,0-5.11,35.4,28.52,28.52,0,0,0-33.63,6.88a28.48,28.48,0,0,0-25,14.81,28.51,28.51,0,0,0-25-14.81A28.52,28.52,0,0,0-112.17,35.4,28.51,28.51,0,0,0-96.26,61,28.47,28.47,0,0,0-112.17,86.5,28.53,28.53,0,0,0-83.65,115a28.52,28.52,0,0,0,25-14.82,28.49,28.49,0,0,0,25,14.82A28.53,28.53,0,0,0-5.11,86.5,28.47,28.47,0,0,0-21,61Z\" transform=\"translate(112.17 -6.69)\" fill=\"url(#b)\"></path></svg>","toggleOpenedIcon":"","closeIcon":"<i class=\"mdi mdi-toggle-switch\"></i>","backIcon":"<i class=\"mdi mdi-toggle-switch-off\"></i>","dropdownIcon":"<i class=\"far fa-circle\"></i>","useBreadcrumb":true,"breadcrumbIcon":"","toggleText":"MENU","toggleLoader":true,"backText":"RETOUR","itemIconVisible":"true","itemBadgeVisible":"true","itemDescVisible":"false","loaderColor":"#FCC800","subTrigger":"item"}\'><MobileMenu :menu-options="menuOptions"></MobileMenu></div>
			</body>
		</html>',
	],
];
