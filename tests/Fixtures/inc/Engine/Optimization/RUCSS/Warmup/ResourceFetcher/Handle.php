<?php

return [

	'vfs_dir' => 'public/',

	'structure' => [

		'css'     => [
			'style1.css'                      => '.first{color:red;}',
			'style2.css'                      => '.second{color:green;}',
			'style3.css'                      => '.third{color:#000000;}',
			'style-empty.css'                 => '',
			'stylewithimport.css'             => '@import "style1.css";.another-class-in-stylewithimport{color: white;}',
			'stylewithimportedmqs.css'        => '@import "style3.css" screen;.another-imported-class{color: blue;}',
			'stylewithimport-recursion.css'   => '@import "stylewithimport-recursion.css";.another-class-in-stylewithimport-recursion{color: white;}',
			'stylewithrelativepathimport.css' => '@import "./../relativelypathedstyles.css";.some-imported-class{color:pink;}',
			'style-with-only-comment.css'     => '/*
Theme Name: Yes
Theme URI: http://example.com/themes/neutro/
Author: WP Media
Author URI:
Description:
Version: 1.2.5
License: GNU General Public License
License URI: http://www.gnu.org/licenses/gpl.html
Tags: two-columns, three-columns, right-sidebar, custom-background, custom-colors, custom-menu, featured-images, post-formats, sticky-post, theme-options, threaded-comments, translation-ready
Text Domain: neutro
*/',
		],
		'scripts' => [
			'script1.js' => 'var first = "content 1";',
			'script2.js' => 'var second = "content 2";',
			'script3.js' => 'var third = "content 3";',

		],
		'relativelypathedstyles.css' => '.relatively-pathed-imported-class{color:black;}'
	],

	'test_data' => [
		'shouldSendURLWithNoHTMLContent' => [
			'input'    => [
				'html' => '',
				'is_error'  => true,
				'page_url'  => 'http://example.org/path/to/error/page/',
			],
			'expected' => [
				'resources' => [
					[
						'is_error'  => true,
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/to/error/page/',
					],
				],
			],
		],

		'shouldSendURLWithNoResourcesInHTML' => [
			'input'    => [
				'html'     => '<!DOCTYPE html><html><head><title></title></head><body>Content here</body></html>',
				'page_url' => 'http://example.org/path/',
				'is_error' => false,
			],
			'expected' => [
				'resources' => [
					[
						'is_error'  => true,
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
					],
				],
			],
		],

		'shouldBailoutWithNotFoundResourcesOrEmptyContent' => [
			'input'    => [
				'html'     => '<!DOCTYPE html><html><head><title></title>' .
						      '<link rel="stylesheet" type="text/css" href="http://example.org/css/style-empty.css">' .
						      '<link rel="stylesheet" type="text/css" href="http://example.org/css/style-notfound.css">' .
						      '</head><body>Content here</body></html>',
				'page_url' => 'http://example.org/path/',
				'is_error' => false,
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/style-empty.css',
						'type'      => 'css',
						'path'      => 'vfs://public/css/style-empty.css',
						'external'  => false,
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					],
					[
						'url'       => 'http://example.org/css/style-notfound.css',
						'type'      => 'css',
						'path'      => 'vfs://public/css/style-notfound.css',
						'external'  => false,
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					]
				],
			],
		],

		'shouldQueueResources' => [
			'input'    => [
				'html' => '<!DOCTYPE html><html><head><title></title>' .
						  '<link rel="stylesheet" type="text/css" href="http://example.org/css/style1.css?ver=123">' .
						  '<link rel="stylesheet" type="text/css" href="http://example.org/css/style2.css">' .

						  '<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100&display=swap" rel="stylesheet">' .
						  '<link href="https://fonts.googleapis.com/css?family=Roboto:wght@100&display=swap" rel="stylesheet">' .
						  '<link href="//fonts.googleapis.com/css2?family=Roboto:wght@100&display=swap" rel="stylesheet">' .
						  '<link href="//fonts.googleapis.com/css?family=Roboto:wght@100&display=swap" rel="stylesheet">' .

						  '<script type="application/ld+json" src="http://example.org/scripts/script1.js"></script>' .
						  '<script src="http://example.org/scripts/script2.js"></script>' .
						  '</head><body>Content here</body></html>',
				'page_url' => 'http://example.org/path/',
				'is_error' => false,
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/style1.css?ver=123',
						'type'      => 'css',
						'path'      => 'vfs://public/css/style1.css',
						'external'  => false,
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					],
					[
						'url'       => 'http://example.org/css/style2.css',
						'type'      => 'css',
						'path'      => 'vfs://public/css/style2.css',
						'external'  => false,
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					],
					[
						'url'       => 'http://example.org/scripts/script2.js',
						'type'      => 'js',
						'path'      => 'vfs://public/scripts/script2.js',
						'external'  => false,
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					]
				],
			],
		],

		'shouldQueueResourcesWithMedias' => [
			'input'    => [
				'page_url' => 'http://example.org/path/',
				'is_error' => false,
				'html'     => '<!DOCTYPE html><html><head><title></title>' .
								'<link rel="stylesheet" type="text/css" href="http://example.org/css/style1.css?ver=123" media="all">' .
								'<link media="print" rel="stylesheet" type="text/css" href="http://example.org/css/style2.css">' .
								'</head><body>Content here</body></html>',
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/style1.css?ver=123',
						'type'      => 'css',
						'path'      => 'vfs://public/css/style1.css',
						'external'  => false,
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					],
					[
						'url'       => 'http://example.org/css/style2.css',
						'type'      => 'css',
						'path'      => 'vfs://public/css/style2.css',
						'external'  => false,
						'media'     => 'print',
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					]

				],
			],
		],

		'shouldQueueResourcesWithoutSchema' => [
			'input'    => [
				'page_url' => 'http://example.org/path/',
				'is_error' => false,
				'html'     => '<!DOCTYPE html><html><head><title></title>' .
								'<link rel="stylesheet" type="text/css" href="//example.org/css/style1.css?ver=123">' .
								'<script type="text/javascript" src="//example.org/scripts/script1.js"></script>' .
								'</head><body>Content here</body></html>',
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/style1.css?ver=123',
						'type'      => 'css',
						'path'      => 'vfs://public/css/style1.css',
						'external'  => false,
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					],
					[
						'url'       => 'http://example.org/scripts/script1.js',
						'type'      => 'js',
						'path'      => 'vfs://public/scripts/script1.js',
						'external'  => false,
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					]
				],
			],
		],

		'shouldFindAndQueueResourcesFoundFromCSSImport' => [
			'input' => [
				'page_url' => 'http://example.org/path/',
				'is_error' => false,
				'html'     => '<!DOCTYPE html><html><head><title></title>' .
								'<link rel="stylesheet" type="text/css" href="//example.org/css/stylewithimport.css?ver=123">' .
								'<script type="text/javascript" src="//example.org/scripts/script1.js"></script>' .
								'</head><body>Content here</body></html>'
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/stylewithimport.css?ver=123',
						'type'      => 'css',
						'path'      => 'vfs://public/css/stylewithimport.css',
						'external'  => false,
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					],
					[
						'url'       => 'http://example.org/scripts/script1.js',
						'type'      => 'js',
						'path'      => 'vfs://public/scripts/script1.js',
						'external'  => false,
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					],
				],
			],
		],

		'shouldFindAndQueueResourcesWithMediaQueryFoundFromCSSImport' => [
			'input' => [
				'page_url' => 'http://example.org/path/',
				'is_error' => false,
				'html'     => '<!DOCTYPE html><html><head><title></title>' .
								'<link rel="stylesheet" type="text/css" href="//example.org/css/stylewithimportedmqs.css?ver=123">' .
								'<script type="text/javascript" src="//example.org/scripts/script1.js"></script>' .
								'</head><body>Content here</body></html>'
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/stylewithimportedmqs.css?ver=123',
						'type'      => 'css',
						'path'      => 'vfs://public/css/stylewithimportedmqs.css',
						'external'  => false,
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					],
					[
						'url'       => 'http://example.org/scripts/script1.js',
						'type'      => 'js',
						'path'      => 'vfs://public/scripts/script1.js',
						'external'  => false,
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					],
				],
			],
		],

		'shouldFindAndQueueResourcesWithRelativePathCSSImport' => [
			'input' => [
				'page_url' => 'http://example.org/path/',
				'is_error' => false,
				'html'     => '<!DOCTYPE html><html><head><title></title>' .
								'<link rel="stylesheet" type="text/css" href="//example.org/css/stylewithrelativepathimport.css?ver=123">' .
								'<script type="text/javascript" src="//example.org/scripts/script1.js"></script>' .
								'</head><body>Content here</body></html>'
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/stylewithrelativepathimport.css?ver=123',
						'type'      => 'css',
						'path'      => 'vfs://public/css/stylewithrelativepathimport.css',
						'external'  => false,
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					],
					[
						'url'       => 'http://example.org/scripts/script1.js',
						'type'      => 'js',
						'path'      => 'vfs://public/scripts/script1.js',
						'external'  => false,
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					],
				],
			],
		],

		'shouldNotRequeueResourcesFoundFromRecursiveCSSImport' => [
			'input' => [
				'page_url' => 'http://example.org/path/',
				'is_error' => false,
				'html'     => '<!DOCTYPE html><html><head><title></title>' .
								'<link rel="stylesheet" type="text/css" href="//example.org/css/stylewithimport-recursion.css?ver=123">' .
								'<script type="text/javascript" src="//example.org/scripts/script1.js"></script>' .
								'</head><body>Content here</body></html>'
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/stylewithimport-recursion.css?ver=123',
						'type'      => 'css',
						'path'      => 'vfs://public/css/stylewithimport-recursion.css',
						'external'  => false,
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					],
					[
						'url'       => 'http://example.org/scripts/script1.js',
						'type'      => 'js',
						'path'      => 'vfs://public/scripts/script1.js',
						'external'  => false,
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					],
				],
			],
		],

		'shouldQueueResourcesWithSpecialCharacters' => [
			'input'    => [
				'page_url' => 'http://example.org/path/',
				'is_error' => false,
				'html'     => '<!DOCTYPE html><html><head><title></title>' .
								'<link rel="stylesheet" type="text/css" href="http://example.org/css/style1.css?ver=123&#038;q=5">' .
								'</head><body>Content here</body></html>',
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/style1.css?ver=123&q=5',
						'type'      => 'css',
						'path'      => 'vfs://public/css/style1.css',
						'external'  => false,
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					],
				],
			],
		],

		'shouldQueueResourcesWithCommentContentOnly' => [
			'input'    => [
				'page_url' => 'http://example.org/path/',
				'is_error' => false,
				'html'     => '<!DOCTYPE html><html><head><title></title>' .
				              '<link rel="stylesheet" type="text/css" href="http://example.org/css/style-with-only-comment.css">' .
				              '</head><body>Content here</body></html>',
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/style-with-only-comment.css',
						'type'      => 'css',
						'path'      => 'vfs://public/css/style-with-only-comment.css',
						'external'  => false,
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					],
				],
			],
		],

		'shouldQueueExternalResources' => [
			'input'    => [
				'page_url' => 'http://example.org/path/',
				'is_error' => false,
				'html'     => '<!DOCTYPE html><html><head><title></title>' .
				              '<link rel="stylesheet" type="text/css" href="http://external.org/css/style.css">' .
				              '</head><body>Content here</body></html>',
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://external.org/css/style.css',
						'type'      => 'css',
						'path'      => 'thirdparty path',
						'external'  => true,
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => 'http://example.org/path/',
						'is_error'  => false,
					],
				],
			],
		],
	],
];
