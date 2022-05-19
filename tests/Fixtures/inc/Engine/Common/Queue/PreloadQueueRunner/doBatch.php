<?php
return [
	'noActionShouldNoProcess' => [
		'config' => [
			'size' => 1,
			'group' => 'rocket-preload',
			'context' => 'context',
			'claim_actions_ids' => [],
			'claim_id' => 10,
			'action_ids' => [],
			'action_max' => [
				true
			],
			'is_using_object_cache' => false,
			'flush_cache' => false,
		],
		'expected' => 0
	],
	'actionShouldProcess' => [
		'config' => [
			'size' => 1,
			'group' => 'rocket-preload',
			'context' => 'context',
			'claim_id' => 10,
			'claim_actions_ids' => [
				1,
				2,
			],
			'action_ids' => [
				1,
				2
			],
			'action_max' => [
				false,
				true
			],
			'is_using_object_cache' => false,
			'flush_cache' => false,
		],
		'expected' => 2
	],
	'limitReachShouldStop' => [
		'config' => [
			'size' => 1,
			'group' => 'rocket-preload',
			'context' => 'context',
			'claim_actions_ids' => [],
			'claim_id' => 10,
			'action_ids' => [
				1,
				2
			],
			'action_max' => [
				false,
				true
			],
			'is_using_object_cache' => false,
			'flush_cache' => false,
		],
		'expected' => 0
	]
];
