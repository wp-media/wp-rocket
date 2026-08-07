<?php

return [
	'shouldReturnFalseOnRollback' => [
		'config' => [
			'new_version'    => '3.21.0',
			'old_version'    => '3.22.0',
			'existing_files' => [],
			'options'        => false,
		],
		'expected' => [
			'result'        => false,
			'write_called'  => false,
			'delete_called' => false,
		],
	],
	'shouldReturnFalseWhenSameVersion' => [
		'config' => [
			'new_version'    => '3.22.0',
			'old_version'    => '3.22.0',
			'existing_files' => [],
			'options'        => false,
		],
		'expected' => [
			'result'        => false,
			'write_called'  => false,
			'delete_called' => false,
		],
	],
	'shouldReturnFalseWhenBackupAlreadyExists' => [
		'config' => [
			'new_version'    => '3.22.1',
			'old_version'    => '3.22.0',
			'existing_files' => [ '/wp-rocket-config/wp_rocket_settings_backup_3.22.0_2026-06-01-10-00-00.json' ],
			'options'        => false,
		],
		'expected' => [
			'result'        => false,
			'write_called'  => false,
			'delete_called' => false,
		],
	],
	'shouldReturnFalseWhenNoOptions' => [
		'config' => [
			'new_version'    => '3.22.1',
			'old_version'    => '3.22.0',
			'existing_files' => [],
			'options'        => false,
		],
		'expected' => [
			'result'        => false,
			'write_called'  => false,
			'delete_called' => false,
		],
	],
	'shouldReturnFalseWhenWriteFails' => [
		'config' => [
			'new_version'    => '3.22.1',
			'old_version'    => '3.22.0',
			'existing_files' => [],
			'options'        => [ 'version' => '3.22.0', 'minify_css' => 1 ],
			'write_result'   => false,
		],
		'expected' => [
			'result'        => false,
			'write_called'  => true,
			'delete_called' => false,
		],
	],
	'shouldWriteBackupSuccessfullyUnderKeepCount' => [
		'config' => [
			'new_version'    => '3.22.1',
			'old_version'    => '3.22.0',
			'existing_files' => [],
			'options'        => [ 'version' => '3.22.0', 'minify_css' => 1 ],
			'write_result'   => true,
			'all_backups'    => [
				'/wp-rocket-config/wp_rocket_settings_backup_3.22.0_2026-06-29-10-00-00.json',
			],
		],
		'expected' => [
			'result'        => true,
			'write_called'  => true,
			'delete_called' => false,
		],
	],
	'shouldGarbageCollectWhenExceedsKeepCount' => [
		'config' => [
			'new_version'    => '3.22.4',
			'old_version'    => '3.22.3',
			'existing_files' => [],
			'options'        => [ 'version' => '3.22.3', 'minify_css' => 1 ],
			'write_result'   => true,
			'all_backups'    => [
				'/wp-rocket-config/wp_rocket_settings_backup_3.22.3_2026-06-29-10-00-04.json',
				'/wp-rocket-config/wp_rocket_settings_backup_3.22.2_2026-06-29-10-00-03.json',
				'/wp-rocket-config/wp_rocket_settings_backup_3.22.1_2026-06-29-10-00-02.json',
				'/wp-rocket-config/wp_rocket_settings_backup_3.22.0_2026-06-29-10-00-01.json',
				'/wp-rocket-config/wp_rocket_settings_backup_3.21.9_2026-06-29-10-00-00.json',
			],
			'mtimes'        => [
				'/wp-rocket-config/wp_rocket_settings_backup_3.22.3_2026-06-29-10-00-04.json' => 1751189204,
				'/wp-rocket-config/wp_rocket_settings_backup_3.22.2_2026-06-29-10-00-03.json' => 1751189203,
				'/wp-rocket-config/wp_rocket_settings_backup_3.22.1_2026-06-29-10-00-02.json' => 1751189202,
				'/wp-rocket-config/wp_rocket_settings_backup_3.22.0_2026-06-29-10-00-01.json' => 1751189201,
				'/wp-rocket-config/wp_rocket_settings_backup_3.21.9_2026-06-29-10-00-00.json' => 1751189200,
			],
			'files_to_delete' => [
				'/wp-rocket-config/wp_rocket_settings_backup_3.21.9_2026-06-29-10-00-00.json',
			],
		],
		'expected' => [
			'result'        => true,
			'write_called'  => true,
			'delete_called' => true,
		],
	],
];
