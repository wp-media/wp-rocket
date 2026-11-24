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
	'shouldBackOffWhenFeatureDisabled'             => [
		'config'   => [
			'enabled'            => true,
			'url'                => 'http://example.org/sync-feature-disabled',
			'is_mobile'          => false,
			'additional_details' => [],
			'http'               => [
				'https://saas.wp-rocket.me/performance/' => [
					'response' => [
						'code'    => 503,
						'message' => 'Service Unavailable',
					],
					'headers'  => [ 'retry-after' => '120' ],
					'body'     => '{"code":503,"error":"Feature Disabled","message":"Rocket Insights submissions are temporarily disabled. Please retry later."}',
				],
			],
		],
		'expected' => [
			'result_type'      => 'int',
			'feature_disabled' => [
				'delay'      => 120,
				'second_url' => 'http://example.org/sync-feature-disabled-second',
			],
			'db_check'         => [
				'exists' => true,
				'status' => 'to-submit',
			],
		],
	],
];
