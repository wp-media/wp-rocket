<?php

return [
	'testShouldRenderLoadingTextWhenNoCachedRecommendations' => [
		'config'   => [
			'recommendations' => false,
		],
		'expected' => [
			'state'        => 'loading',
			'contains'     => [
				'wpr-dash-recs__loading-text',
				'Analyzing your page (~1 min)',
			],
			'not_contains' => [
				'Recommendations loading',
			],
		],
	],
	'testShouldRenderLoadingTextWhenStatusIsPending' => [
		'config'   => [
			'recommendations' => [
				'status'          => 'pending',
				'recommendations' => [],
			],
		],
		'expected' => [
			'state'        => 'loading',
			'contains'     => [
				'wpr-dash-recs__loading-text',
				'Analyzing your page (~1 min)',
			],
			'not_contains' => [
				'Recommendations loading',
			],
		],
	],
];
