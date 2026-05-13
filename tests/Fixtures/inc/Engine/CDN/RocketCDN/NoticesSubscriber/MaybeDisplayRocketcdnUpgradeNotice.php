<?php

return [
	'shouldDisplayNoticeOnWpRocketSettingsPage' => [
		'config' => [
			'show_upgrade_notice' => true,
			'role'                => 'administrator',
			'screen'              => 'settings_page_wprocket',
		],
		'expected' => [
			'display' => true,
			'html'    => '<a class="button button-primary" href="' . admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG . '&rocket_source=notice_rocketcdn_upgrade#page_cdn' ) . '">Add your pages now</a>',
		],
	],
	'shouldNotDisplayNoticeWithoutFlag' => [
		'config' => [
			'show_upgrade_notice' => false,
			'role'                => 'administrator',
			'screen'              => 'settings_page_wprocket',
		],
		'expected' => [
			'display' => false,
			'html'    => 'Use RocketCDN for free to boost up to 3 pages',
		],
	],
	'shouldNotDisplayNoticeWhenDismissed' => [
		'config' => [
			'show_upgrade_notice' => true,
			'role'                => 'administrator',
			'screen'              => 'settings_page_wprocket',
			'boxes'               => [ 'rocket_update_notice' ],
		],
		'expected' => [
			'display' => false,
			'html'    => 'Use RocketCDN for free to boost up to 3 pages',
		],
	],
	'shouldDisplayNoticeOnAnyAdminScreen' => [
		'config' => [
			'show_upgrade_notice' => true,
			'role'                => 'administrator',
			'screen'              => 'dashboard',
		],
		'expected' => [
			'display' => true,
			'html'    => 'Use RocketCDN for free to boost up to 3 pages',
		],
	],
	'shouldNotDisplayNoticeWithoutCapability' => [
		'config' => [
			'show_upgrade_notice' => true,
			'role'                => 'editor',
			'screen'              => 'settings_page_wprocket',
		],
		'expected' => [
			'display' => false,
			'html'    => 'Use RocketCDN for free to boost up to 3 pages',
		],
	],
	'shouldNotDisplayNoticeWhenRocketCDNIsActive' => [
		'config' => [
			'show_upgrade_notice'  => true,
			'role'                 => 'administrator',
			'screen'               => 'settings_page_wprocket',
			'rocketcdn_user_token' => 'random_user_token',
		],
		'expected' => [
			'display' => false,
			'html'    => 'Use RocketCDN for free to boost up to 3 pages',
		],
	],
];
