<?php

$constants = [
	'WP_ROCKET_FILE'        => 'vfs://public/wp-content/plugins/wp-rocket/wp-rocket.php',
	'WP_ROCKET_LASTVERSION' => '3.0',
	'WP_PLUGIN_DIR'         => 'vfs://public/wp-content/plugins',
];

return [
	'testShouldEnableMaintenanceModeAroundUpgradeWhenUpgradeSucceeds' => [
		'config'   => [
			'constants'  => $constants,
			'fs_connect' => true,
			'upgrade'    => true,
		],
		'expected' => [
			'plugin'           => 'wp-rocket/wp-rocket.php',
			'new_version'      => '3.0',
			'fs_connect'       => [ 'vfs://public/wp-content', 'vfs://public/wp-content/plugins' ],
			'maintenance_mode' => true,
		],
	],
	'testShouldDisableMaintenanceModeWhenUpgradeReturnsFalse' => [
		'config'   => [
			'constants'  => $constants,
			'fs_connect' => true,
			'upgrade'    => false,
		],
		'expected' => [
			'plugin'           => 'wp-rocket/wp-rocket.php',
			'new_version'      => '3.0',
			'fs_connect'       => [ 'vfs://public/wp-content', 'vfs://public/wp-content/plugins' ],
			'maintenance_mode' => true,
		],
	],
	'testShouldDisableMaintenanceModeWhenUpgradeReturnsWPError' => [
		'config'   => [
			'constants'  => $constants,
			'fs_connect' => true,
			'upgrade'    => new WP_Error( 'error', 'Download failed' ),
		],
		'expected' => [
			'plugin'           => 'wp-rocket/wp-rocket.php',
			'new_version'      => '3.0',
			'fs_connect'       => [ 'vfs://public/wp-content', 'vfs://public/wp-content/plugins' ],
			'maintenance_mode' => true,
		],
	],
	'testShouldNotEnableMaintenanceModeWhenFilesystemConnectionFails' => [
		'config'   => [
			'constants'  => $constants,
			'fs_connect' => false,
			'upgrade'    => null,
		],
		'expected' => [
			'plugin'           => 'wp-rocket/wp-rocket.php',
			'new_version'      => '3.0',
			'fs_connect'       => [ 'vfs://public/wp-content', 'vfs://public/wp-content/plugins' ],
			'maintenance_mode' => false,
		],
	],
];
