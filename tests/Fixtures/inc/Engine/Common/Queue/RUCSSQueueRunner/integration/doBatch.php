<?php

return [
	'test_data' => [
		'shouldNotInsertClaimWhenGroupIsMissing' => [
			'config'   => [
				'store'     => 'db',
				'scheduled' => false,
			],
			'expected' => [
				'processed' => 0,
				'executed'  => 0,
			],
		],
		'shouldProcessAndReleaseClaimWhenActionIsDue' => [
			'config'   => [
				'store'     => 'db',
				'scheduled' => true,
			],
			'expected' => [
				'processed' => 1,
				'executed'  => 1,
			],
		],
		'shouldNotInsertClaimWhenGroupIsMissingInHybridStoreNonRegression' => [
			'config'   => [
				'store'     => 'hybrid',
				'scheduled' => false,
			],
			'expected' => [
				'processed' => 0,
				'executed'  => 0,
			],
		],
	],
];
