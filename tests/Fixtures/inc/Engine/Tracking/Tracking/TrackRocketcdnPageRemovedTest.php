<?php

return [
	'testShouldNotTrackWhenOptinCannotTrack' => [
		'config'   => [
			'can_track'   => false,
			'url'         => 'http://example.org/',
			'pages_count' => 0,
		],
		'expected' => [
			'track_called'    => false,
			'can_track_count' => 1,
			'is_homepage'     => null,
		],
	],
	'testShouldTrackHomepageRemoved'         => [
		'config'   => [
			'can_track'   => true,
			'home_url'    => 'http://example.org',
			'url'         => 'http://example.org/',
			'pages_count' => 0,
		],
		'expected' => [
			'track_called'    => true,
			'can_track_count' => 2,
			'is_homepage'     => true,
		],
	],
	'testShouldTrackNonHomepageRemoved'      => [
		'config'   => [
			'can_track'   => true,
			'home_url'    => 'http://example.org',
			'url'         => 'http://example.org/contact/',
			'pages_count' => 2,
		],
		'expected' => [
			'track_called'    => true,
			'can_track_count' => 2,
			'is_homepage'     => false,
		],
	],
];
