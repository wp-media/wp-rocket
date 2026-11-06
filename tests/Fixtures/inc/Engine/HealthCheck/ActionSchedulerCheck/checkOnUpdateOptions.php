<?php

return [
	'testShouldReturnFalseWhenValuesNotSet' => [
		'config' => [
			'old_value' => [],
			'value' => [],
			'found_as_tables' => [
                'wp_actionscheduler_actions',
                'wp_actionscheduler_claims',
                'wp_actionscheduler_groups',
            ],
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenValuesNotChanged' => [
		'config' => [
			'old_value' => [
				'manual_preload' => 1,
				'remove_unused_css' => 1,
			],
			'value' => [
				'manual_preload' => 1,
				'remove_unused_css' => 1,
			],
			'found_as_tables' => [
                'wp_actionscheduler_actions',
                'wp_actionscheduler_claims',
                'wp_actionscheduler_groups',
            ],
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenValuesZero' => [
		'config' => [
			'old_value' => [
				'manual_preload' => 1,
				'remove_unused_css' => 1,
			],
			'value' => [
				'manual_preload' => 0,
				'remove_unused_css' => 0,
			],
			'found_as_tables' => [
                'wp_actionscheduler_actions',
                'wp_actionscheduler_claims',
                'wp_actionscheduler_groups',
            ],
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenDisablingPreload' => [
		'config' => [
			'old_value' => [
				'manual_preload' => 1,
				'remove_unused_css' => 1,
			],
			'value' => [
				'manual_preload' => 0,
				'remove_unused_css' => 1,
			],
			'found_as_tables' => [
                'wp_actionscheduler_actions',
                'wp_actionscheduler_claims',
                'wp_actionscheduler_groups',
            ],
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenDisablingRUCSS' => [
		'config' => [
			'old_value' => [
				'manual_preload' => 1,
				'remove_unused_css' => 1,
			],
			'value' => [
				'manual_preload' => 1,
				'remove_unused_css' => 0,
			],
			'found_as_tables' => [
                'wp_actionscheduler_actions',
                'wp_actionscheduler_claims',
                'wp_actionscheduler_groups',
            ],
		],
		'expected' => false,
	],
	'testShouldReturnTrueWhenRuCSSEnabled' => [
		'config' => [
			'old_value' => [
				'manual_preload' => 0,
				'remove_unused_css' => 0,
			],
			'value' => [
				'manual_preload' => 0,
				'remove_unused_css' => 1,
			],
			'found_as_tables' => [
                'wp_actionscheduler_actions',
                'wp_actionscheduler_claims',
                'wp_actionscheduler_groups',
            ],
		],
		'expected' => true,
	],
	'testShouldReturnTrueWhenPreloadEnabled' => [
		'config' => [
			'old_value' => [
				'manual_preload' => 0,
				'remove_unused_css' => 0,
			],
			'value' => [
				'manual_preload' => 1,
				'remove_unused_css' => 0,
			],
			'found_as_tables' => [
                'wp_actionscheduler_actions',
                'wp_actionscheduler_claims',
                'wp_actionscheduler_groups',
            ],
		],
		'expected' => true,
	],
];
