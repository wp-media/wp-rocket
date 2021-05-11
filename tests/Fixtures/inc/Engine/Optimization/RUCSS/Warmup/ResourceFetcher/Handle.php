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
		'shouldBailoutWithNoHTMLContent' => [
			'input'    => [
				'html' => '',
			],
			'expected' => [
				'resources' => [],
			],
		],

		'shouldBailoutWithNoResourcesInHTML' => [
			'input'    => [
				'html' => '<!DOCTYPE html><html><head><title></title></head><body>Content here</body></html>',
			],
			'expected' => [
				'resources' => [],
			],
		],

		'shouldBailoutWithNotFoundResourcesOrEmptyContent' => [
			'input'    => [
				'html' => '<!DOCTYPE html><html><head><title></title>' .
						  '<link rel="stylesheet" type="text/css" href="http://example.org/css/style-empty.css">' .
						  '<link rel="stylesheet" type="text/css" href="http://example.org/css/style-notfound.css">' .
						  '</head><body>Content here</body></html>',
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/style-empty.css',
						'content'   => '*',
						'type'      => 'css',
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => '',
					],
					[
						'url'       => 'http://example.org/css/style-notfound.css',
						'content'   => '*',
						'type'      => 'css',
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => '',
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
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/style1.css?ver=123',
						'content'   => '.first{color:red}',
						'type'      => 'css',
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => '',
					],
					[
						'url'       => 'http://example.org/css/style2.css',
						'content'   => '.second{color:green}',
						'type'      => 'css',
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => '',
					],
					[
						'url'       => 'http://example.org/scripts/script2.js',
						'content'   => 'var second="content 2"',
						'type'      => 'js',
						'prewarmup' => 0,
						'page_url'  => '',
					]
				],
			],
		],

		'shouldQueueResourcesWithMedias' => [
			'input'    => [
				'html' => '<!DOCTYPE html><html><head><title></title>' .
						  '<link rel="stylesheet" type="text/css" href="http://example.org/css/style1.css?ver=123" media="all">' .
						  '<link media="print" rel="stylesheet" type="text/css" href="http://example.org/css/style2.css">' .
						  '</head><body>Content here</body></html>',
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/style1.css?ver=123',
						'content'   => '.first{color:red}',
						'type'      => 'css',
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => '',
					],
					[
						'url'       => 'http://example.org/css/style2.css',
						'content'   => '.second{color:green}',
						'type'      => 'css',
						'media'     => 'print',
						'prewarmup' => 0,
						'page_url'  => '',
					]

				],
			],
		],

		'shouldQueueResourcesWithoutSchema' => [
			'input'    => [
				'html' => '<!DOCTYPE html><html><head><title></title>' .
						  '<link rel="stylesheet" type="text/css" href="//example.org/css/style1.css?ver=123">' .
						  '<script type="text/javascript" src="//example.org/scripts/script1.js"></script>' .
						  '</head><body>Content here</body></html>',
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/style1.css?ver=123',
						'content'   => '.first{color:red}',
						'type'      => 'css',
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => '',
					],
					[
						'url'       => 'http://example.org/scripts/script1.js',
						'content'   => 'var first="content 1"',
						'type'      => 'js',
						'prewarmup' => 0,
						'page_url'  => '',
					]
				],
			],
		],

		'shouldFindAndQueueResourcesFoundFromCSSImport' => [
			'input' => [
				'html' => '<!DOCTYPE html><html><head><title></title>' .
						  '<link rel="stylesheet" type="text/css" href="//example.org/css/stylewithimport.css?ver=123">' .
						  '<script type="text/javascript" src="//example.org/scripts/script1.js"></script>' .
						  '</head><body>Content here</body></html>'
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/stylewithimport.css?ver=123',
						'content'   => '.first{color:red}.another-class-in-stylewithimport{color:#fff}',
						'type'      => 'css',
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => '',
					],
					[
						'url'       => 'http://example.org/scripts/script1.js',
						'content'   => 'var first="content 1"',
						'type'      => 'js',
						'prewarmup' => 0,
						'page_url'  => '',
					],
				],
			],
		],

		'shouldFindAndQueueResourcesWithMediaQueryFoundFromCSSImport' => [
			'input' => [
				'html' => '<!DOCTYPE html><html><head><title></title>' .
						  '<link rel="stylesheet" type="text/css" href="//example.org/css/stylewithimportedmqs.css?ver=123">' .
						  '<script type="text/javascript" src="//example.org/scripts/script1.js"></script>' .
						  '</head><body>Content here</body></html>'
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/stylewithimportedmqs.css?ver=123',
						'content'   => '@media screen{.third{color:#000}}.another-imported-class{color:blue}',
						'type'      => 'css',
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => '',
					],
					[
						'url'       => 'http://example.org/scripts/script1.js',
						'content'   => 'var first="content 1"',
						'type'      => 'js',
						'prewarmup' => 0,
						'page_url'  => '',
					],
				],
			],
		],

		'shouldFindAndQueueResourcesWithRelativePathCSSImport' => [
			'input' => [
				'html' => '<!DOCTYPE html><html><head><title></title>' .
						  '<link rel="stylesheet" type="text/css" href="//example.org/css/stylewithrelativepathimport.css?ver=123">' .
						  '<script type="text/javascript" src="//example.org/scripts/script1.js"></script>' .
						  '</head><body>Content here</body></html>'
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/stylewithrelativepathimport.css?ver=123',
						'content'   => '.relatively-pathed-imported-class{color:#000}.some-imported-class{color:pink}',
						'type'      => 'css',
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => '',
					],
					[
						'url'       => 'http://example.org/scripts/script1.js',
						'content'   => 'var first="content 1"',
						'type'      => 'js',
						'prewarmup' => 0,
						'page_url'  => '',
					],
				],
			],
		],

		'shouldNotRequeueResourcesFoundFromRecursiveCSSImport' => [
			'input' => [
				'html' => '<!DOCTYPE html><html><head><title></title>' .
						  '<link rel="stylesheet" type="text/css" href="//example.org/css/stylewithimport-recursion.css?ver=123">' .
						  '<script type="text/javascript" src="//example.org/scripts/script1.js"></script>' .
						  '</head><body>Content here</body></html>'
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/stylewithimport-recursion.css?ver=123',
						'content'   => ".another-class-in-stylewithimport-recursion{color:#fff}",
						'type'      => 'css',
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => '',
					],
					[
						'url'       => 'http://example.org/scripts/script1.js',
						'content'   => 'var first="content 1"',
						'type'      => 'js',
						'prewarmup' => 0,
						'page_url'  => '',
					],
				],
			],
		],

		'shouldQueueResourcesWithSpecialCharacters' => [
			'input'    => [
				'html' => '<!DOCTYPE html><html><head><title></title>' .
				          '<link rel="stylesheet" type="text/css" href="http://example.org/css/style1.css?ver=123&#038;q=5">' .
				          '</head><body>Content here</body></html>',
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/style1.css?ver=123&q=5',
						'content'   => '.first{color:red}',
						'type'      => 'css',
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => '',
					],
				],
			],
		],

		'shouldQueueResourcesWithCommentContentOnly' => [
			'input'    => [
				'html' => '<!DOCTYPE html><html><head><title></title>' .
				          '<link rel="stylesheet" type="text/css" href="http://example.org/css/style-with-only-comment.css">' .
				          '</head><body>Content here</body></html>',
			],
			'expected' => [
				'resources' => [
					[
						'url'       => 'http://example.org/css/style-with-only-comment.css',
						'content'   => '*',
						'type'      => 'css',
						'media'     => 'all',
						'prewarmup' => 0,
						'page_url'  => '',
					],
				],
			],
		],
	],
];
