<?php

$json = '{"licenses":{"single":{"prices":{"regular":49,"sale":39.2,"renewal":{"is_grandfather":24.5,"not_grandfather":34.3,"is_expired":39.2}},"websites":1},"plus":{"prices":{"regular":99,"sale":79.2,"from_single":{"regular":50,"sale":40},"renewal":{"is_grandfather":49.5,"not_grandfather":69.3,"is_expired":79.2}},"websites":3},"infinite":{"prices":{"regular":249,"sale":199.2,"from_single":{"regular":200,"sale":160},"from_plus":{"regular":150,"sale":120},"renewal":{"is_grandfather":124.5,"not_grandfather":174.3,"is_expired":199.2}},"websites":"unlimited"}},"renewals":{"extra_days":90,"grandfather_date":1567296000,"discount_percent":{"is_grandfather":50,"not_grandfather":30,"is_expired":20}},"promo":{"name":"Halloween","discount_percent":20,"start_date":1603756800,"end_date":1604361600}}';

$data = json_decode( $json );

return [
	'testShouldReturnDataWhenCached' => [
		'config'   => [
			'pricing-transient' => true,
			'timeout-active'    => false,
			'timeout-duration'  => false,
			'response'          => false,
		],
		'expected' => [
			'result' => $data,
		]
	],

	'testShouldReturnFalseWhenTimeoutActive' => [
		'config'   => [
			'pricing-transient' => false,
			'timeout-active'    => true,
			'timeout-duration'  => false,
			'response'          => false,
		],
		'expected' => [
			'result' => false,
		],
	],

	'testReturnFalseWhenWPError' => [
		'config'   => [
			'pricing-transient' => false,
			'timeout-active'    => false,
			'timeout-duration'  => false,
			'response'          => new WP_Error( 'http_request_failed', 'error' ),
		],
		'expected' => [
			'result' => false,
		],
	],

	'testShouldReturnFalseWhenNot200' => [
		'config'   => [
			'pricing-transient' => false,
			'timeout-active'    => false,
			'timeout-duration'  => false,
			'response'  => [
				'headers' => [],
				'body' => 'error 404',
				'response' => [
					'code' => 404,
				],
				'cookies' => [],
				'filename' => '',
			],
		],
		'expected' => [
			'result' => false,
		],
	],

	'testShouldReturnFalseWhenNoBody' => [
		'config'   => [
			'pricing-transient' => false,
			'timeout-active'    => false,
			'timeout-duration'  => false,
			'response'  => [
				'headers' => [],
				'body' => '',
				'response' => [
					'code' => 200,
				],
				'cookies' => [],
				'filename' => '',
			],
		],
		'expected' => [
			'result' => false,
		],
	],

	'testShouldReturnDataWhenSuccess' => [
		'config'   => [
			'pricing-transient' => false,
			'timeout-active'    => false,
			'timeout-duration'  => false,
			'response'  => [
				'headers' => [],
				'body' => $json,
				'response' => [
					'code' => 200,
				],
				'cookies' => [],
				'filename' => '',
			],
		],
		'expected' => [
			'result' => $data,
		],
	],

	'testShouldDoubleTimeoutDurationFromPreviousDuration' => [
		'config'   => [
			'pricing-transient' => false,
			'timeout-active'    => false,
			'timeout-duration'  => 300,
			'response'  => [
				'headers' => [],
				'body' => 'error 404',
				'response' => [
					'code' => 404,
				],
				'cookies' => [],
				'filename' => '',
			],
		],
		'expected' => [
			'result'           => false,
			'timeout-duration' => 600
		],
	],

	'testShouldNotSetTimeoutDurationLongerThanADay' => [
		'config'   => [
			'pricing-transient' => false,
			'timeout-active'    => false,
			'timeout-duration'  => rocket_get_constant( 'DAY_IN_SECONDS' )
								   - rocket_get_constant( 'HOUR_IN_SECONDS' ),
			'response'  => [
				'headers' => [],
				'body' => 'error 404',
				'response' => [
					'code' => 404,
				],
				'cookies' => [],
				'filename' => '',
			],
		],
		'expected' => [
			'result'           => false,
			'timeout-duration' => rocket_get_constant( 'DAY_IN_SECONDS' ),
		],
	],
];
