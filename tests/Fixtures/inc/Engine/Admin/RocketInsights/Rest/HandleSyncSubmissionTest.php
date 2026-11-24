<?php

return [
	'shouldQueueDesktopUrlWhenSyncSubmissionFails' => [
		'config'   => [
			'enabled'            => true,
			'url'                => 'http://example.org/sync-desktop',
			'is_mobile'          => false,
			'additional_details' => [],
		],
		'expected' => [
			'result_type' => 'int',
			'db_check'    => [
				'exists' => true,
				'status' => 'to-submit',
			],
		],
	],
	'shouldQueueMobileUrlWhenSyncSubmissionFails'  => [
		'config'   => [
			'enabled'            => true,
			'url'                => 'http://example.org/sync-mobile',
			'is_mobile'          => true,
			'additional_details' => [],
		],
		'expected' => [
			'result_type' => 'int',
			'db_check'    => [
				'exists' => true,
				'status' => 'to-submit',
			],
		],
	],
	'shouldNotQueueUrlWhenRocketInsightsDisabled'  => [
		'config'   => [
			'enabled'            => false,
			'url'                => 'http://example.org/sync-disabled',
			'is_mobile'          => false,
			'additional_details' => [],
		],
		'expected' => [
			'result_type' => 'null',
			'db_check'    => [
				'exists' => false,
			],
		],
	],
];
