<?php

return [
	'testShouldNotTrackWhenOptinCannotTrack'    => [
		'config'   => [
			'can_track' => false,
			'box'       => 'rocketcdn_install_notice',
		],
		'expected' => [
			'track_called'    => false,
			'can_track_count' => 1,
		],
	],
	'testShouldNotTrackUnknownBox'              => [
		'config'   => [
			'can_track' => true,
			'box'       => 'some_other_notice',
		],
		'expected' => [
			'track_called'    => false,
			'can_track_count' => 1,
		],
	],
	'testShouldTrackRocketcdnInstallNotice'     => [
		'config'   => [
			'can_track' => true,
			'box'       => 'rocketcdn_install_notice',
		],
		'expected' => [
			'track_called'    => true,
			'can_track_count' => 2,
		],
	],
	'testShouldTrackRocketUpdateNotice'         => [
		'config'   => [
			'can_track' => true,
			'box'       => 'rocket_update_notice',
		],
		'expected' => [
			'track_called'    => true,
			'can_track_count' => 2,
		],
	],
];
