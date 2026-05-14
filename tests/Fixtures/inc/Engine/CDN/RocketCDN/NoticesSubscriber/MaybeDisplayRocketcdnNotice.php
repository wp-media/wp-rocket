<?php

return [
	'shouldDisplayInstallNoticeOnFreshInstall'        => [
		'config'   => [
			'show_upgrade_notice' => true,
			'previous_version'    => '', // empty = fresh install
			'role'                => 'administrator',
			'screen'              => 'dashboard',
		],
		'expected' => [
			'display' => true,
			'html'    => 'New in WP Rocket: Faster loading for your key pages',
		],
	],
	'shouldNotDisplayInstallNoticeWhenTokenExists'    => [
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
	'shouldNotDisplayInstallNoticeWhenDismissed'      => [
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
];
