<?php

return [
	'test_data' => [
		'shouldNotClaimWhenGroupIsMissing' => [
			'config'   => [
				'store'       => 'db',
				'scheduled'   => false,
				'query_error' => false,
			],
			'expected' => 0,
		],
		'shouldClaimAndProcessWhenActionIsDue' => [
			'config'   => [
				'store'       => 'db',
				'scheduled'   => true,
				'query_error' => false,
			],
			'expected' => 1,
		],
		'shouldNotClaimAndLogErrorWhenQueryFails' => [
			'config'   => [
				'store'       => 'db',
				'scheduled'   => true,
				'query_error' => true,
			],
			'expected' => 0,
		],
		'shouldNotClaimWhenGroupIsMissingInHybridStoreNonRegression' => [
			'config'   => [
				'store'       => 'hybrid',
				'scheduled'   => false,
				'query_error' => false,
			],
			'expected' => 0,
		],
	],
];
