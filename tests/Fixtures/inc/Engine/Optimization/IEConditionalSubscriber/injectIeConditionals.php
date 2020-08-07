<?php

return [

	'shouldBailOutWhenNoConditionalTag' => [
		'html'         => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
</head>
<body></body>
</html>
HTML
		,
		'expected'     => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
</head>
<body></body>
</html>
HTML
		,
		'conditionals' => [],
	],

	'shouldInjectConditional' => [
		'html'         => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" id="dashicons-css" href="https://example.org/wp-includes/css/dashicons.min.css?ver=5.4.2" media="all">
	<link rel="stylesheet" id="twentyseventeen-style-css" href="https://example.org/wp-content/themes/twentyseventeen/style.css?ver=20190507" media="all">
	<!--{{WP_ROCKET_CONDITIONAL}}-->
	<script src="https://example.org/wp-includes/js/jquery/jquery.js?ver=1.12.4-wp"></script>
</head>
<body>
	<div id="page" class="site">
		<a class="skip-link screen-reader-text" href="#content">Skip to content</a>
	</div><!-- #page -->
</body>
</html>
HTML
		,
		'expected'     => <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" id="dashicons-css" href="https://example.org/wp-includes/css/dashicons.min.css?ver=5.4.2" media="all">
	<link rel="stylesheet" id="twentyseventeen-style-css" href="https://example.org/wp-content/themes/twentyseventeen/style.css?ver=20190507" media="all">
	<!--[if lt IE 9]>
	<link rel='stylesheet' id='twentyseventeen-ie8-css'  href='https://example.org/wp-content/themes/twentyseventeen/assets/css/ie8.css?ver=20161202' media='all' />
	<![endif]-->
	<script src="https://example.org/wp-includes/js/jquery/jquery.js?ver=1.12.4-wp"></script>
</head>
<body>
	<div id="page" class="site">
		<a class="skip-link screen-reader-text" href="#content">Skip to content</a>
	</div><!-- #page -->
</body>
</html>
HTML
		,
		'conditionals' => [
			<<<IE_LINK
	<!--[if lt IE 9]>
	<link rel='stylesheet' id='twentyseventeen-ie8-css'  href='https://example.org/wp-content/themes/twentyseventeen/assets/css/ie8.css?ver=20161202' media='all' />
	<![endif]-->
IE_LINK
			,
		],
	],

	'shouldInjectMultipleConditionals' => [
		'html'         => <<<HTML
<!DOCTYPE html>
<html lang="en-US" class="js svg background-fixed">
<head>
	<link rel="stylesheet" id="dashicons-css" href="https://example.org/wp-includes/css/dashicons.min.css?ver=5.4.2" media="all">
	<link rel="stylesheet" id="twentyseventeen-style-css" href="https://example.org/wp-content/themes/twentyseventeen/style.css?ver=20190507" media="all">
	<!--{{WP_ROCKET_CONDITIONAL}}-->
	<script src="https://example.org/wp-includes/js/jquery/jquery.js?ver=1.12.4-wp"></script>
	<script src="https://example.org/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1"></script>
	<!--{{WP_ROCKET_CONDITIONAL}}-->
	<link rel="https://api.w.org/" href="https://example.org/wp-json/">
</head>
<body class="home blog logged-in admin-bar wp-embed-responsive hfeed has-header-image has-sidebar colors-light customize-support">
	<div id="page" class="site">
		<a class="skip-link screen-reader-text" href="#content">Skip to content</a>
		<header id="masthead" class="site-header" role="banner">
		</header><!-- #masthead -->
	</div><!-- #page -->
</body>
</html>
HTML
		,
		'expected'     => <<<HTML
<!DOCTYPE html>
<html lang="en-US" class="js svg background-fixed">
<head>
	<link rel="stylesheet" id="dashicons-css" href="https://example.org/wp-includes/css/dashicons.min.css?ver=5.4.2" media="all">
	<link rel="stylesheet" id="twentyseventeen-style-css" href="https://example.org/wp-content/themes/twentyseventeen/style.css?ver=20190507" media="all">
	<!--[if lt IE 9]>
	<link rel='stylesheet' id='twentyseventeen-ie8-css'  href='https://example.org/wp-content/themes/twentyseventeen/assets/css/ie8.css?ver=20161202' media='all' />
	<![endif]-->
	<script src="https://example.org/wp-includes/js/jquery/jquery.js?ver=1.12.4-wp"></script>
	<script src="https://example.org/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1"></script>
	<!--[if lt IE 9]>
	<script src='https://example.org/wp-content/themes/twentyseventeen/assets/js/html5.js?ver=20161020'></script>
	<![endif]-->
	<link rel="https://api.w.org/" href="https://example.org/wp-json/">
</head>
<body class="home blog logged-in admin-bar wp-embed-responsive hfeed has-header-image has-sidebar colors-light customize-support">
	<div id="page" class="site">
		<a class="skip-link screen-reader-text" href="#content">Skip to content</a>
		<header id="masthead" class="site-header" role="banner">
		</header><!-- #masthead -->
	</div><!-- #page -->
</body>
</html>
HTML
		,
		'conditionals' => [
			<<<IE_LINK
	<!--[if lt IE 9]>
	<link rel='stylesheet' id='twentyseventeen-ie8-css'  href='https://example.org/wp-content/themes/twentyseventeen/assets/css/ie8.css?ver=20161202' media='all' />
	<![endif]-->
IE_LINK
			,
			<<<IE_SCRIPT
	<!--[if lt IE 9]>
	<script src='https://example.org/wp-content/themes/twentyseventeen/assets/js/html5.js?ver=20161020'></script>
	<![endif]-->
IE_SCRIPT
			,
		],
	],

	'shouldInjectMultipleConditionals_whenInHeadAndBody' => [
		'html'         => <<<HTML
<!DOCTYPE html>
<html lang="en-US" class="js svg background-fixed">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<title>WP Rocket QA - Testing page</title>
	<link rel="dns-prefetch" href="//fonts.googleapis.com">
	<link href="https://fonts.gstatic.com" crossorigin="" rel="preconnect">
	<link rel="alternate" type="application/rss+xml" title="WP Rocket QA » Feed" href="https://example.org/feed/">
	<link rel="alternate" type="application/rss+xml" title="WP Rocket QA » Comments Feed" href="https://example.org/comments/feed/">
	<style>
		img.wp-smiley,
		img.emoji {
			display: inline !important;
			border: none !important;
			box-shadow: none !important;
			height: 1em !important;
			width: 1em !important;
			margin: 0 .07em !important;
			vertical-align: -0.1em !important;
			background: none !important;
			padding: 0 !important;
		}
	</style>
	<link rel="preload" href="https://example.org/wp-includes/css/dashicons.min.css?ver=5.4.2" as="style">
	<link rel="stylesheet" id="dashicons-css" href="https://example.org/wp-includes/css/dashicons.min.css?ver=5.4.2" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-includes/css/admin-bar.min.css?ver=5.4.2" as="style">
	<link rel="stylesheet" id="admin-bar-css" href="https://example.org/wp-includes/css/admin-bar.min.css?ver=5.4.2" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=5.4.2" as="style">
	<link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=5.4.2" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-includes/css/dist/block-library/theme.min.css?ver=5.4.2" as="style">
	<link rel="stylesheet" id="wp-block-library-theme-css" href="https://example.org/wp-includes/css/dist/block-library/theme.min.css?ver=5.4.2" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-content/plugins/imagify/assets/css/admin-bar.min.css?ver=1.9.10" as="style">
	<link rel="stylesheet" id="imagify-admin-bar-css" href="https://example.org/wp-content/plugins/imagify/assets/css/admin-bar.min.css?ver=1.9.10" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://fonts.googleapis.com/css?family=Libre+Franklin%3A300%2C300i%2C400%2C400i%2C600%2C600i%2C800%2C800i&amp;subset=latin%2Clatin-ext&amp;display=fallback" as="style">
	<link rel="stylesheet" id="twentyseventeen-fonts-css" href="https://fonts.googleapis.com/css?family=Libre+Franklin%3A300%2C300i%2C400%2C400i%2C600%2C600i%2C800%2C800i&amp;subset=latin%2Clatin-ext&amp;display=fallback" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-content/themes/twentyseventeen/style.css?ver=20190507" as="style">
	<link rel="stylesheet" id="twentyseventeen-style-css" href="https://example.org/wp-content/themes/twentyseventeen/style.css?ver=20190507" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-content/themes/twentyseventeen/assets/css/blocks.css?ver=20190105" as="style">
	<link rel="stylesheet" id="twentyseventeen-block-style-css" href="https://example.org/wp-content/themes/twentyseventeen/assets/css/blocks.css?ver=20190105" media="print" onload="this.onload=null;this.media='all'">
	<!--{{WP_ROCKET_CONDITIONAL}}-->
	<script src="https://example.org/wp-includes/js/jquery/jquery.js?ver=1.12.4-wp"></script>
	<script src="https://example.org/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1"></script>
	<!--{{WP_ROCKET_CONDITIONAL}}-->
	<link rel="https://api.w.org/" href="https://example.org/wp-json/">
	<link rel="EditURI" type="application/rsd+xml" title="RSD" href="https://example.org/xmlrpc.php?rsd">
	<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="https://example.org/wp-includes/wlwmanifest.xml">
	<meta name="generator" content="WordPress 5.4.2">
	<style>.recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;}</style><style media="print">#wpadminbar { display:none; }</style>
	<style media="screen">
		html { margin-top: 32px !important; }
		* html body { margin-top: 32px !important; }
		@media screen and ( max-width: 782px ) {
			html { margin-top: 46px !important; }
			* html body { margin-top: 46px !important; }
		}
	</style>
</head>
<body class="home blog logged-in admin-bar wp-embed-responsive hfeed has-header-image has-sidebar colors-light customize-support">
	<!--{{WP_ROCKET_CONDITIONAL}}-->
	<div id="page" class="site">
		<a class="skip-link screen-reader-text" href="#content">Skip to content</a>
		<header id="masthead" class="site-header" role="banner">
		</header><!-- #masthead -->
	</div><!-- #page -->
</body>
</html>
HTML
		,
		'expected'     => <<<HTML
<!DOCTYPE html>
<html lang="en-US" class="js svg background-fixed">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<title>WP Rocket QA - Testing page</title>
	<link rel="dns-prefetch" href="//fonts.googleapis.com">
	<link href="https://fonts.gstatic.com" crossorigin="" rel="preconnect">
	<link rel="alternate" type="application/rss+xml" title="WP Rocket QA » Feed" href="https://example.org/feed/">
	<link rel="alternate" type="application/rss+xml" title="WP Rocket QA » Comments Feed" href="https://example.org/comments/feed/">
	<style>
		img.wp-smiley,
		img.emoji {
			display: inline !important;
			border: none !important;
			box-shadow: none !important;
			height: 1em !important;
			width: 1em !important;
			margin: 0 .07em !important;
			vertical-align: -0.1em !important;
			background: none !important;
			padding: 0 !important;
		}
	</style>
	<link rel="preload" href="https://example.org/wp-includes/css/dashicons.min.css?ver=5.4.2" as="style">
	<link rel="stylesheet" id="dashicons-css" href="https://example.org/wp-includes/css/dashicons.min.css?ver=5.4.2" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-includes/css/admin-bar.min.css?ver=5.4.2" as="style">
	<link rel="stylesheet" id="admin-bar-css" href="https://example.org/wp-includes/css/admin-bar.min.css?ver=5.4.2" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=5.4.2" as="style">
	<link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=5.4.2" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-includes/css/dist/block-library/theme.min.css?ver=5.4.2" as="style">
	<link rel="stylesheet" id="wp-block-library-theme-css" href="https://example.org/wp-includes/css/dist/block-library/theme.min.css?ver=5.4.2" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-content/plugins/imagify/assets/css/admin-bar.min.css?ver=1.9.10" as="style">
	<link rel="stylesheet" id="imagify-admin-bar-css" href="https://example.org/wp-content/plugins/imagify/assets/css/admin-bar.min.css?ver=1.9.10" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://fonts.googleapis.com/css?family=Libre+Franklin%3A300%2C300i%2C400%2C400i%2C600%2C600i%2C800%2C800i&amp;subset=latin%2Clatin-ext&amp;display=fallback" as="style">
	<link rel="stylesheet" id="twentyseventeen-fonts-css" href="https://fonts.googleapis.com/css?family=Libre+Franklin%3A300%2C300i%2C400%2C400i%2C600%2C600i%2C800%2C800i&amp;subset=latin%2Clatin-ext&amp;display=fallback" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-content/themes/twentyseventeen/style.css?ver=20190507" as="style">
	<link rel="stylesheet" id="twentyseventeen-style-css" href="https://example.org/wp-content/themes/twentyseventeen/style.css?ver=20190507" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-content/themes/twentyseventeen/assets/css/blocks.css?ver=20190105" as="style">
	<link rel="stylesheet" id="twentyseventeen-block-style-css" href="https://example.org/wp-content/themes/twentyseventeen/assets/css/blocks.css?ver=20190105" media="print" onload="this.onload=null;this.media='all'">
	<!--[if lt IE 9]>
	<link rel='stylesheet' id='twentyseventeen-ie8-css'  href='https://example.org/wp-content/themes/twentyseventeen/assets/css/ie8.css?ver=20161202' media='all' />
	<![endif]-->
	<script src="https://example.org/wp-includes/js/jquery/jquery.js?ver=1.12.4-wp"></script>
	<script src="https://example.org/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1"></script>
	<!--[if lt IE 9]>
	<script src='https://example.org/wp-content/themes/twentyseventeen/assets/js/html5.js?ver=20161020'></script>
	<![endif]-->
	<link rel="https://api.w.org/" href="https://example.org/wp-json/">
	<link rel="EditURI" type="application/rsd+xml" title="RSD" href="https://example.org/xmlrpc.php?rsd">
	<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="https://example.org/wp-includes/wlwmanifest.xml">
	<meta name="generator" content="WordPress 5.4.2">
	<style>.recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;}</style><style media="print">#wpadminbar { display:none; }</style>
	<style media="screen">
		html { margin-top: 32px !important; }
		* html body { margin-top: 32px !important; }
		@media screen and ( max-width: 782px ) {
			html { margin-top: 46px !important; }
			* html body { margin-top: 46px !important; }
		}
	</style>
</head>
<body class="home blog logged-in admin-bar wp-embed-responsive hfeed has-header-image has-sidebar colors-light customize-support">
	<!--[if lte IE 8]>
	<script>
	 document.body.className = document.body.className.replace( /(^|\s)(no-)?customize-support(?=\s|$)/, '' ) + ' no-customize-support';
	</script>
	<![endif]-->
	<div id="page" class="site">
		<a class="skip-link screen-reader-text" href="#content">Skip to content</a>
		<header id="masthead" class="site-header" role="banner">
		</header><!-- #masthead -->
	</div><!-- #page -->
</body>
</html>
HTML
		,
		'conditionals' => [
			<<<IE_LINK
<!--[if lt IE 9]>
	<link rel='stylesheet' id='twentyseventeen-ie8-css'  href='https://example.org/wp-content/themes/twentyseventeen/assets/css/ie8.css?ver=20161202' media='all' />
	<![endif]-->
IE_LINK
			,
			<<<IE_SCRIPT
<!--[if lt IE 9]>
	<script src='https://example.org/wp-content/themes/twentyseventeen/assets/js/html5.js?ver=20161020'></script>
	<![endif]-->
IE_SCRIPT
			,
			<<<IE_IN_BODY
<!--[if lte IE 8]>
	<script>
	 document.body.className = document.body.className.replace( /(^|\s)(no-)?customize-support(?=\s|$)/, '' ) + ' no-customize-support';
	</script>
	<![endif]-->
IE_IN_BODY
,
		],
	],

	'shouldInjectAndFixMultipleBackslashesInScript' => [
		'html'         => <<<HTML
<!DOCTYPE html>
<html lang="en-US" class="js svg background-fixed">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<title>WP Rocket QA - Testing page</title>
	<link rel="dns-prefetch" href="//fonts.googleapis.com">
	<link href="https://fonts.gstatic.com" crossorigin="" rel="preconnect">
	<link rel="alternate" type="application/rss+xml" title="WP Rocket QA » Feed" href="https://example.org/feed/">
	<link rel="alternate" type="application/rss+xml" title="WP Rocket QA » Comments Feed" href="https://example.org/comments/feed/">
	<style>
		img.wp-smiley,
		img.emoji {
			display: inline !important;
			border: none !important;
			box-shadow: none !important;
			height: 1em !important;
			width: 1em !important;
			margin: 0 .07em !important;
			vertical-align: -0.1em !important;
			background: none !important;
			padding: 0 !important;
		}
	</style>
	<link rel="preload" href="https://example.org/wp-includes/css/dashicons.min.css?ver=5.4.2" as="style">
	<link rel="stylesheet" id="dashicons-css" href="https://example.org/wp-includes/css/dashicons.min.css?ver=5.4.2" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-includes/css/admin-bar.min.css?ver=5.4.2" as="style">
	<link rel="stylesheet" id="admin-bar-css" href="https://example.org/wp-includes/css/admin-bar.min.css?ver=5.4.2" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=5.4.2" as="style">
	<link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=5.4.2" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-includes/css/dist/block-library/theme.min.css?ver=5.4.2" as="style">
	<link rel="stylesheet" id="wp-block-library-theme-css" href="https://example.org/wp-includes/css/dist/block-library/theme.min.css?ver=5.4.2" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-content/plugins/imagify/assets/css/admin-bar.min.css?ver=1.9.10" as="style">
	<link rel="stylesheet" id="imagify-admin-bar-css" href="https://example.org/wp-content/plugins/imagify/assets/css/admin-bar.min.css?ver=1.9.10" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://fonts.googleapis.com/css?family=Libre+Franklin%3A300%2C300i%2C400%2C400i%2C600%2C600i%2C800%2C800i&amp;subset=latin%2Clatin-ext&amp;display=fallback" as="style">
	<link rel="stylesheet" id="twentyseventeen-fonts-css" href="https://fonts.googleapis.com/css?family=Libre+Franklin%3A300%2C300i%2C400%2C400i%2C600%2C600i%2C800%2C800i&amp;subset=latin%2Clatin-ext&amp;display=fallback" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-content/themes/twentyseventeen/style.css?ver=20190507" as="style">
	<link rel="stylesheet" id="twentyseventeen-style-css" href="https://example.org/wp-content/themes/twentyseventeen/style.css?ver=20190507" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-content/themes/twentyseventeen/assets/css/blocks.css?ver=20190105" as="style">
	<link rel="stylesheet" id="twentyseventeen-block-style-css" href="https://example.org/wp-content/themes/twentyseventeen/assets/css/blocks.css?ver=20190105" media="print" onload="this.onload=null;this.media='all'">
	<!--{{WP_ROCKET_CONDITIONAL}}-->
	<script src="https://example.org/wp-includes/js/jquery/jquery.js?ver=1.12.4-wp"></script>
	<script src="https://example.org/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1"></script>
	<!--{{WP_ROCKET_CONDITIONAL}}-->
	<link rel="https://api.w.org/" href="https://example.org/wp-json/">
	<link rel="EditURI" type="application/rsd+xml" title="RSD" href="https://example.org/xmlrpc.php?rsd">
	<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="https://example.org/wp-includes/wlwmanifest.xml">
	<meta name="generator" content="WordPress 5.4.2">
	<style>.recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;}</style><style media="print">#wpadminbar { display:none; }</style>
	<style media="screen">
		html { margin-top: 32px !important; }
		* html body { margin-top: 32px !important; }
		@media screen and ( max-width: 782px ) {
			html { margin-top: 46px !important; }
			* html body { margin-top: 46px !important; }
		}
	</style>
</head>
<body class="home blog logged-in admin-bar wp-embed-responsive hfeed has-header-image has-sidebar colors-light customize-support">
	<!--{{WP_ROCKET_CONDITIONAL}}-->
	<!--{{WP_ROCKET_CONDITIONAL}}-->
	<div id="page" class="site">
		<a class="skip-link screen-reader-text" href="#content">Skip to content</a>
		<header id="masthead" class="site-header" role="banner">
		</header><!-- #masthead -->
	</div><!-- #page -->
</body>
</html>
HTML
		,
		'expected'     => <<<HTML
<!DOCTYPE html>
<html lang="en-US" class="js svg background-fixed">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<title>WP Rocket QA - Testing page</title>
	<link rel="dns-prefetch" href="//fonts.googleapis.com">
	<link href="https://fonts.gstatic.com" crossorigin="" rel="preconnect">
	<link rel="alternate" type="application/rss+xml" title="WP Rocket QA » Feed" href="https://example.org/feed/">
	<link rel="alternate" type="application/rss+xml" title="WP Rocket QA » Comments Feed" href="https://example.org/comments/feed/">
	<style>
		img.wp-smiley,
		img.emoji {
			display: inline !important;
			border: none !important;
			box-shadow: none !important;
			height: 1em !important;
			width: 1em !important;
			margin: 0 .07em !important;
			vertical-align: -0.1em !important;
			background: none !important;
			padding: 0 !important;
		}
	</style>
	<link rel="preload" href="https://example.org/wp-includes/css/dashicons.min.css?ver=5.4.2" as="style">
	<link rel="stylesheet" id="dashicons-css" href="https://example.org/wp-includes/css/dashicons.min.css?ver=5.4.2" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-includes/css/admin-bar.min.css?ver=5.4.2" as="style">
	<link rel="stylesheet" id="admin-bar-css" href="https://example.org/wp-includes/css/admin-bar.min.css?ver=5.4.2" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=5.4.2" as="style">
	<link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=5.4.2" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-includes/css/dist/block-library/theme.min.css?ver=5.4.2" as="style">
	<link rel="stylesheet" id="wp-block-library-theme-css" href="https://example.org/wp-includes/css/dist/block-library/theme.min.css?ver=5.4.2" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-content/plugins/imagify/assets/css/admin-bar.min.css?ver=1.9.10" as="style">
	<link rel="stylesheet" id="imagify-admin-bar-css" href="https://example.org/wp-content/plugins/imagify/assets/css/admin-bar.min.css?ver=1.9.10" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://fonts.googleapis.com/css?family=Libre+Franklin%3A300%2C300i%2C400%2C400i%2C600%2C600i%2C800%2C800i&amp;subset=latin%2Clatin-ext&amp;display=fallback" as="style">
	<link rel="stylesheet" id="twentyseventeen-fonts-css" href="https://fonts.googleapis.com/css?family=Libre+Franklin%3A300%2C300i%2C400%2C400i%2C600%2C600i%2C800%2C800i&amp;subset=latin%2Clatin-ext&amp;display=fallback" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-content/themes/twentyseventeen/style.css?ver=20190507" as="style">
	<link rel="stylesheet" id="twentyseventeen-style-css" href="https://example.org/wp-content/themes/twentyseventeen/style.css?ver=20190507" media="print" onload="this.onload=null;this.media='all'">
	<link rel="preload" href="https://example.org/wp-content/themes/twentyseventeen/assets/css/blocks.css?ver=20190105" as="style">
	<link rel="stylesheet" id="twentyseventeen-block-style-css" href="https://example.org/wp-content/themes/twentyseventeen/assets/css/blocks.css?ver=20190105" media="print" onload="this.onload=null;this.media='all'">
	<!--[if lt IE 9]>
	<link rel='stylesheet' id='twentyseventeen-ie8-css'  href='https://example.org/wp-content/themes/twentyseventeen/assets/css/ie8.css?ver=20161202' media='all' />
	<![endif]-->
	<script src="https://example.org/wp-includes/js/jquery/jquery.js?ver=1.12.4-wp"></script>
	<script src="https://example.org/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1"></script>
	<!--[if lt IE 9]>
	<script src='https://example.org/wp-content/themes/twentyseventeen/assets/js/html5.js?ver=20161020'></script>
	<![endif]-->
	<link rel="https://api.w.org/" href="https://example.org/wp-json/">
	<link rel="EditURI" type="application/rsd+xml" title="RSD" href="https://example.org/xmlrpc.php?rsd">
	<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="https://example.org/wp-includes/wlwmanifest.xml">
	<meta name="generator" content="WordPress 5.4.2">
	<style>.recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;}</style><style media="print">#wpadminbar { display:none; }</style>
	<style media="screen">
		html { margin-top: 32px !important; }
		* html body { margin-top: 32px !important; }
		@media screen and ( max-width: 782px ) {
			html { margin-top: 46px !important; }
			* html body { margin-top: 46px !important; }
		}
	</style>
</head>
<body class="home blog logged-in admin-bar wp-embed-responsive hfeed has-header-image has-sidebar colors-light customize-support">
	<!--[if lte IE 8]>
	<script>
		document.body.className = document.body.className.replace( /(^|\s)(no-)?customize-support(?=\s|$)/, '' ) + ' no-customize-support';
	</script>
	<![endif]-->
	<!--[if gte IE 9]><!-->
	<script>
		(function() {
			var request, b = document.body, c = 'className', cs = 'customize-support', rcs = new RegExp('(^|\s+)(no-)?'+cs+'(\s+|$)');
			request = true;
			b[c] = b[c].replace( rcs, ' ' );
			// The customizer requires postMessage and CORS (if the site is cross domain).
			b[c] += ( window.postMessage && request ? ' ' : ' no-' ) + cs;
		}());
	</script>
	<!--<![endif]-->
	<div id="page" class="site">
		<a class="skip-link screen-reader-text" href="#content">Skip to content</a>
		<header id="masthead" class="site-header" role="banner">
		</header><!-- #masthead -->
	</div><!-- #page -->
</body>
</html>
HTML
		,
		'conditionals' => [
			<<<IE_LINK
<!--[if lt IE 9]>
	<link rel='stylesheet' id='twentyseventeen-ie8-css'  href='https://example.org/wp-content/themes/twentyseventeen/assets/css/ie8.css?ver=20161202' media='all' />
	<![endif]-->
IE_LINK
			,
			<<<IE_SCRIPT
<!--[if lt IE 9]>
	<script src='https://example.org/wp-content/themes/twentyseventeen/assets/js/html5.js?ver=20161020'></script>
	<![endif]-->
IE_SCRIPT
			,
			<<<IE_IN_BODY
<!--[if lte IE 8]>
	<script>
		document.body.className = document.body.className.replace( /(^|\s)(no-)?customize-support(?=\s|$)/, '' ) + ' no-customize-support';
	</script>
	<![endif]-->
IE_IN_BODY
			,
			<<<IE_IN_BODY_WITH_MULTIPLE_BACKSLASHES
<!--[if gte IE 9]><!-->
	<script>
		(function() {
			var request, b = document.body, c = 'className', cs = 'customize-support', rcs = new RegExp('(^|\\s+)(no-)?'+cs+'(\\s+|$)');
			request = true;
			b[c] = b[c].replace( rcs, ' ' );
			// The customizer requires postMessage and CORS (if the site is cross domain).
			b[c] += ( window.postMessage && request ? ' ' : ' no-' ) + cs;
		}());
	</script>
	<!--<![endif]-->
IE_IN_BODY_WITH_MULTIPLE_BACKSLASHES
,
		],
	],
];
