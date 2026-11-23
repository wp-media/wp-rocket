<?php

$response = function ( int $code, string $body ): array {
	return [
		'headers'  => [],
		'body'     => $body,
		'response' => [ 'code' => $code ],
		'cookies'  => [],
		'filename' => '',
	];
};

$error_404   = '{"code":404,"detail":"User not found.","success":false}';
$edge_page   = '<html><head><title>403 Forbidden</title></head><body><center><h1>403 Forbidden</h1></center><hr><center>nginx</center></body></html>';
$user_json   = '{"ID":"1","status":"active"}';
$rejected    = [ 'data' => false, 'flag' => true, 'backoff' => true ];
$no_decision = [ 'data' => false, 'flag' => false, 'backoff' => true ];

return [
	'testShouldFlagTheEdge403Page' => [
		'config'   => [ 'response' => $response( 403, $edge_page ) ],
		'expected' => $rejected,
	],
	'testShouldFlagA403WithEmptyBody' => [
		'config'   => [ 'response' => $response( 403, '' ) ],
		'expected' => $rejected,
	],
	'testShouldFlagThe403JsonError' => [
		'config'   => [ 'response' => $response( 403, '{"code":403,"detail":"You do not have permission.","success":false}' ) ],
		'expected' => $rejected,
	],
	'testShouldFlagThe404JsonError' => [
		'config'   => [ 'response' => $response( 404, $error_404 ) ],
		'expected' => $rejected,
	],
	'testShouldNotFlagA404Page' => [
		'config'   => [ 'response' => $response( 404, '<html><title>404</title></html>' ) ],
		'expected' => $no_decision,
	],
	'testShouldNotFlagA404JsonWithoutSuccess' => [
		'config'   => [ 'response' => $response( 404, '{"detail":"x"}' ) ],
		'expected' => $no_decision,
	],
	'testShouldNotFlagA500' => [
		'config'   => [ 'response' => $response( 500, $error_404 ) ],
		'expected' => $no_decision,
	],
	'testShouldKeepTheFlagOnA500' => [
		'config'   => [
			'flag'     => true,
			'response' => $response( 500, $error_404 ),
		],
		'expected' => $rejected,
	],
	'testShouldKeepTheFlagOnANetworkError' => [
		'config'   => [
			'flag'     => true,
			'response' => new WP_Error( 'http_request_failed', 'error' ),
		],
		'expected' => $rejected,
	],
	'testShouldKeepTheFlagWithoutRequestWhileTheBackoffIsActive' => [
		'config'   => [
			'flag'     => true,
			'backoff'  => true,
			'response' => null,
		],
		'expected' => $rejected,
	],
	'testShouldClearTheFlagOnAValidAnswer' => [
		'config'   => [
			'flag'     => true,
			'response' => $response( 200, $user_json ),
		],
		'expected' => [
			'data'    => json_decode( $user_json ),
			'flag'    => false,
			'backoff' => false,
		],
	],
];
