<?php

return [
	'vfs_dir' => 'wp-content/cache/',
	'structure' => [
		'wp-content' => [
			'cache' => [
				'busting' => [
					'1' => []
				],
				'google-tracking' => []
			]
		]
	],
	'test_data' => [
		'shouldBailOutWhenNoMatchingScripts' => [
			'config' => [
				'html' => '<html><head><script async src="https://www.test.com/rocket.js"></script></head></html>',
			],
			'expected' => '<html><head><script async src="https://www.test.com/rocket.js"></script></head></html>',
		],
		'shouldReplaceUrlWhenFullUrlAndAsync' => [
			'config' => [
				'url' => 'https://www.google-analytics.com/analytics.js',
				'html' => '<html><head><script async src="https://www.google-analytics.com/analytics.js"></script></head></html>',
			],
			'expected' => '<html><head><script async src="http://example.org/wp-content/cache/busting/google-tracking/ga-88c587e9d2fdeb7ac5d4cdd9bd8d4af5.js"></script></head></html>',
		],
		'shouldReplaceUrlWhenRelativeUrlAndAsync' => [
			'config' => [
				'url' => 'https://www.google-analytics.com/analytics.js',
				'html' => '<html><head><script async src="//www.google-analytics.com/analytics.js"></script></head></html>',
			],
			'expected' => '<html><head><script async src="http://example.org/wp-content/cache/busting/google-tracking/ga-88c587e9d2fdeb7ac5d4cdd9bd8d4af5.js"></script></head></html>',
		],
		'shouldReplaceUrlWhenFullUrlAndInsideInlineScript' => [
			'config' => [
				'url' => 'https://www.google-analytics.com/analytics.js',
				'html' => "<html><head><script>
				(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
				ga('create', 'UA-172439894-1', 'auto');
				ga('send', 'pageview');
				</script></head></html>",
			],
			'expected' => "<html><head><script>
				(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','http://example.org/wp-content/cache/busting/google-tracking/ga-88c587e9d2fdeb7ac5d4cdd9bd8d4af5.js','ga');
				ga('create', 'UA-172439894-1', 'auto');
				ga('send', 'pageview');
				</script></head></html>",
		],
		'shouldReplaceUrlWhenRelativeUrlAndInsideInlineScript' => [
			'config' => [
				'url' => 'https://www.google-analytics.com/analytics.js',
				'html' => "<html><head><script>
				(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
				ga('create', 'UA-172439894-1', 'auto');
				ga('send', 'pageview');
				</script></head></html>",
			],
			'expected' => "<html><head><script>
				(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','http://example.org/wp-content/cache/busting/google-tracking/ga-88c587e9d2fdeb7ac5d4cdd9bd8d4af5.js','ga');
				ga('create', 'UA-172439894-1', 'auto');
				ga('send', 'pageview');
				</script></head></html>",
		],
	]
];
