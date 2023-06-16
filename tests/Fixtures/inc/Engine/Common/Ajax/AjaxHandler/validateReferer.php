<?php
return [
    'EmptyShouldPass' => [
        'config' => [
              'args' => [],
			  'user_can' => false
        ],
        'expected' => [
			'referer' => 'referer_test',
			'capacity' => 'capacity_test',
			'result' => true,
        ]
    ],
	'RefererAndCapacityShouldCheckAndSucceed' => [
		'config' => [
			'args' => [
				'referer' => 'referer_test',
			],
			'user_can' => false
		],
		'expected' => [
			'referer' => 'referer_test',
			'capacity' => 'capacity_test',
			'result' => true,
		]
	],
	'NoCapacityShouldFail' => [
		'config' => [
			'args' => [
				'referer' => 'referer_test',
				'capacity' => 'capacity_test',
			],
			'user_can' => true
		],
		'expected' => [
			'referer' => 'referer_test',
			'capacity' => 'capacity_test',
			'result' => true,
		]
	]
];
