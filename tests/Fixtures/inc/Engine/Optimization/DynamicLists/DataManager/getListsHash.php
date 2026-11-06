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
		],
	],
	'test_data' => [
		'shouldReturnDataWhenTransient' => [
			'expected' => 'f6adbdb5646961a50985a1c00cdcdbde',
		],
	],
];
