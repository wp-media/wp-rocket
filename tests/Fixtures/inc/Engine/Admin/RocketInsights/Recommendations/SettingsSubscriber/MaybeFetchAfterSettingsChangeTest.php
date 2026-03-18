<?php

return [
	'test_data' => [
		'shouldNotClearWhenStatusIsPending' => [
			'config' => [
				'status'                => 'pending',
				'has_relevant_changes'  => true,
				'old_options'           => [ 'minify_css' => 0 ],
				'new_options'           => [ 'minify_css' => 1 ],
			],
			'expected' => [
				'should_clear' => false,
			],
		],

		'shouldNotClearWhenStatusIsLoading' => [
			'config' => [
				'status'                => 'loading',
				'has_relevant_changes'  => true,
				'old_options'           => [ 'minify_css' => 0 ],
				'new_options'           => [ 'minify_css' => 1 ],
			],
			'expected' => [
				'should_clear' => false,
			],
		],

		'shouldNotClearWhenNoRelevantChanges' => [
			'config' => [
				'status'                => 'completed',
				'has_relevant_changes'  => false,
				'old_options'           => [ 'cache_lifespan' => 10 ],
				'new_options'           => [ 'cache_lifespan' => 20 ],
			],
			'expected' => [
				'should_clear' => false,
			],
		],

		'shouldClearWhenStatusCompletedAndRelevantChanges' => [
			'config' => [
				'status'                => 'completed',
				'has_relevant_changes'  => true,
				'old_options'           => [ 'minify_css' => 0 ],
				'new_options'           => [ 'minify_css' => 1 ],
			],
			'expected' => [
				'should_clear' => true,
			],
		],

		'shouldClearWhenStatusFailedAndRelevantChanges' => [
			'config' => [
				'status'                => 'failed',
				'has_relevant_changes'  => true,
				'old_options'           => [ 'delay_js' => 0 ],
				'new_options'           => [ 'delay_js' => 1 ],
			],
			'expected' => [
				'should_clear' => true,
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
				'should_clear' => true,
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
				'should_clear' => true,
			],
		],
	],
];
