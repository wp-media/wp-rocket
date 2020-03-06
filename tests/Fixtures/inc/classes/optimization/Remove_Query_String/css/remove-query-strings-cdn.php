<?php
return [
	[
		// CDN URL, CSS files are cached busted. Files with the WordPress version in the query get the ?ver= removed.
		'<html>
			<head>
				<title>Page title</title>
				<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css?ver=5.3">
				<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/style.css?ver=1.0">
				<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/style.css?ver=3.5">
				<link rel="stylesheet" href="https://google.com/external.css?v=20200306">
				<script src="https://123456.rocketcdn.me/wp-includes/js/jquery/jquery.js?ver=5.3"></script>
				<script src="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/assets/script.js?ver=1.0"></script>
			</head>
			<body>
				<script src="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/script.js?ver=3.5"></script>
			</body>
		</html>',
		'<html>
			<head>
				<title>Page title</title>
				<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">
				<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/busting/1/wp-content/themes/twentytwenty/style-1.0.css">
				<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/style-3.5.css">
				<link rel="stylesheet" href="https://google.com/external.css?v=20200306">
				<script src="https://123456.rocketcdn.me/wp-includes/js/jquery/jquery.js?ver=5.3"></script>
				<script src="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/assets/script.js?ver=1.0"></script>
			</head>
			<body>
				<script src="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/script.js?ver=3.5"></script>
			</body>
		</html>',
	]
];
