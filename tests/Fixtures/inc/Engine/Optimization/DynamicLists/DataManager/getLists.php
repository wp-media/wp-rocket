<?php
$data = json_encode( [
	'rucss_exclusions' => [
		'.wp-container-',
		'.wp-elements-',
	],
] );

return [
	'structure' => [
		'wp-content' => [
			'wp-rocket-config' => [
				'dynamic-lists.json' => $data,
			],
			'plugins' => [
				'wp-rocket' => [
					'dynamic-lists.json' => $data,
				],
			],
		],
	],
	'test_data' => [
		'shouldReturnDataWhenTransient' => [
			'transient' => json_decode( $data ),
			'fallback' => false,
			'expected' => json_decode( $data ),
		],
		'shouldReturnDataWhenFileExists' => [
			'transient' => false,
			'fallback' => false,
			'expected' => json_decode( $data ),
		],
		'shouldReturnDataWhenFallback' => [
			'transient' => false,
			'fallback' => true,
			'expected' => json_decode( $data ),
		],
	],
];
