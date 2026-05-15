<?php

return [
	'shouldDisplayInstallNoticeOnFreshInstall'        => [
		'config'   => [
			'show_upgrade_notice' => true,
			'previous_version'    => '',
			'role'                => 'administrator',
			'screen'              => 'dashboard',
		],
		'expected' => [
			'display' => true,
			'html'    => 'New in WP Rocket: Faster loading for your key pages',
		],
	],
	'shouldNotDisplayInstallNoticeWhenTokenExists'       => [
		'config'   => [
			'show_upgrade_notice'  => true,
			'previous_version'     => '',
			'role'                 => 'administrator',
			'screen'               => 'dashboard',
			'rocketcdn_user_token' => 'some_token',
		],
		'expected' => [
			'display' => false,
			'html'    => 'New in WP Rocket: Faster loading for your key pages',
		],
	],
	'shouldDisplayInstallNoticeWhenDismissButtonDiffers' => [
		'config'   => [
			'show_upgrade_notice' => true,
			'previous_version'    => '',
			'role'                => 'administrator',
			'screen'              => 'dashboard',
			'boxes'               => [ 'rocket_update_notice' ],
		],
		'expected' => [
			'display' => true,
			'html'    => 'New in WP Rocket: Faster loading for your key pages',
		],
	],
	'shouldNotDisplayInstallNoticeWhenDismissed'         => [
		'config'   => [
			'show_upgrade_notice' => true,
			'previous_version'    => '',
			'role'                => 'administrator',
			'screen'              => 'dashboard',
			'boxes'               => [ 'rocketcdn_install_notice' ],
		],
		'expected' => [
			'display' => false,
			'html'    => 'New in WP Rocket: Faster loading for your key pages',
		],
	],
	'shouldDisplayUpgradeNoticeOnAnyScreen'              => [
		'config'   => [
			'show_upgrade_notice' => true,
			'role'                => 'administrator',
			'screen'              => 'dashboard',
		],
		'expected' => [
			'display' => true,
			'html'    => 'Use RocketCDN for free to boost up to 3 pages',
		],
	],
	'shouldNotDisplayUpgradeNoticeWithoutFlag'           => [
		'config'   => [
			'show_upgrade_notice' => false,
			'role'                => 'administrator',
			'screen'              => 'dashboard',
		],
		'expected' => [
			'display' => false,
			'html'    => 'Use RocketCDN for free to boost up to 3 pages',
		],
	],
	'shouldNotDisplayUpgradeNoticeWhenTokenExists'       => [
		'config'   => [
			'show_upgrade_notice'  => true,
			'role'                 => 'administrator',
			'screen'               => 'dashboard',
			'rocketcdn_user_token' => 'some_token',
		],
		'expected' => [
			'display' => false,
			'html'    => 'Use RocketCDN for free to boost up to 3 pages',
		],
	],
	'shouldNotDisplayUpgradeNoticeWhenDismissed'         => [
		'config'   => [
			'show_upgrade_notice' => true,
			'role'                => 'administrator',
			'screen'              => 'dashboard',
			'boxes'               => [ 'rocket_update_notice' ],
		],
		'expected' => [
			'display' => false,
			'html'    => 'Use RocketCDN for free to boost up to 3 pages',
		],
	],
	'shouldNotDisplayUpgradeNoticeWithoutCapability'     => [
		'config'   => [
			'show_upgrade_notice' => true,
			'role'                => 'editor',
			'screen'              => 'dashboard',
		],
		'expected' => [
			'display' => false,
			'html'    => 'Use RocketCDN for free to boost up to 3 pages',
		],
	],
	'shouldDisplayUpgradeNoticeButtonText'               => [
		'config'   => [
			'show_upgrade_notice' => true,
			'role'                => 'administrator',
			'screen'              => 'dashboard',
		],
		'expected' => [
			'display' => true,
			'html'    => 'Add your pages now',
		],
	],
	'shouldDisplayInstallNoticeButtonText'               => [
		'config'   => [
			'show_upgrade_notice' => true,
			'previous_version'    => '',
			'role'                => 'administrator',
			'screen'              => 'dashboard',
		],
		'expected' => [
			'display' => true,
			'html'    => 'Start with my homepage',
		],
	],
];
