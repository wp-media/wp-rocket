<?php
return [
    'disabledShouldBailOut' => [
        'config' => [
			'enabled' => false,
			'rows_count' => 100,
        ],
		'expected' => [
			'rows_count' => 100,
			'in_progress' => null
		]
    ],
	'noPendingShouldBailOut' => [
		'configs' => [
			'rows_count' => 100,
			'enabled' => true,
			'rows' => [
			]
		],
		'expected' => [
			'rows_count' => 100,
			'in_progress' => null,
		]
	],
	'pendingShouldPassInProgress' => [
		'configs' => [
			'rows_count' => 100,
			'enabled' => true,
			'rows' => [
				(object) [
					'id' => 10,
					'url' => 'http://example.org'
				]
			]
		],
		'expected' => [
			'rows_count' => 100,
			'in_progress' => 10,
		]
	]
];
