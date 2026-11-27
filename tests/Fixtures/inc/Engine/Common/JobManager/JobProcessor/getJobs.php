<?php

return [
	'test_data' => [
		'testRucssBlockedShouldReturnOnlyRocketInsightsRows' => [
			'config'   => [
				'type'      => 'submit',
				'factories' => [
					'rucss'           => [ 'blocked' => true, 'rows' => [ 'rucss-1' ] ],
					'rocket_insights' => [ 'blocked' => false, 'rows' => [ 'ri-1' ] ],
				],
			],
			'expected' => [ 'ri-1' ],
		],
		'testRocketInsightsBlockedShouldReturnOnlyRucssRows' => [
			'config'   => [
				'type'      => 'submit',
				'factories' => [
					'rucss'           => [ 'blocked' => false, 'rows' => [ 'rucss-1' ] ],
					'rocket_insights' => [ 'blocked' => true, 'rows' => [ 'ri-1' ] ],
				],
			],
			'expected' => [ 'rucss-1' ],
		],
		'testBothOpenShouldReturnAllRows' => [
			'config'   => [
				'type'      => 'submit',
				'factories' => [
					'rucss'           => [ 'blocked' => false, 'rows' => [ 'rucss-1' ] ],
					'rocket_insights' => [ 'blocked' => false, 'rows' => [ 'ri-1' ] ],
				],
			],
			'expected' => [ 'rucss-1', 'ri-1' ],
		],
		'testBothBlockedShouldReturnNoRows' => [
			'config'   => [
				'type'      => 'submit',
				'factories' => [
					'rucss'           => [ 'blocked' => true, 'rows' => [ 'rucss-1' ] ],
					'rocket_insights' => [ 'blocked' => true, 'rows' => [ 'ri-1' ] ],
				],
			],
			'expected' => [],
		],
		'testPendingShouldNotBeSkippedWhenBlocked' => [
			'config'   => [
				'type'      => 'pending',
				'factories' => [
					'rucss'           => [ 'blocked' => true, 'rows' => [ 'rucss-pending-1' ] ],
					'rocket_insights' => [ 'blocked' => false, 'rows' => [ 'ri-pending-1' ] ],
				],
			],
			'expected' => [ 'rucss-pending-1', 'ri-pending-1' ],
		],
	],
];
