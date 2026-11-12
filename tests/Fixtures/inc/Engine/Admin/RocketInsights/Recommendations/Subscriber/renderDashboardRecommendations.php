<?php

return [
	'test_data' => [
		'shouldNotRenderWhenNotAllowed' => [
			'config'   => [
				'is_allowed'      => false,
				'recommendations' => false,
			],
			'expected' => [
				'fetches' => false,
			],
		],
		'shouldFetchThenRenderWhenNotCached' => [
			'config'   => [
				'is_allowed'      => true,
				'recommendations' => false,
			],
			'expected' => [
				'fetches' => true,
			],
		],
		'shouldRenderCachedRecommendationsWithoutFetching' => [
			'config'   => [
				'is_allowed'      => true,
				'recommendations' => [
					'status'          => 'completed',
					'recommendations' => [],
				],
			],
			'expected' => [
				'fetches' => false,
			],
		],
	],
];
