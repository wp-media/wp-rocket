<?php

return [
	'testShouldNotTrackWhenOptinCannotTrack'          => [
		'config'   => [
			'can_track' => false,
			'source'    => 'add_homepage_button',
		],
		'expected' => [
			'track_called'    => false,
			'can_track_count' => 1,
		],
	],
	'testShouldTrackWithAddHomepageButtonSource'      => [
		'config'   => [
			'can_track' => true,
			'source'    => 'add_homepage_button',
		],
		'expected' => [
			'track_called'    => true,
			'can_track_count' => 2,
		],
	],
	'testShouldTrackWithAdminNoticesSource'           => [
		'config'   => [
			'can_track' => true,
			'source'    => 'admin_notices',
		],
		'expected' => [
			'track_called'    => true,
			'can_track_count' => 2,
		],
	],
];
