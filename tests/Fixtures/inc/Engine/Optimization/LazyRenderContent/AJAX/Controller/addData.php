<?php

$long_array = [
	(object) [
		'type' => 'img',
		'label' => 'lcp',
		'src'   => 'http://example.org/lcp.jpg',
	],
];
$long_array_2 = [
	(object) [
		'type' => 'img',
		'src'   => 'http://example.org/lcp.jpg',
	],
];
for ( $i = 1; $i <= 50; $i++ ) {
	$long_array[] = (object) [
		'label' => 'above-the-fold',
		'type'  => 'img',
		'src'   => 'http://example.org/above-the-fold-' . $i . '.jpg',
	];
	$long_array_2[] = (object) [
		'type' => 'img',
		'src'   => 'http://example.org/above-the-fold-' . $i . '.jpg',
	];
}

return [
	'testShouldBailWhenNotAllowed' => [
		'config'   => [
			'filter'    => false,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'lrc' => [
						(object) [
							'db47c7d69edcf4565baa182deb470091',
							'db47c7d69edcf4565baa182deb470092',
						],
					]
				],
			),
		],
		'expected' => [
			'images_valid_sources' => [],
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'below_the_fold' => json_encode( [
					(object) [
						'db47c7d69edcf4565baa182deb470091',
						'db47c7d69edcf4565baa182deb470092',
					],
				] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
				'error_message'  => ''
			],
			'result'  => false,
			'message' => 'error when adding the entry to the database',
		],
	],
];
