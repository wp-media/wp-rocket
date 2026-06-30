<?php

return [

	// -------------------------------------------------------------------------
	// Activation notice (fresh install: previous_version = '')
	// -------------------------------------------------------------------------

	'shouldDisplayActivationNoticeOnFreshInstall'            => [
		'config'   => [
			'previous_version' => '',
			'role'             => 'administrator',
			'screen'           => 'dashboard',
		],
		'expected' => [
			'display' => true,
			'html'    => 'is good to go',
		],
	],
	'shouldNotDisplayActivationNoticeWithoutCapability'      => [
		'config'   => [
			'previous_version' => '',
			'role'             => 'editor',
			'screen'           => 'dashboard',
		],
		'expected' => [
			'display' => false,
			'html'    => 'is good to go',
		],
	],
	'shouldNotDisplayActivationNoticeWhenDismissed'          => [
		'config'   => [
			'previous_version' => '',
			'role'             => 'administrator',
			'screen'           => 'dashboard',
			'boxes'            => [ 'rocket_new_user_activation_notice' ],
		],
		'expected' => [
			'display' => false,
			'html'    => 'is good to go',
		],
	],
	'shouldDisplayStartWithMyHomepageButton'                 => [
		'config'   => [
			'previous_version' => '',
			'role'             => 'administrator',
			'screen'           => 'dashboard',
		],
		'expected' => [
			'display' => true,
			'html'    => 'Start with my homepage',
		],
	],
	'shouldDisplayActivationNoticeDismissButton'             => [
		'config'   => [
			'previous_version' => '',
			'role'             => 'administrator',
			'screen'           => 'dashboard',
		],
		'expected' => [
			'display' => true,
			'html'    => 'Dismiss',
		],
	],

	// -------------------------------------------------------------------------
	// Major release notice (existing user upgrading from before 3.22.0)
	// -------------------------------------------------------------------------

	'shouldDisplayMajorReleaseNoticeOnUpgrade'               => [
		'config'   => [
			'previous_version' => '3.21.0',
			'role'             => 'administrator',
			'screen'           => 'dashboard',
		],
		'expected' => [
			'display' => true,
			'html'    => 'Use RocketCDN for free to boost up to 3 pages',
		],
	],
	'shouldNotDisplayMajorReleaseNoticeWithoutCapability'    => [
		'config'   => [
			'previous_version' => '3.21.0',
			'role'             => 'editor',
			'screen'           => 'dashboard',
		],
		'expected' => [
			'display' => false,
			'html'    => 'Use RocketCDN for free to boost up to 3 pages',
		],
	],
	'shouldNotDisplayMajorReleaseNoticeWhenUpgradeNoticePreviouslyDismissed' => [
		'config'   => [
			'previous_version' => '3.21.0',
			'role'             => 'administrator',
			'screen'           => 'dashboard',
			'boxes'            => [ 'rocket_update_notice' ],
		],
		'expected' => [
			'display' => false,
			'html'    => 'Use RocketCDN for free to boost up to 3 pages',
		],
	],
	'shouldNotDisplayMajorReleaseNoticeWhenInstallNoticePreviouslyDismissed' => [
		'config'   => [
			'previous_version' => '3.21.0',
			'role'             => 'administrator',
			'screen'           => 'dashboard',
			'boxes'            => [ 'rocketcdn_install_notice' ],
		],
		'expected' => [
			'display' => false,
			'html'    => 'Use RocketCDN for free to boost up to 3 pages',
		],
	],
	'shouldNotDisplayMajorReleaseNoticeWhenDismissed'        => [
		'config'   => [
			'previous_version' => '3.21.0',
			'role'             => 'administrator',
			'screen'           => 'dashboard',
			'boxes'            => [ 'rocket_major_release_notice_3_22' ],
		],
		'expected' => [
			'display' => false,
			'html'    => 'Use RocketCDN for free to boost up to 3 pages',
		],
	],
	'shouldNotDisplayMajorReleaseNoticeWhenTokenExists'      => [
		'config'   => [
			'previous_version'     => '3.21.0',
			'role'                 => 'administrator',
			'screen'               => 'dashboard',
			'rocketcdn_user_token' => 'some_token',
		],
		'expected' => [
			'display' => false,
			'html'    => 'Use RocketCDN for free to boost up to 3 pages',
		],
	],
	'shouldDisplayMajorReleaseNoticeDuringPatchUpdate'       => [
		'config'   => [
			'previous_version' => '3.22.1',
			'role'             => 'administrator',
			'screen'           => 'dashboard',
		],
		'expected' => [
			'display' => true,
			'html'    => 'Use RocketCDN for free to boost up to 3 pages',
		],
	],
	'shouldDisplayAddYourPagesNowButton'                     => [
		'config'   => [
			'previous_version' => '3.21.0',
			'role'             => 'administrator',
			'screen'           => 'dashboard',
		],
		'expected' => [
			'display' => true,
			'html'    => 'Add your pages now',
		],
	],

	// -------------------------------------------------------------------------
	// No notice — existing user whose previous version is beyond the major release series
	// -------------------------------------------------------------------------

	'shouldNotDisplayAnyNoticeWhenBeyondMajorReleaseSeries' => [
		'config'   => [
			'previous_version' => '3.23.0',
			'role'             => 'administrator',
			'screen'           => 'dashboard',
		],
		'expected' => [
			'display' => false,
			'html'    => 'is good to go',
		],
	],
];
