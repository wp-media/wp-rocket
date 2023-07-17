<?php
return [
    'EmptyShouldPass' => [
        'config' => [
              'action' => '',
              'capacities' => '',
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
			'action' => 'referer_test',
			'capacities' => '',
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
			'action' => 'referer_test',
			'capacities' => 'capacity_test',
			'user_can' => true
		],
		'expected' => [
			'referer' => 'referer_test',
			'capacity' => 'capacity_test',
			'result' => true,
		]
	]
];
