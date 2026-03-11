<?php

return [
	'testShouldDisplayLoadingStateWhenNoTransientData' => [
		'config'   => [
			'transient_data' => null,
		],
		'expected' => '',
	],

	'testShouldDisplayLoadingStateWhenStatusIsPending' => [
		'config'   => [
			'transient_data' => [
				'status'          => 'pending',
				'recommendations' => [],
				'timestamp'       => time(),
			],
		],
		'expected' => [
			'state'        => 'loading',
			'contains'     => [
				'wpr-recommendations__loading',
			],
			'not_contains' => [
				'wpr-recommendations__list',
			],
		],
	],

	'testShouldDisplayFailedStateWhenStatusIsFailed'   => [
		'config'   => [
			'transient_data' => [
				'status'          => 'failed',
				'recommendations' => [],
				'error_message'   => 'API connection failed',
				'timestamp'       => time(),
			],
		],
		'expected' => [
			'state'        => 'failed',
			'contains'     => [
				'wpr-recommendations__failed',
				'recommendations are currently unavailable',
			],
			'not_contains' => [
				'wpr-recommendations__loading',
				'wpr-recommendations__list',
				'wpr-recommendations__success',
			],
		],
	],

	'testShouldDisplaySuccessStateWhenCompletedWithNoRecommendations' => [
		'config'   => [
			'transient_data' => [
				'status'          => 'completed',
				'recommendations' => [],
				'timestamp'       => time(),
			],
		],
		'expected' => [
			// Note: state is 'completed' but template renders success partial when recommendations are empty.
			'state'        => 'completed',
			'contains'     => [
				'wpr-recommendations__success',
				'All done!',
				'All recommended WP Rocket features are now enabled',
			],
			'not_contains' => [
				'wpr-recommendations__loading',
				'wpr-recommendations__list',
				'wpr-recommendations__failed',
			],
		],
	],

	'testShouldDisplayCompletedStateWithSingleRecommendation' => [
		'config'   => [
			'transient_data' => [
				'status'          => 'completed',
				'recommendations' => [
					[
						'option_slug'    => 'delay_js',
						'priority'       => 10,
						'title'          => 'Enable Delay JavaScript Execution',
						'description'    => 'Defer loading of non-critical JavaScript files.',
						'learn_more_url' => 'https://docs.wp-rocket.me/article/1265-delay-javascript-execution',
						'icon_slug'      => 'javascript',
						'lcp_impact'     => 100,
						'ttfb_impact'    => null,
						'cls_impact'     => null,
						'tbt_impact'     => 25,
					],
				],
				'timestamp'       => time(),
			],
		],
		'expected' => [
			'state'                => 'completed',
			'recommendation_count' => 1,
			'contains'             => [
				'wpr-recommendations__list',
				'Enable Delay JavaScript Execution',
				'Defer loading of non-critical JavaScript files.',
				'More info',
				'Activate',
			],
			'impact_tags'          => [
				'lcp',
				'tbt',
			],
			'show_load_more'       => false,
			'not_contains'         => [
				'wpr-recommendations__loading',
				'wpr-recommendations__failed',
				'wpr-recommendations__success',
			],
		],
	],

	'testShouldDisplayCompletedStateWithMultipleRecommendations' => [
		'config'   => [
			'transient_data' => [
				'status'          => 'completed',
				'recommendations' => [
					[
						'option_slug'    => 'delay_js',
						'priority'       => 10,
						'title'          => 'Enable Delay JavaScript Execution',
						'description'    => 'Defer loading of non-critical JavaScript files.',
						'learn_more_url' => 'https://docs.wp-rocket.me/delay-js',
						'icon_slug'      => 'javascript',
						'lcp_impact'     => 100,
						'ttfb_impact'    => null,
						'cls_impact'     => null,
						'tbt_impact'     => 25,
					],
					[
						'option_slug'    => 'remove_unused_css',
						'priority'       => 20,
						'title'          => 'Remove Unused CSS',
						'description'    => 'Reduce page weight by removing unused CSS.',
						'learn_more_url' => 'https://docs.wp-rocket.me/rucss',
						'icon_slug'      => 'css',
						'lcp_impact'     => 80,
						'ttfb_impact'    => null,
						'cls_impact'     => 15,
						'tbt_impact'     => null,
					],
					[
						'option_slug'    => 'lazyload_images',
						'priority'       => 30,
						'title'          => 'Enable LazyLoad for Images',
						'description'    => 'Load images only when they enter the viewport.',
						'learn_more_url' => 'https://docs.wp-rocket.me/lazyload',
						'icon_slug'      => 'image',
						'lcp_impact'     => 60,
						'ttfb_impact'    => null,
						'cls_impact'     => null,
						'tbt_impact'     => null,
					],
				],
				'timestamp'       => time(),
			],
		],
		'expected' => [
			'state'                => 'completed',
			'recommendation_count' => 3,
			'contains'             => [
				'Enable Delay JavaScript Execution',
				'Remove Unused CSS',
				'Enable LazyLoad for Images',
			],
			'show_load_more'       => false,
		],
	],

	'testShouldShowLoadMoreWhenMoreThanThreeRecommendations' => [
		'config'   => [
			'transient_data' => [
				'status'          => 'completed',
				'recommendations' => [
					[
						'option_slug'    => 'delay_js',
						'priority'       => 10,
						'title'          => 'Enable Delay JavaScript Execution',
						'description'    => 'Defer loading of non-critical JavaScript files.',
						'learn_more_url' => 'https://docs.wp-rocket.me/delay-js',
						'icon_slug'      => 'javascript',
						'lcp_impact'     => 100,
						'ttfb_impact'    => null,
						'cls_impact'     => null,
						'tbt_impact'     => null,
					],
					[
						'option_slug'    => 'remove_unused_css',
						'priority'       => 20,
						'title'          => 'Remove Unused CSS',
						'description'    => 'Reduce page weight by removing unused CSS.',
						'learn_more_url' => 'https://docs.wp-rocket.me/rucss',
						'icon_slug'      => 'css',
						'lcp_impact'     => 80,
						'ttfb_impact'    => null,
						'cls_impact'     => null,
						'tbt_impact'     => null,
					],
					[
						'option_slug'    => 'lazyload_images',
						'priority'       => 30,
						'title'          => 'Enable LazyLoad for Images',
						'description'    => 'Load images only when they enter the viewport.',
						'learn_more_url' => 'https://docs.wp-rocket.me/lazyload',
						'icon_slug'      => 'image',
						'lcp_impact'     => null,
						'ttfb_impact'    => null,
						'cls_impact'     => null,
						'tbt_impact'     => null,
					],
					[
						'option_slug'    => 'minify_css',
						'priority'       => 40,
						'title'          => 'Minify CSS Files',
						'description'    => 'Reduce file size by removing unnecessary characters.',
						'learn_more_url' => 'https://docs.wp-rocket.me/minify-css',
						'icon_slug'      => 'minify',
						'lcp_impact'     => null,
						'ttfb_impact'    => 10,
						'cls_impact'     => null,
						'tbt_impact'     => null,
					],
				],
				'timestamp'       => time(),
			],
		],
		'expected' => [
			'state'                => 'completed',
			'recommendation_count' => 4,
			'contains'             => [
				'Enable Delay JavaScript Execution',
				'Remove Unused CSS',
				'Enable LazyLoad for Images',
				'Minify CSS Files',
				'More Recommendations',
			],
			'show_load_more'       => true,
		],
	],

	'testShouldDisplayAllImpactTags'                   => [
		'config'   => [
			'transient_data' => [
				'status'          => 'completed',
				'recommendations' => [
					[
						'option_slug'    => 'test_option',
						'priority'       => 10,
						'title'          => 'Test Recommendation',
						'description'    => 'This recommendation has all impact metrics.',
						'learn_more_url' => 'https://docs.wp-rocket.me/test',
						'icon_slug'      => 'test',
						'lcp_impact'     => 100,
						'ttfb_impact'    => 50,
						'cls_impact'     => 25,
						'tbt_impact'     => 75,
					],
				],
				'timestamp'       => time(),
			],
		],
		'expected' => [
			'state'       => 'completed',
			'impact_tags' => [
				'lcp',
				'ttfb',
				'cls',
				'tbt',
			],
		],
	],

	'testShouldNotShowImpactTagsWhenAllNull'           => [
		'config'   => [
			'transient_data' => [
				'status'          => 'completed',
				'recommendations' => [
					[
						'option_slug'    => 'test_option',
						'priority'       => 10,
						'title'          => 'Test Recommendation Without Impact',
						'description'    => 'This recommendation has no impact metrics.',
						'learn_more_url' => 'https://docs.wp-rocket.me/test',
						'icon_slug'      => 'test',
						'lcp_impact'     => null,
						'ttfb_impact'    => null,
						'cls_impact'     => null,
						'tbt_impact'     => null,
					],
				],
				'timestamp'       => time(),
			],
		],
		'expected' => [
			'state'        => 'completed',
			'contains'     => [
				'Test Recommendation Without Impact',
			],
			'not_contains' => [
				'wpr-recommendation-item__impact-tags',
			],
		],
	],
];
