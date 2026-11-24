<?php

$error_404 = '{"code":404,"detail":"User not found.","success":false}';

return [
	'testShouldBeValidAndClearTheFlagOn200' => [
		'config'   => [
			'code'    => 200,
			'body'    => '{"ID":"1","status":"active"}',
			'flagged' => true,
		],
		'expected' => [
			'valid'   => true,
			'flagged' => false,
		],
	],
	'testShouldBeValidAndClearTheFlagOn202' => [
		'config'   => [
			'code'    => 202,
			'body'    => '',
			'flagged' => true,
		],
		'expected' => [
			'valid'   => true,
			'flagged' => false,
		],
	],
	'testShouldBeInvalidAndFlagTheEdge403Page' => [
		'config'   => [
			'code'    => 403,
			'body'    => '<html><head><title>403 Forbidden</title></head><body><center><h1>403 Forbidden</h1></center><hr><center>nginx</center></body></html>',
			'flagged' => false,
		],
		'expected' => [
			'valid'   => false,
			'flagged' => true,
		],
	],
	'testShouldBeInvalidAndFlagA403WithEmptyBody' => [
		'config'   => [
			'code'    => 403,
			'body'    => '',
			'flagged' => false,
		],
		'expected' => [
			'valid'   => false,
			'flagged' => true,
		],
	],
	'testShouldBeInvalidAndFlagThe403JsonError' => [
		'config'   => [
			'code'    => 403,
			'body'    => '{"code":403,"detail":"You do not have permission.","success":false}',
			'flagged' => false,
		],
		'expected' => [
			'valid'   => false,
			'flagged' => true,
		],
	],
	'testShouldBeInvalidAndFlagThe404JsonError' => [
		'config'   => [
			'code'    => 404,
			'body'    => $error_404,
			'flagged' => false,
		],
		'expected' => [
			'valid'   => false,
			'flagged' => true,
		],
	],
	'testShouldBeInvalidAndNotFlagA404Page' => [
		'config'   => [
			'code'    => 404,
			'body'    => '<html><title>404</title></html>',
			'flagged' => false,
		],
		'expected' => [
			'valid'   => false,
			'flagged' => false,
		],
	],
	'testShouldBeInvalidAndNotFlagA404JsonWithoutSuccess' => [
		'config'   => [
			'code'    => 404,
			'body'    => '{"detail":"x"}',
			'flagged' => false,
		],
		'expected' => [
			'valid'   => false,
			'flagged' => false,
		],
	],
	'testShouldBeInvalidAndNotFlagA404JsonScalar' => [
		'config'   => [
			'code'    => 404,
			'body'    => 'false',
			'flagged' => false,
		],
		'expected' => [
			'valid'   => false,
			'flagged' => false,
		],
	],
	'testShouldBeInvalidAndNotFlagA500' => [
		'config'   => [
			'code'    => 500,
			'body'    => $error_404,
			'flagged' => false,
		],
		'expected' => [
			'valid'   => false,
			'flagged' => false,
		],
	],
	'testShouldBeInvalidAndKeepTheFlagOnA500' => [
		'config'   => [
			'code'    => 500,
			'body'    => $error_404,
			'flagged' => true,
		],
		'expected' => [
			'valid'   => false,
			'flagged' => true,
		],
	],
];
