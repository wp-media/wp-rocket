<?php

return [
	'test_data' => [
		'shouldSetResponseAndReturnTrueOnSuccess'       => [
			'atts'         => [
				'url'     => 'http://example.com/path/to/styles.css',
				'type'    => 'css',
				'content' => 'h1 {color: red;}',
			],
			'success'      => true,
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
				'body'     => '{"code":200,"message":"Warm-up request received succesfully!","contents":""}',
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
				'cookies'  => [],
				'filename' => null,
			],
		],
		'shouldSetErrorAndReturnFalseOnNon200Response'  => [
			'atts'         => [
				'url'     => 'http://example.com/path/to/styles.css',
				'type'    => 'css',
				'content' => 'h1 {color: red;}',
			],
			'success'      => false,
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
		],
		'shouldSetErrorAndReturnFalseOnWPErrorResponse' => [
			'atts'         => [
				'url'     => 'http://example.com/path/to/styles.css',
				'type'    => 'css',
				'content' => 'h1 {color: red;}',
			],
			'success'      => false,
			'mockResponse' => new WP_Error( 500, 'Whoops!' ),
		],
	],
];
