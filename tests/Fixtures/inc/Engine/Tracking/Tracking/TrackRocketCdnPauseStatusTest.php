<?php

return [
	'testShouldNotTrackWhenOptinCannotTrack' => [
		'config'   => [
			'can_track' => false,
			'status'    => 'paused',
			'trigger'   => 'user_paused',
		],
		'expected' => [
			'track_called'    => false,
			'can_track_count' => 1,
		],
	],
	'testShouldTrackPausedStatus'            => [
		'config'   => [
			'can_track' => true,
			'status'    => 'paused',
			'trigger'   => 'user_paused',
		],
		'expected' => [
			'track_called'    => true,
			'can_track_count' => 2,
		],
	],
	'testShouldTrackActiveStatus'            => [
		'config'   => [
			'can_track' => true,
			'status'    => 'active',
			'trigger'   => 'user_resume',
		],
		'expected' => [
			'track_called'    => true,
			'can_track_count' => 2,
		],
	],
];
