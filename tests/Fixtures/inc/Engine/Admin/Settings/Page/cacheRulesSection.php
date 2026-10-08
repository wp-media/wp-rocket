<?php
return [
	'test_data' => [
		'shouldRenameAdvancedRulesToCacheRules' => [
			'config'   => [
				'page' => 'advanced_cache',
			],
			'expected' => [
				'title'              => 'Cache Rules',
				'last_section'       => 'user_cache_section',
				'user_cache_section' => 'user_cache_section',
			],
		],

		'shouldNotListUserCacheOnAddons'        => [
			'config'   => [
				'page' => 'addons',
			],
			'expected' => [
				'title'              => 'Add-ons',
				'last_section'       => 'addons',
				'user_cache_section' => null,
			],
		],
	],
];
