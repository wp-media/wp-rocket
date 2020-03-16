<?php

// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet

return [

	// Without Subsets.
	[
		// Test Data: Original HTML.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link rel="stylesheet" id="dt-web-fonts-css"  href="//fonts.googleapis.com/css?family=Josefin+Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica+One%3A400%2C600%2C700&#038;ver=7.3.2" type="text/css" media="all" />' .
				'<link rel="stylesheet" id="ultimate-google-fonts-css"  href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300|" type="text/css" media="all" />' .
				'<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300" rel="stylesheet" property="stylesheet" type"text/css" media="all">' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		// Expected: Combined HTML.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" />' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
	],

	// With Subsets.
	[
		// Test Data: Original HTML.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link rel="stylesheet" id="dt-web-fonts-css"  href="https://fonts.googleapis.com/css?family=Lato:100,300,400,600,700,900%7COpen+Sans:700,300,600,400%7CRaleway:900%7CPlayfair+Display%7C&#038;ver=9adfdbd43f39a234a09de4319e14b851" type="text/css" media="all" />' .
				'<link rel="stylesheet" id="ultimate-google-fonts-css"  href="https://fonts.googleapis.com/css?family=Open+Sans%7CJockey+One:400&#038;subset=latin&#038;ver=1549273751" type="text/css" media="all" />' .
				'<link href="https://fonts.googleapis.com/css?family=Abril+Fatface:regular&#038;ver=9adfdbd43f39a234a09de4319e14b851" rel="stylesheet" property="stylesheet" type="text/css" media="all">' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		// Expected: Combined HTML.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato%3A100%2C300%2C400%2C600%2C700%2C900%7COpen%20Sans%3A700%2C300%2C600%2C400%7CRaleway%3A900%7CPlayfair%20Display%7COpen%20Sans%7CJockey%20One%3A400%7CAbril%20Fatface%3Aregular&#038;subset=latin&#038;display=swap" />' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
	],
	// Without Subsets & no ending |
	[
		// Test Data: Original HTML.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link rel="stylesheet" id="dt-web-fonts-css"  href="//fonts.googleapis.com/css?family=Josefin+Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica+One%3A400%2C600%2C700&#038;ver=7.3.2" type="text/css" media="all" />' .
				'<link rel="stylesheet" id="ultimate-google-fonts-css"  href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300" type="text/css" media="all" />' .
				'<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300" rel="stylesheet" property="stylesheet" type"text/css" media="all">' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		// Expected: Combined HTML.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica%20One%3A400%2C600%2C700%7CJosefin%20Sans%3Aregular%2C300%7CJosefin%20Sans%3A300&#038;display=swap" />' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
	],
	// Without Subsets & malformed URL (no family, but wrong query string)
	[
		// Test Data: Original HTML.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300" type="text/css" media="all" />' .
				'<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:regular,300" type="text/css" media="all" />' .
				'<link href="https://fonts.googleapis.com/css?fam=Error" rel="stylesheet" property="stylesheet" type"text/css" media="all">' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		// Expected: Combined HTML.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin%20Sans%3Aregular%2C300%7CLato%3Aregular%2C300&#038;display=swap" />' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
	],
	// Without Subsets & malformed URL (no family query string)
	[
		// Test Data: Original HTML.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300" type="text/css" media="all" />' .
				'<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:regular,300" type="text/css" media="all" />' .
				'<link href="https://fonts.googleapis.com/css?" rel="stylesheet" property="stylesheet" type"text/css" media="all">' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		// Expected: Combined HTML.
		'<html>' .
			'<head>' .
			'<title>Sample Page</title>' .
			'<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Josefin+Sans:regular,300" type="text/css" media="all" />' .
			'<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:regular,300" type="text/css" media="all" />' .
			'<link href="https://fonts.googleapis.com/css?" rel="stylesheet" property="stylesheet" type"text/css" media="all">' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
	],
	// Single Font no Display param
	[
		// Test Data: Original HTML.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300" rel="stylesheet" property="stylesheet" type"text/css" media="all">' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		// Expected: Combined HTML.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300&#038;display=swap" rel="stylesheet" property="stylesheet" type"text/css" media="all">' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
	],
	// Single Font Display param
	[
		// Test Data: Original HTML.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300&#038;display=auto" rel="stylesheet" property="stylesheet" type"text/css" media="all">' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		// Expected: Combined HTML.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300&#038;display=swap" rel="stylesheet" property="stylesheet" type"text/css" media="all">' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
	],
	// Single Font Invalid Display param
	[
		// Test Data: Original HTML.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300&display=invalid" rel="stylesheet" property="stylesheet" type"text/css" media="all">' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		// Expected: Combined HTML.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link href="https://fonts.googleapis.com/css?family=Josefin+Sans:300&#038;display=swap" rel="stylesheet" property="stylesheet" type"text/css" media="all">' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
	],
	// Single Font with decode
	[
		// Test Data: Original HTML.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link rel="stylesheet" id="dt-web-fonts-css"  href="//fonts.googleapis.com/css?family=Josefin+Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica+One%3A400%2C600%2C700&#038;ver=7.3.2" type="text/css" media="all" />' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		// Expected: Combined HTML.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link rel="stylesheet" id="dt-web-fonts-css"  href="//fonts.googleapis.com/css?family=Josefin+Sans%3A100%2C300%2C300italic%2C400%2C600%2C700%7CRoboto%3A100italic%2C300italic%2C400%2C500%2C600%2C700%7CUnica+One%3A400%2C600%2C700&#038;ver=7.3.2&#038;display=swap" type="text/css" media="all" />' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
	],
];
