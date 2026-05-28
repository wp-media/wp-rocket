<?php

return [
	'shouldSyncPreviousVersionOnFirstEverUpgrade' => [
		'config'   => [
			'initial_previous_version' => '',
			'new_version'              => '3.22.0',
			'old_version'              => '3.21.3',
		],
		'expected' => [
			'previous_version' => '3.21.3',
		],
	],

	'shouldSyncPreviousVersionWhenPreviousVersionAlreadySet' => [
		'config'   => [
			'initial_previous_version' => '3.20.0',
			'new_version'              => '3.22.0',
			'old_version'              => '3.21.3',
		],
		'expected' => [
			'previous_version' => '3.21.3',
		],
	],

	'shouldSyncPreviousVersionForPatchUpgrade'    => [
		'config'   => [
			'initial_previous_version' => '3.22.0',
			'new_version'              => '3.22.1',
			'old_version'              => '3.22.0',
		],
		'expected' => [
			'previous_version' => '3.22.0',
		],
	],

	'shouldDisplayUpgradeNoticeOnPluginsScreen'   => [
		'config'   => [
			'initial_previous_version' => '',
			'new_version'              => '3.22.0',
			'old_version'              => '3.21.3',
			'screen'                   => 'plugins',
		],
		'expected' => [
			'previous_version' => '3.21.3',
		],
	],
];
