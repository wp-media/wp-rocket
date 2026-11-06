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
		'shouldReturnTrueWhenDataSaved' => [
			'content' => $data,
			'expected' => true,
		],
	],
];
