<?php

return [
	'testShouldNotTrackWhenOptinCannotTrack' => [
		'config'   => [
			'can_track' => false,
		],
		'expected' => [
			'track_called'    => false,
			'can_track_count' => 1,
		],
	],
	'testShouldTrackActivation'              => [
		'config'   => [
			'can_track' => true,
		],
		'expected' => [
			'track_called'    => true,
			'can_track_count' => 2,
		],
	],
];
