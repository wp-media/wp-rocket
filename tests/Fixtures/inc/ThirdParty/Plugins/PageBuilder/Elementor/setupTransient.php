<?php
return [
	'ShouldSetupTransient' => [
		'config' => [
			'check'     => null,
			'object_id' => null,
			'meta_key' => '_elementor_conditions',
			'meta_value' => true,
			'prev_value' => false,
			'remove_unused_css' => true,
			'user_id' => 10,
			'boxes' => [
				'maybe_clear_cache_change_notice'
			],
			'change' => true,
		],
		'expected' => [
			'user_id' => 10,
			'transient' => true,
			'boxes' => [
			]
		]
	],
	'SameValueShouldDoNothing' => [
        'config' => [
			  'check'     => null,
              'object_id' => null,
              'meta_key' => '_elementor_conditions',
              'meta_value' => true,
              'prev_value' => true,
			'remove_unused_css' => true,
			'user_id' => 10,
			'boxes' => [
				'maybe_clear_cache_change_notice'
			],
			'change' => false,
		],
		'expected' => [
			'user_id' => 10,
			'transient' => false,
			'boxes' => [
				'maybe_clear_cache_change_notice'
			]
		]
    ],
	'DisabledRUCSSSHouldDoNothing' => [
		'config' => [
			'check'     => null,
			'object_id' => null,
			'meta_key' => '_elementor_conditions',
			'meta_value' => true,
			'prev_value' => false,
			'remove_unused_css' => false,
			'user_id' => 10,
			'boxes' => [
				'maybe_clear_cache_change_notice'
			],
			'change' => false,
		],
		'expected' => [
			'user_id' => 10,
			'transient' => false,
			'boxes' => [
				'maybe_clear_cache_change_notice'
			]
		]
	],
	'WrongMetaShouldDoNothing' => [
		'config' => [
			'check'     => null,
			'object_id' => null,
			'meta_key' => 'wrong',
			'meta_value' => true,
			'prev_value' => false,
			'remove_unused_css' => true,
			'user_id' => 10,
			'boxes' => [
				'maybe_clear_cache_change_notice'
			],
			'change' => false,
		],
		'expected' => [
			'user_id' => 10,
			'transient' => false,
			'boxes' => [
				'maybe_clear_cache_change_notice'
			]
		]
	],
];
