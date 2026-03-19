<?php
return [
	'test_data' => [
		'shouldClearRecommendationsWhenStatusIsInProgress' => [
			'config'   => [
				'new_status'           => 'in-progress',
				'previous_status'      => 'complete',
				'status'               => 'completed',
				'has_required_metrics' => true,
				'should_fetch'         => true,
			],
			'expected' => [
				'clears_recommendations' => true,
				'fetches_recommendations' => false,
			],
		],

		'shouldFetchWhenStatusIsCompletedAndDataChanged' => [
			'config'   => [
				'new_status'           => 'complete',
				'previous_status'      => 'in-progress',
				'status'               => 'pending',
				'has_required_metrics' => true,
				'should_fetch'         => true,
			],
			'expected' => [
				'clears_recommendations' => false,
				'fetches_recommendations' => true,
			],
		],

		'shouldExtendTransientWhenStatusIsCompletedAndDataUnchanged' => [
			'config'   => [
				'new_status'           => 'complete',
				'previous_status'      => 'in-progress',
				'status'               => 'pending',
				'has_required_metrics' => true,
				'should_fetch'         => false,
			],
			'expected' => [
				'clears_recommendations' => false,
				'fetches_recommendations' => true,
			],
		],

		'shouldSkipFetchWhenStatusIsCompletedAndAlreadyLoading' => [
			'config'   => [
				'new_status'           => 'complete',
				'previous_status'      => 'in-progress',
				'status'               => 'loading',
				'has_required_metrics' => true,
				'should_fetch'         => true,
			],
			'expected' => [
				'clears_recommendations' => false,
				'fetches_recommendations' => true,
			],
		],

		'shouldSkipFetchWhenStatusIsCompletedAndMetricsNotReady' => [
			'config'   => [
				'new_status'           => 'complete',
				'previous_status'      => 'in-progress',
				'status'               => 'pending',
				'has_required_metrics' => false,
				'should_fetch'         => true,
			],
			'expected' => [
				'clears_recommendations' => false,
				'fetches_recommendations' => true,
			],
		],

		'shouldDoNothingWhenStatusIsNoUrl' => [
			'config'   => [
				'new_status'           => 'no-url',
				'previous_status'      => 'in-progress',
				'status'               => 'pending',
				'has_required_metrics' => true,
				'should_fetch'         => true,
			],
			'expected' => [
				'clears_recommendations' => false,
				'fetches_recommendations' => false,
			],
		],

		'shouldFailRecommendationsWhenStatusIsFailed' => [
			'config'   => [
				'new_status'           => 'failed',
				'previous_status'      => 'in-progress',
				'status'               => 'pending',
				'has_required_metrics' => true,
				'should_fetch'         => true,
			],
			'expected' => [
				'clears_recommendations' => false,
				'fetches_recommendations' => false,
				'failed_recommendations' => true,
			],
		],
	],
];
