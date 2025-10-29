<?php

return [
	'testShouldDoAsExpected' => [
		// Test case 1: Should add homepage when upgrading from < 3.20.0.2 to >= 3.20.1 with no existing pages
		[
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

		// Test case 2: Should NOT add homepage when upgrading from >= 3.20.0.2
		[
			'config'   => [
				'old_version'    => '3.20.0.2',
				'new_version'    => '3.20.1',
				'existing_pages' => 0,
			],
			'expected' => [
				'database_entries' => 0,
				'hook_fired'       => false,
			],
		],

		// Test case 3: Should NOT add homepage when upgrading to < 3.20.1
		[
			'config'   => [
				'old_version'    => '3.20.0.1',
				'new_version'    => '3.20.0.9',
				'existing_pages' => 0,
			],
			'expected' => [
				'database_entries' => 0,
				'hook_fired'       => false,
			],
		],

		// Test case 4: Should NOT add homepage when there are existing pages
		[
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

		// Test case 5: Should add homepage when upgrading from much older version
		[
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

		// Test case 6: Should add homepage when upgrading to higher version than 3.20.1
		[
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
	],
];
