<?php

return [
	'test_data' => [
		'shouldReturnExpectedDataWhenSuccess'           => [
			'config'       => [
				'html'    => 'some html',
				'url'     => 'http://example.com/path/to/style.css',
				'options' => [
					'wpr_email'      => 'rocketeer@wp-rocket.me',
					'wpr_key'        => 'SuperSecretRocketeerKey',
					'rucss_safelist' => [ 'http://example.com/my/safe/css.css' ],
				],
			],
			'mockResponse' => [
				'headers'  => [
					'date'                      => 'Wed, 24 Mar, 2021 14:26:14 GMT',
					'content-type'              => 'application/json',
					'x-powered-by'              => 'PHP/7.4.16',
					'cache-control'             => 'no-cache, private',
					'x-frame-options'           => 'SAMEORIGIN',
					'x-xss-protection'          => '1; mode=block',
					'x-content-type-options'    => 'nosniff',
					'strict-transport-security' => 'max-age=15724800; includeSubDomains'
				],
				'body'     => json_encode(
					[
						'code'     => 200,
						'message'  => 'OK',
						'contents' => [
							'shakedCSS'      => 'h1{color:red;}',
						],
					]
				),
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
				'cookies'  => [],
				'filename' => null,
			],
			'expected'     => [
				'code'            => 200,
				'message'         => 'OK',
				'css'             => 'h1{color:red;}',
			],
		],

		'shouldSetErrorAndReturnFalseOnNon200Response'  => [
			'config'         => [
				'html'    => 'some html',
				'url'     => 'http://example.com/path/to/style.css',
				'options' => [
					'wpr_email'      => 'rocketeer@wp-rocket.me',
					'wpr_key'        => 'SuperSecretRocketeerKey',
					'rucss_safelist' => [ 'http://example.com/my/safe/css.css' ],
				],
			],
			'mockResponse' => [
				'headers'  => [
					'cache-control' => 'max-age=604800',
					'content-type'  => 'text/html; charset=UTF-8',
					'date'          => 'Wed, 24 Mar, 2021 14:26:14 GMT',
				],
				'body'     => <<<HTML
<html lang="en">
        <head>
                <title>404 - Not Found</title>
        </head>
        <body>
                <h1>404 - Not Found</h1>
        </body>
</html>
HTML
,
				'response' => [
					'code'    => 404,
					'message' => 'Not Found',
				],
				'cookies'  => [],
				'filename' => null,
			],
			'expected'     => [
				'code' => 404,
				'message' => 'Not Found'
			],
		],

		'shouldSetErrorAndReturnFalseOnWPErrorResponse' => [
			'config'         => [
				'html'    => 'some html',
				'url'     => 'http://example.com/path/to/style.css',
				'options' => [
					'wpr_email'      => 'rocketeer@wp-rocket.me',
					'wpr_key'        => 'SuperSecretRocketeerKey',
					'rucss_safelist' => [ 'http://example.com/my/safe/css.css' ],
				],
			],
			'mockResponse' => new WP_Error( 500, 'Whoops!' ),
			'expected'     => [
				'code' => 500,
				'message' => 'Whoops!'
			],
		],
	],
];
