<?php

$scripts = new WP_Scripts();
wp_default_scripts( $scripts );

$jquery_path = $scripts->registered['jquery-core']->src;
$timenow = time();

return [
	'vfs_dir' => 'wp-content/',

	'settings' => [
		'minify_concatenate_js' => 0,
		'cdn'                   => 0,
		'cdn_cnames'            => [],
		'cdn_zone'              => [],
		'defer_all_js'          => 0,
	],

	'test_data' => [

		'minifyJSFile' => [
			// Test Data: Original JS files.
			'original' =>
				'<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="http://example.org' . $jquery_path . '"></script>
					<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js"></script>
				</head>
				<body>
				</body>
			</html>',
			'expected' => [
				'html' => '<html>
					<head>
						<title>Sample Page</title>
						<script data-minify="1" type="text/javascript" src="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js?ver={{mtime}}"></script>
						<script data-minify="1" type="text/javascript" src="http://example.org/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js?ver={{mtime}}"></script>
						<script type="text/javascript" src="http://example.org' . $jquery_path . '"></script>
						<script data-minify="1" type="text/javascript" src="http://example.org/wp-content/cache/min/1/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js?ver={{mtime}}"></script>
					</head>
					<body>
					<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false,"elements":"img, video, picture, p, main, div, li, svg","width_threshold":1600,"height_threshold":700}</script>
					<script data-name="wpr-lcp-beacon" src=\'http://example.org/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js.gz',
					'wp-content/cache/min/1/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js',
					'wp-content/cache/min/1/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js.gz',
				],
			],
			'settings' => [
				'minify_concatenate_js' => 0,
				'cdn'                   => 0,
				'cdn_cnames'            => [],
				'cdn_zone'              => [],
				'defer_all_js'          => 0,
			],
		],

		'minifyJSFileWithIntegrity' => [
			// Test Data: Original JS files.
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js" integrity="notvalid"></script>
					<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js" integrity="notvalidalgorithm-hashed"></script>
					<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js" integrity="sha384-notvalidhash"></script>
					<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.1/js/bootstrap.js" integrity="sha384-DCTGxr1MNV4fD9E8fGEPvxOCqu7hIyBSUrSwiSFtEloMCudWDuD8X75eb1x9b8eJ"></script>
				</head>
				<body>
				</body>
			</html>',
			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<script data-minify="1" type="text/javascript" src="http://example.org/wp-content/cache/min/1/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js?ver={{mtime}}" integrity="notvalid"></script>
						<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js" integrity="notvalidalgorithm-hashed"></script>
						<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js" integrity="sha384-notvalidhash"></script>
						<script data-minify="1" type="text/javascript" src="http://example.org/wp-content/cache/min/1/ajax/libs/twitter-bootstrap/4.5.1/js/bootstrap.js?ver={{mtime}}"></script>
					</head>
					<body>
					<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false,"elements":"img, video, picture, p, main, div, li, svg","width_threshold":1600,"height_threshold":700}</script>
					<script data-name="wpr-lcp-beacon" src=\'http://example.org/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js',
					'wp-content/cache/min/1/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js.gz',
					'wp-content/cache/min/1/ajax/libs/twitter-bootstrap/4.5.1/js/bootstrap.js',
					'wp-content/cache/min/1/ajax/libs/twitter-bootstrap/4.5.1/js/bootstrap.js.gz',
				],
			],
			'settings' => [
				'minify_concatenate_js' => 0,
				'cdn'                   => 0,
				'cdn_cnames'            => [],
				'cdn_zone'              => [],
				'defer_all_js'          => 0,
			],
		],

		'minifyJSFileForGoogleCSE' => [
			// Test Data: Original JS files.
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="https://cse.google.com/cse.js?cx=xxx:xxx"></script>
				</head>
				<body>
				</body>
			</html>',
			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<script type="text/javascript" src="https://cse.google.com/cse.js?cx=xxx:xxx"></script>
					</head>
					<body>
					<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false,"elements":"img, video, picture, p, main, div, li, svg","width_threshold":1600,"height_threshold":700}</script>
					<script data-name="wpr-lcp-beacon" src=\'http://example.org/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
					</body>
				</html>',
				'files' => [],
			],
			'settings' => [
				'minify_concatenate_js' => 0,
				'cdn'                   => 0,
				'cdn_cnames'            => [],
				'cdn_zone'              => [],
				'defer_all_js'          => 0,
			],
		],

		'minifyJSFilesToCDNUrl' => [
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="http://example.org' . $jquery_path . '"></script>
				</head>
				<body>
				</body>
			</html>',
			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js?ver={{mtime}}"></script>
						<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js?ver={{mtime}}"></script>
						<script type="text/javascript" src="https://123456.rocketcdn.me' . $jquery_path . '"></script>
					</head>
					<body>
					<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false,"elements":"img, video, picture, p, main, div, li, svg","width_threshold":1600,"height_threshold":700}</script>
					<script data-name="wpr-lcp-beacon" src=\'https://123456.rocketcdn.me/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
					</body>
				</html>',
				'files'   => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js.gz',
				],
			],
			'settings' => [
				'minify_concatenate_js' => 0,
				'cdn'                   => 1,
				'cdn_cnames'            => [ 'https://123456.rocketcdn.me' ],
				'cdn_zone'              => [ 'all' ],
				'defer_all_js'          => 0,
			],
		],

		'shouldMinifyJSFilesWithCDNUrl_withoutSubdir' => [
			// Test Data: Original JS files.
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="https://123456.rocketcdn.me' . $jquery_path . '"></script>
				</head>
				<body>
				</body>
			</html>',
			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js?ver={{mtime}}"></script>
						<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js?ver={{mtime}}"></script>
						<script type="text/javascript" src="https://123456.rocketcdn.me' . $jquery_path . '"></script>
					</head>
					<body>
					<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false,"elements":"img, video, picture, p, main, div, li, svg","width_threshold":1600,"height_threshold":700}</script>
					<script data-name="wpr-lcp-beacon" src=\'https://123456.rocketcdn.me/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js.gz',
				],
			],
			'settings' => [
				'minify_concatenate_js' => 0,
				'cdn'                   => 1,
				'cdn_cnames'            => [ 'https://123456.rocketcdn.me' ],
				'cdn_zone'              => [ 'all' ],
				'defer_all_js'          => 0,
			],
		],

		'shouldMinifyJSFilesWithCDNUrlWithSubDir' => [
			'original' =>'<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="http://example.org' . $jquery_path . '"></script>
				</head>
				<body>
				</body>
			</html>',
			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js?ver={{mtime}}"></script>
						<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js?ver={{mtime}}"></script>
						<script type="text/javascript" src="https://123456.rocketcdn.me/cdnpath' . $jquery_path . '"></script>
					</head>
					<body>
					<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false,"elements":"img, video, picture, p, main, div, li, svg","width_threshold":1600,"height_threshold":700}</script>
					<script data-name="wpr-lcp-beacon" src=\'https://123456.rocketcdn.me/cdnpath/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js.gz',
				],
			],
			[
				'minify_concatenate_js' => 0,
				'cdn'                   => 1,
				'cdn_cnames'            => [ 'https://123456.rocketcdn.me/cdnpath' ],
				'cdn_zone'              => [ 'all' ],
				'defer_all_js'          => 0,
			],
		],

		'shouldCombineJSFiles_whenNoCDN' => [
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="http://example.org' . $jquery_path . '"></script>
					<script>
					document.getElementById("demo").innerHTML = "Hello JavaScript!";
					</script>
					<script>
					nonce = "nonce";
					</script>
				</head>
				<body>
				</body>
			</html>',
			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<script>
						nonce = "nonce";
						</script>
					</head>
					<body>
						<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false,"elements":"img, video, picture, p, main, div, li, svg","width_threshold":1600,"height_threshold":700}</script>
						<script data-name="wpr-lcp-beacon" src=\'http://example.org/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
						<script src="http://example.org/wp-content/cache/min/1/f819bcaed244d53d3b4ffc4c5cc0efdc.js" data-minify="1"></script>
					</body>
				</html>',
				'files'   => [
					'wp-content/cache/min/1/f819bcaed244d53d3b4ffc4c5cc0efdc.js',
					'wp-content/cache/min/1/f819bcaed244d53d3b4ffc4c5cc0efdc.js.gz',
				],
			],
			'settings' => [
				'minify_concatenate_js' => 1,
				'cdn'                   => 0,
				'cdn_cnames'            => [],
				'cdn_zone'              => [],
				'defer_all_js'          => 0,
			],
		],

		'shouldCombineJSFilesToCDNUrl' => [
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="http://example.org' . $jquery_path . '"></script>
					<script>
					document.getElementById("demo").innerHTML = "Hello JavaScript!";
					</script>
					<script>
					nonce = "nonce";
					</script>
				</head>
				<body>
				</body>
			</html>',

			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<script>
						nonce = "nonce";
						</script>
					</head>
					<body>
						<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false,"elements":"img, video, picture, p, main, div, li, svg","width_threshold":1600,"height_threshold":700}</script>
						<script data-name="wpr-lcp-beacon" src=\'https://123456.rocketcdn.me/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
						<script src="https://123456.rocketcdn.me/wp-content/cache/min/1/f819bcaed244d53d3b4ffc4c5cc0efdc.js" data-minify="1"></script>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/f819bcaed244d53d3b4ffc4c5cc0efdc.js',
					'wp-content/cache/min/1/f819bcaed244d53d3b4ffc4c5cc0efdc.js.gz',
				],
			],

			'settings' => [
				'minify_concatenate_js' => 1,
				'cdn'                   => 1,
				'cdn_cnames'            => [ 'https://123456.rocketcdn.me' ],
				'cdn_zone'              => [ 'all' ],
				'defer_all_js'          => 0,
			],
		],

		'shouldCombineJSFilesWithDefer' => [
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="http://example.org' . $jquery_path . '"></script>
					<script>
					document.getElementById("demo").innerHTML = "Hello JavaScript!";
					</script>
					<script>
					nonce = "nonce";
					</script>
				</head>
				<body>
				</body>
			</html>',

			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<script type="text/javascript" src="http://example.org' . $jquery_path . '" defer></script>
						<script>
						nonce = "nonce";
						</script>
					</head>
					<body>
						<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false,"elements":"img, video, picture, p, main, div, li, svg","width_threshold":1600,"height_threshold":700}</script>
						<script data-name="wpr-lcp-beacon" src=\'http://example.org/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
						<script src="http://example.org/wp-content/cache/min/1/1100e4606ab35f45752eb8c3c8da0427.js" data-minify="1" defer></script>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/1100e4606ab35f45752eb8c3c8da0427.js',
					'wp-content/cache/min/1/1100e4606ab35f45752eb8c3c8da0427.js.gz',
				],
			],

			'settings' => [
				'minify_concatenate_js' => 1,
				'cdn'                   => 0,
				'cdn_cnames'            => [ ],
				'cdn_zone'              => [ ],
				'defer_all_js'          => 1,
			],
		],

		'shouldCombineJSFilesWithDeferAndExternalJQueryLibrary' => [
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="http://example.org' . $jquery_path . '"></script>
					<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js"></script>
					<script>
					document.getElementById("demo").innerHTML = "Hello JavaScript!";
					</script>
					<script>
					nonce = "nonce";
					</script>
				</head>
				<body>
				</body>
			</html>',

			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<script type="text/javascript" src="http://example.org' . $jquery_path . '" defer></script>
						<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js" defer></script>
						<script>
						nonce = "nonce";
						</script>
					</head>
					<body>
						<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false,"elements":"img, video, picture, p, main, div, li, svg","width_threshold":1600,"height_threshold":700}</script>
						<script data-name="wpr-lcp-beacon" src=\'http://example.org/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
						<script src="http://example.org/wp-content/cache/min/1/1100e4606ab35f45752eb8c3c8da0427.js" data-minify="1" defer></script>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/1100e4606ab35f45752eb8c3c8da0427.js',
					'wp-content/cache/min/1/1100e4606ab35f45752eb8c3c8da0427.js.gz',
				],
			],

			'settings' => [
				'minify_concatenate_js' => 1,
				'cdn'                   => 0,
				'cdn_cnames'            => [ ],
				'cdn_zone'              => [ ],
				'defer_all_js'          => 1,
			],
		],

		'shouldCombineJSWithCdnFilesWithDefer' => [
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="http://example.org' . $jquery_path . '"></script>
					<script>
					document.getElementById("demo").innerHTML = "Hello JavaScript!";
					</script>
					<script>
					nonce = "nonce";
					</script>
				</head>
				<body>
				</body>
			</html>',

			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<script type="text/javascript" src="https://123456.rocketcdn.me' . $jquery_path . '" defer></script>
						<script>
						nonce = "nonce";
						</script>
					</head>
					<body>
						<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false,"elements":"img, video, picture, p, main, div, li, svg","width_threshold":1600,"height_threshold":700}</script>
						<script data-name="wpr-lcp-beacon" src=\'https://123456.rocketcdn.me/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
						<script src="https://123456.rocketcdn.me/wp-content/cache/min/1/1100e4606ab35f45752eb8c3c8da0427.js" data-minify="1" defer></script>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/1100e4606ab35f45752eb8c3c8da0427.js',
					'wp-content/cache/min/1/1100e4606ab35f45752eb8c3c8da0427.js.gz',
				],
			],

			'settings' => [
				'minify_concatenate_js' => 1,
				'cdn'                   => 1,
				'cdn_cnames'            => [ 'https://123456.rocketcdn.me' ],
				'cdn_zone'              => [ 'all' ],
				'defer_all_js'          => 1,
			],
		],

		'shouldCombineJSFilesWithCDNUrlWithDeferAndExternalJQueryLibrary' => [
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="http://example.org' . $jquery_path . '"></script>
					<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js"></script>
					<script>
					document.getElementById("demo").innerHTML = "Hello JavaScript!";
					</script>
					<script>
					nonce = "nonce";
					</script>
				</head>
				<body>
				</body>
			</html>',

			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<script type="text/javascript" src="https://123456.rocketcdn.me' . $jquery_path . '" defer></script>
						<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js" defer></script>
						<script>
						nonce = "nonce";
						</script>
					</head>
					<body>
						<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false,"elements":"img, video, picture, p, main, div, li, svg","width_threshold":1600,"height_threshold":700}</script>
						<script data-name="wpr-lcp-beacon" src=\'https://123456.rocketcdn.me/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
						<script src="https://123456.rocketcdn.me/wp-content/cache/min/1/1100e4606ab35f45752eb8c3c8da0427.js" data-minify="1" defer></script>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/1100e4606ab35f45752eb8c3c8da0427.js',
					'wp-content/cache/min/1/1100e4606ab35f45752eb8c3c8da0427.js.gz',
				],
			],

			'settings' => [
				'minify_concatenate_js' => 1,
				'cdn'                   => 1,
				'cdn_cnames'            => [ 'https://123456.rocketcdn.me' ],
				'cdn_zone'              => [ 'all' ],
				'defer_all_js'          => 1,
			],
		],

		'shouldCombineJSfilesWithCDNUrlAndNoSubDir' => [
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="https://123456.rocketcdn.me' . $jquery_path . '"></script>
					<script>
					document.getElementById("demo").innerHTML = "Hello JavaScript!";
					</script>
					<script>
					nonce = "nonce";
					</script>
				</head>
				<body>
				</body>
			</html>',

			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<script>
						nonce = "nonce";
						</script>
					</head>
					<body>
						<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false,"elements":"img, video, picture, p, main, div, li, svg","width_threshold":1600,"height_threshold":700}</script>
						<script data-name="wpr-lcp-beacon" src=\'https://123456.rocketcdn.me/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
						<script src="https://123456.rocketcdn.me/wp-content/cache/min/1/f819bcaed244d53d3b4ffc4c5cc0efdc.js" data-minify="1"></script>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/f819bcaed244d53d3b4ffc4c5cc0efdc.js',
					'wp-content/cache/min/1/f819bcaed244d53d3b4ffc4c5cc0efdc.js.gz',
				],
			],

			'settings' => [
				'minify_concatenate_js' => 1,
				'cdn'                   => 1,
				'cdn_cnames'            => [ 'https://123456.rocketcdn.me' ],
				'cdn_zone'              => [ 'all' ],
				'defer_all_js'          => 0,
			],
		],

		'combineJSFilesWithCDNUrlWithSubdir' => [
			// Test Data: Original JS files.
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="https://123456.rocketcdn.me/cdnpath' . $jquery_path . '"></script>
					<script>
					document.getElementById("demo").innerHTML = "Hello JavaScript!";
					</script>
					<script>
					nonce = "nonce";
					</script>
				</head>
				<body>
				</body>
			</html>',

			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<script>
						nonce = "nonce";
						</script>
					</head>
					<body>
						<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false,"elements":"img, video, picture, p, main, div, li, svg","width_threshold":1600,"height_threshold":700}</script>
						<script data-name="wpr-lcp-beacon" src=\'https://123456.rocketcdn.me/cdnpath/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
						<script src="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/f819bcaed244d53d3b4ffc4c5cc0efdc.js" data-minify="1"></script>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/f819bcaed244d53d3b4ffc4c5cc0efdc.js',
					'wp-content/cache/min/1/f819bcaed244d53d3b4ffc4c5cc0efdc.js.gz',
				],
			],

			'settings' => [
				'minify_concatenate_js' => 1,
				'cdn'                   => 1,
				'cdn_cnames'            => [ 'https://123456.rocketcdn.me/cdnpath' ],
				'cdn_zone'              => [ 'all' ],
				'defer_all_js'          => 0,
			],
		],
	],
];
