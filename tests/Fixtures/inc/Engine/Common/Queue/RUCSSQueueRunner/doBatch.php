<?php

return [
	'test_data' => [
		'shouldNotClaimWhenGroupIsMissing' => [
			'config'   => [
				'store'     => 'db',
				'scheduled' => false,
			],
			'expected' => 0,
		],
		'shouldClaimAndProcessWhenActionIsDue' => [
			'config'   => [
				'store'     => 'db',
				'scheduled' => true,
			],
			'expected' => 1,
		],
		'shouldNotClaimWhenGroupIsMissingInHybridStoreNonRegression' => [
			'config'   => [
				'store'     => 'hybrid',
				'scheduled' => false,
			],
			'expected' => 0,
		],
	],
];
