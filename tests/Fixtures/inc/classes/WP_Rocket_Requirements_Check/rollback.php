<?php

$constants = [
	'WP_PLUGIN_DIR' => 'vfs://public/wp-content/plugins',
];

return [
	'testShouldEnableMaintenanceModeAroundUpgradeWhenUpgradeSucceeds' => [
		'config'   => [
			'constants'  => $constants,
			'fs_connect' => true,
			'upgrade'    => true,
		],
		'expected' => [
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
			'fs_connect'       => [ 'vfs://public/wp-content', 'vfs://public/wp-content/plugins' ],
			'maintenance_mode' => false,
		],
	],
];
