<?php

return [
	'shouldReturnSameWhenNotExpired' => [
		'config' => [
			'expired' => false,
			'auto_renew' => false,
			'expire_date' => strtotime( 'now + 7 days' ),
			'ocd' => 1,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
	'shouldReturnSameWhenAutoRenewAndExpiredSinceLessThan4Days' => [
		'config' => [
			'expired' => true,
			'auto_renew' => true,
			'expire_date' => strtotime( 'now - 3 days' ),
			'ocd' => 0,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
	'shouldReturnSameWhenOCDDisabled' => [
		'config' => [
			'expired' => true,
			'auto_renew' => false,
			'expire_date' => strtotime( 'now - 7 days' ),
			'ocd' => 0,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
	'shouldReturnSameWhenTransient' => [
		'config' => [
			'expired' => true,
			'auto_renew' => false,
			'expire_date' => strtotime( 'now - 7 days' ),
			'ocd' => 1,
			'transient' => 1,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
	'shouldReturnUpdatedTitle' => [
		'config' => [
			'expired' => true,
			'auto_renew' => false,
			'expire_date' => strtotime( 'now - 7 days' ),
			'ocd' => 1,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket <span class="awaiting-mod">!</span>'
	],
];
