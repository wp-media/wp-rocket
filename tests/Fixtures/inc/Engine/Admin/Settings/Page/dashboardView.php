<?php

return [
	'testShouldRenderWelcomeBannerOnFreshInstallWithoutCdnSubscription' => [
		'config'   => [
			'dismissed' => false,
		],
		'expected' => [
			'contains'     => [ 'Congratulations!', 'WP Rocket is now activated and already working for you.' ],
			'not_contains' => [ 'NEW!', 'enable RocketCDN for free' ],
		],
	],
	'testShouldNotRenderBannerWhenDismissed' => [
		'config'   => [
			'dismissed' => true,
		],
		'expected' => [
			'contains'     => [ 'Dashboard' ],
			'not_contains' => [ 'Congratulations!', 'wpr-Dashboard-main-notice' ],
		],
	],
];
