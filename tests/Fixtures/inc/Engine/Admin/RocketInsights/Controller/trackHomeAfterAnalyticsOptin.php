<?php

return [
	'test_data' => [
		'shouldBailOutWhenStatusIsFalse' => [
			'config' => [
				'status' => false,
			],
			'expected' => [
				'should_track' => false,
			],
		],

		'shouldBailOutWhenAnalyticsNoticeAlreadyDisplayed' => [
			'config' => [
				'status'           => true,
				'notice_displayed' => 1,
			],
			'expected' => [
				'should_track' => false,
			],
		],

		'shouldBailOutWhenRowDetailsIsEmpty' => [
			'config' => [
				'status'           => true,
				'notice_displayed' => 0,
				'home_url'         => 'https://example.com',
				'row_details'      => null,
			],
			'expected' => [
				'should_track' => false,
			],
		],

		'shouldBailOutWhenTestStatusIsToSubmit' => [
			'config' => [
				'status'           => true,
				'notice_displayed' => 0,
				'home_url'         => 'https://example.com',
				'row_details'      => (object) [
					'id'     => 1,
					'url'    => 'https://example.com',
					'status' => 'to-submit',
				],
			],
			'expected' => [
				'should_track' => false,
			],
		],
		'shouldBailOutWhenTestStatusIsPending' => [
			'config' => [
				'status'           => true,
				'notice_displayed' => 0,
				'home_url'         => 'https://example.com',
				'row_details'      => (object) [
					'id'     => 1,
					'url'    => 'https://example.com',
					'status' => 'pending',
				],
			],
			'expected' => [
				'should_track' => false,
			],
		],

		'shouldBailOutWhenTestStatusIsInProgress' => [
			'config' => [
				'status'           => true,
				'notice_displayed' => 0,
				'home_url'         => 'https://example.com',
				'row_details'      => (object) [
					'id'     => 1,
					'url'    => 'https://example.com',
					'status' => 'in-progress',
				],
			],
			'expected' => [
				'should_track' => false,
			],
		],

		'shouldTrackWhenTestStatusIsCompleted' => [
			'config' => [
				'status'           => true,
				'notice_displayed' => 0,
				'home_url'         => 'https://example.com',
				'row_details'      => (object) [
					'id'     => 1,
					'url'    => 'https://example.com',
					'status' => 'completed',
				],
				'current_plan'     => 'perf-monitor-free',
			],
			'expected' => [
				'should_track' => true,
			],
		],

		'shouldTrackWhenTestStatusIsFailed' => [
			'config' => [
				'status'           => true,
				'notice_displayed' => 0,
				'home_url'         => 'https://example.com',
				'row_details'      => (object) [
					'id'     => 1,
					'url'    => 'https://example.com',
					'status' => 'failed',
				],
				'current_plan'     => 'perf-monitor-free',
			],
			'expected' => [
				'should_track' => true,
			],
		],
	],
];
