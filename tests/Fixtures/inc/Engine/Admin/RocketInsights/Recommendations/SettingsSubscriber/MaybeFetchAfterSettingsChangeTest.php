<?php

return [
	'test_data' => [
		'shouldNotFetchWhenStatusIsPending' => [
			'config' => [
				'status'                => 'pending',
				'has_relevant_changes'  => true,
				'old_options'           => [ 'minify_css' => 0 ],
				'new_options'           => [ 'minify_css' => 1 ],
			],
			'expected' => [
				'should_fetch' => false,
			],
		],

		'shouldNotFetchWhenStatusIsLoading' => [
			'config' => [
				'status'                => 'loading',
				'has_relevant_changes'  => true,
				'old_options'           => [ 'minify_css' => 0 ],
				'new_options'           => [ 'minify_css' => 1 ],
			],
			'expected' => [
				'should_fetch' => false,
			],
		],

		'shouldNotFetchWhenNoRelevantChanges' => [
			'config' => [
				'status'                => 'completed',
				'has_relevant_changes'  => false,
				'old_options'           => [ 'cache_lifespan' => 10 ],
				'new_options'           => [ 'cache_lifespan' => 20 ],
			],
			'expected' => [
				'should_fetch' => false,
			],
		],

		'shouldNotFetchWhenHashUnchanged' => [
			'config' => [
				'status'                => 'completed',
				'has_relevant_changes'  => true,
				'old_options'           => [ 'minify_css' => 0 ],
				'new_options'           => [ 'minify_css' => 1 ],
			],
			'expected' => [
				'should_fetch' => false,
			],
		],

		'shouldFetchWhenStatusCompletedAndRelevantChangesAndHashChanged' => [
			'config' => [
				'status'                => 'completed',
				'has_relevant_changes'  => true,
				'old_options'           => [ 'minify_css' => 0 ],
				'new_options'           => [ 'minify_css' => 1 ],
			],
			'expected' => [
				'should_fetch' => true,
			],
		],

		'shouldFetchWhenStatusFailedAndRelevantChanges' => [
			'config' => [
				'status'                => 'failed',
				'has_relevant_changes'  => true,
				'old_options'           => [ 'delay_js' => 0 ],
				'new_options'           => [ 'delay_js' => 1 ],
			],
			'expected' => [
				'should_fetch' => true,
			],
		],

		'shouldDetectMultipleRelevantChanges' => [
			'config' => [
				'status'                => 'completed',
				'has_relevant_changes'  => true,
				'old_options'           => [
					'minify_css' => 0,
					'minify_js'  => 0,
					'lazyload'   => 0,
				],
				'new_options'           => [
					'minify_css' => 1,
					'minify_js'  => 1,
					'lazyload'   => 1,
				],
			],
			'expected' => [
				'should_fetch' => true,
			],
		],

		'shouldDetectMixedRelevantAndIrrelevantChanges' => [
			'config' => [
				'status'                => 'completed',
				'has_relevant_changes'  => true,
				'old_options'           => [
					'minify_css'     => 0,
					'cache_lifespan' => 10,
				],
				'new_options'           => [
					'minify_css'     => 1,
					'cache_lifespan' => 20,
				],
			],
			'expected' => [
				'should_fetch' => true,
			],
		],
	],
];
