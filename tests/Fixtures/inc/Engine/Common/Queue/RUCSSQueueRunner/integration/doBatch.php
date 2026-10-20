<?php

return [
	'test_data' => [
		'shouldNotInsertClaimWhenGroupIsMissing' => [
			'config'   => [
				'scheduled' => false,
			],
			'expected' => [
				'processed' => 0,
				'executed'  => 0,
			],
		],
		'shouldProcessAndReleaseClaimWhenActionIsDue' => [
			'config'   => [
				'scheduled' => true,
			],
			'expected' => [
				'processed' => 1,
				'executed'  => 1,
			],
		],
	],
];
