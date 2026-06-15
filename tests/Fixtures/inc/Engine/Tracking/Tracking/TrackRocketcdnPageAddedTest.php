<?php

return [
	'testShouldNotTrackWhenOptinCannotTrack'    => [
		'config'   => [
			'can_track'   => false,
			'url'         => 'http://example.org/',
			'pages_count' => 1,
			'source'      => 'manual',
		],
		'expected' => [
			'track_called'    => false,
			'can_track_count' => 1,
			'is_homepage'     => null,
		],
	],
	'testShouldTrackHomepageAdded'              => [
		'config'   => [
			'can_track'   => true,
			'home_url'    => 'http://example.org',
			'url'         => 'http://example.org/',
			'pages_count' => 1,
			'source'      => 'add_homepage_button',
		],
		'expected' => [
			'track_called'    => true,
			'can_track_count' => 2,
			'is_homepage'     => true,
		],
	],
	'testShouldTrackNonHomepageAdded'           => [
		'config'   => [
			'can_track'   => true,
			'home_url'    => 'http://example.org',
			'url'         => 'http://example.org/about/',
			'pages_count' => 3,
			'source'      => 'manual',
		],
		'expected' => [
			'track_called'    => true,
			'can_track_count' => 2,
			'is_homepage'     => false,
		],
	],
	'testShouldTrackHomepageAddedFromAdminNotice' => [
		'config'   => [
			'can_track'   => true,
			'home_url'    => 'http://example.org',
			'url'         => 'http://example.org',
			'pages_count' => 1,
			'source'      => 'admin_notices',
		],
		'expected' => [
			'track_called'    => true,
			'can_track_count' => 2,
			'is_homepage'     => true,
		],
	],
];
