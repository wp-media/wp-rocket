<?php

return [
	'testShouldNotTrackWhenOptinCannotTrack' => [
		'config'   => [
			'banner'    => 'promo',
			'can_track' => false,
			'referer'   => 'https://example.org/wp-admin/options-general.php?page=wprocket',
		],
		'expected' => [
			'track_called' => false,
		],
	],
	'testShouldIgnoreUnknownBanner' => [
		'config'   => [
			'banner'    => 'unknown',
			'can_track' => true,
			'referer'   => 'https://example.org/wp-admin/options-general.php?page=wprocket',
		],
		'expected' => [
			'track_called' => false,
		],
	],
	'testShouldTrackPromo' => [
		'config'   => [
			'banner'    => 'promo',
			'can_track' => true,
			'referer'   => 'https://example.org/wp-admin/options-general.php?page=wprocket',
		],
		'expected' => [
			'track_called' => true,
			'path'         => 'https://example.org/wp-admin/options-general.php?page=wprocket',
		],
	],
	'testShouldTrackLicenseExpired' => [
		'config'   => [
			'banner'    => 'license_expired',
			'can_track' => true,
			'referer'   => 'https://example.org/wp-admin/options-general.php?page=wprocket',
		],
		'expected' => [
			'track_called' => true,
			'path'         => 'https://example.org/wp-admin/options-general.php?page=wprocket',
		],
	],
	'testShouldTrackTrialExpired' => [
		'config'   => [
			'banner'    => 'trial_expired',
			'can_track' => true,
			'referer'   => 'https://example.org/wp-admin/options-general.php?page=wprocket',
		],
		'expected' => [
			'track_called' => true,
			'path'         => 'https://example.org/wp-admin/options-general.php?page=wprocket',
		],
	],
	'testShouldTrackImagify' => [
		'config'   => [
			'banner'    => 'imagify',
			'can_track' => true,
			'referer'   => 'https://example.org/wp-admin/options-general.php?page=wprocket',
		],
		'expected' => [
			'track_called' => true,
			'path'         => 'https://example.org/wp-admin/options-general.php?page=wprocket',
		],
	],
	'testShouldTrackRocketcdn' => [
		'config'   => [
			'banner'    => 'rocketcdn',
			'can_track' => true,
			'referer'   => 'https://example.org/wp-admin/options-general.php?page=wprocket',
		],
		'expected' => [
			'track_called' => true,
			'path'         => 'https://example.org/wp-admin/options-general.php?page=wprocket',
		],
	],
	'testShouldUseEmptyPathWhenNoReferer' => [
		'config'   => [
			'banner'    => 'promo',
			'can_track' => true,
			'referer'   => false,
		],
		'expected' => [
			'track_called' => true,
			'path'         => '',
		],
	],
];
