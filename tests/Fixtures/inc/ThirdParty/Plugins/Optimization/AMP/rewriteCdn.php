<?php

return [
	'original' => '<!doctype html>
			<html amp lang="en">
			  <head>
				<meta charset="utf-8">
				<script async src="https://cdn.ampproject.org/v0.js"></script>
				<title>Hello, AMPs</title>
				<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
				<script type="application/ld+json">
				  {
					"@context": "http://schema.org",
					"@type": "NewsArticle",
					"headline": "Open-source framework for publishing content",
					"datePublished": "2015-10-07T12:02:41Z",
					"image": [
					  "logo.jpg"
					]
				  }
				</script>
				<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
			  </head>
			  <body>
				<h1>Welcome to the mobile web</h1>
				<figure class="wp-block-image size-large"><amp-img src="http://example.org/wp-content/uploads/image1.png" width="300" height="300"></amp-img></figure>
				<figure class="wp-block-image size-large">
					<amp-img alt="Hummingbird"
						src="http://example.org/wp-content/uploads/image2.png"
						width="640"
						height="457"
						layout="responsive"
						srcset="http://example.org/wp-content/uploads/image1_wide.png 640w,
								http://example.org/wp-content/uploads/image1_narrow.png 320w">
					</amp-img>
			    </figure>
			  </body>
			</html>',
	'rewrite'  => '<!doctype html>
			<html amp lang="en">
			  <head>
				<meta charset="utf-8">
				<script async src="https://cdn.ampproject.org/v0.js"></script>
				<title>Hello, AMPs</title>
				<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
				<script type="application/ld+json">
				  {
					"@context": "http://schema.org",
					"@type": "NewsArticle",
					"headline": "Open-source framework for publishing content",
					"datePublished": "2015-10-07T12:02:41Z",
					"image": [
					  "logo.jpg"
					]
				  }
				</script>
				<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
			  </head>
			  <body>
				<h1>Welcome to the mobile web</h1>
				<figure class="wp-block-image size-large"><amp-img src="http://cdn.example.org/wp-content/uploads/image1.png" width="300" height="300"></amp-img></figure>
				<figure class="wp-block-image size-large">
					<amp-img alt="Hummingbird"
						src="http://cdn.example.org/wp-content/uploads/image2.png"
						width="640"
						height="457"
						layout="responsive"
						srcset="http://cdn.example.org/wp-content/uploads/image1_wide.png 640w,
								http://cdn.example.org/wp-content/uploads/image1_narrow.png 320w">
					</amp-img>
			    </figure>
			  </body>
			</html>',
	'test_data' => [
		'testShouldNotRewriteAMPWithCdnNullThemeSupport'         => [
			'config'  => [
				'amp_options' => [ 'theme_support' => null ],
				'cdn' => [
					'default' => 0,
					'value'   => 1,
				],
				'cdn_cnames' => [
					'default' => [],
					'value'   => [
						'cdn.example.org',
					],
				],
				'cdn_zone' => [
					'default' => [],
					'value'   => [
						'all',
					],
				],
			],
			'expected' => [
				'shouldRewrite' => false,
			],
		],
		'testShouldNotRewriteAMPWithCdnStandardThemeSupport'     => [
			'config'  => [
				'amp_options' => [ 'theme_support' => 'standard' ],
				'cdn' => [
					'default' => 0,
					'value'   => 1,
				],
				'cdn_cnames' => [
					'default' => [],
					'value'   => [
						'cdn.example.org',
					],
				],
				'cdn_zone' => [
					'default' => [],
					'value'   => [
						'all',
					],
				],
			],
			'expected' => [
				'shouldRewrite' => false,
			],
		],
		'testShouldNotRewriteAMPWithCdnTransitionalThemeSupport' => [
			'config'  => [
				'amp_options' => [ 'theme_support' => 'transitional' ],
				'cdn' => [
					'default' => 0,
					'value'   => 0,
				],
				'cdn_cnames' => [
					'default' => [],
					'value'   => [
						'cdn.example.org',
					],
				],
				'cdn_zone' => [
					'default' => [],
					'value'   => [
						'all',
					],
				],
			],
			'expected' => [
				'shouldRewrite' => false,
			],
		],
		'testShouldRewriteAMPWithCdnTransitionalThemeSupport'    => [
			'config'  => [
				'amp_options' => [ 'theme_support' => 'transitional' ],
				'cdn' => [
					'default' => 0,
					'value'   => 1,
				],
				'cdn_cnames' => [
					'default' => [],
					'value'   => [
						'cdn.example.org',
					],
				],
				'cdn_zone' => [
					'default' => [],
					'value'   => [
						'all',
					],
				],
			],
			'expected' => [
				'shouldRewrite' => true,
			],
		],
		'testShouldRewriteAMPWithCdnReaderThemeSupport'          => [
			'config'  => [
				'amp_options' => [ 'theme_support' => 'reader' ],
				'cdn' => [
					'default' => 0,
					'value'   => 1,
				],
				'cdn_cnames' => [
					'default' => [],
					'value'   => [
						'cdn.example.org',
					],
				],
				'cdn_zone' => [
					'default' => [],
					'value'   => [
						'images',
					],
				],
			],
			'expected' => [
				'shouldRewrite' => true,
			],
		],
	],
];
