<?php

return [
	'testShouldAddHomepageWhenUpgradingFromOldVersionToNew' => [
		'config'   => [
			'old_version'    => '3.20.0.1',
			'new_version'    => '3.20.1',
			'existing_pages' => 0,
		],
		'expected' => [
			'database_entries' => 1,
			'hook_fired'       => true,
		],
	],
	'testShouldNotAddHomepageWhenUpgradingFromRecentVersion' => [
		'config'   => [
			'old_version'    => '3.20.1',
			'new_version'    => '3.20.2',
			'existing_pages' => 0,
		],
		'expected' => [
			'database_entries' => 0,
			'hook_fired'       => false,
		],
	],
	'testShouldAddHomepageWhenUpgradingToBelowTargetVersion' => [
		'config'   => [
			'old_version'    => '3.20.0.1',
			'new_version'    => '3.20.0.9',
			'existing_pages' => 0,
		],
		'expected' => [
			'database_entries' => 1,
			'hook_fired'       => true,
		],
	],
	'testShouldNotAddHomepageWhenExistingPagesPresent' => [
		'config'   => [
			'old_version'    => '3.20.0.1',
			'new_version'    => '3.20.1',
			'existing_pages' => 2,
		],
		'expected' => [
			'database_entries' => 2,
			'hook_fired'       => false,
		],
	],
	'testShouldAddHomepageWhenUpgradingFromMuchOlderVersion' => [
		'config'   => [
			'old_version'    => '3.19.0',
			'new_version'    => '3.20.1',
			'existing_pages' => 0,
		],
		'expected' => [
			'database_entries' => 1,
			'hook_fired'       => true,
		],
	],
	'testShouldAddHomepageWhenUpgradingToHigherVersion' => [
		'config'   => [
			'old_version'    => '3.20.0.1',
			'new_version'    => '3.21.0',
			'existing_pages' => 0,
		],
		'expected' => [
			'database_entries' => 1,
			'hook_fired'       => true,
		],
	],
];
