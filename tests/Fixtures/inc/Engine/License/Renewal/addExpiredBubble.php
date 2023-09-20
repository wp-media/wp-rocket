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
	'shouldReturnUpdatedTitleWhenOCDEnabledAndExpiredSinceLessThan4Days' => [
		'config' => [
			'expired' => true,
			'auto_renew' => false,
			'expire_date' => strtotime( 'now - 3 days' ),
			'ocd' => 1,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket <span class="awaiting-mod">!</span>'
	],
	'shouldReturnUpdatedTitleWhenOCDEnabledAndExpiredSinceMoreThan15Days' => [
		'config' => [
			'expired' => true,
			'auto_renew' => false,
			'expire_date' => strtotime( 'now - 20 days' ),
			'ocd' => 1,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket <span class="awaiting-mod">!</span>'
	],
	'shouldReturnSameWhenOCDEnabledAndAutoRenewAndExpiredSinceLessThan4Days' => [
		'config' => [
			'expired' => true,
			'auto_renew' => true,
			'expire_date' => strtotime( 'now - 3 days' ),
			'ocd' => 1,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
	'shouldReturnUpdatedTitleWhenOCDEnabledAndAutoRenewEnabledAndExpiredSinceMoreThan4Days' => [
		'config' => [
			'expired' => true,
			'auto_renew' => true,
			'expire_date' => strtotime( 'now - 10 days' ),
			'ocd' => 1,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket <span class="awaiting-mod">!</span>'
	],
	'shouldReturnUpdatedTitleWhenOCDDisabledAndExpiredSinceLessThan4Days' => [
		'config' => [
			'expired' => true,
			'auto_renew' => false,
			'expire_date' => strtotime( 'now - 3 days' ),
			'ocd' => 0,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket <span class="awaiting-mod">!</span>'
	],
	'shouldReturnSameWhenOCDDisabledAndExpiredSinceMoreThan4Days' => [
		'config' => [
			'expired' => true,
			'auto_renew' => false,
			'expire_date' => strtotime( 'now - 10 days' ),
			'ocd' => 0,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
	'shouldReturnSameWhenOCDDisabledAndAutoRenewEnabledAndExpiredSinceLessThan4Days' => [
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
	'shouldReturnUpdatedTitleWhenOCDDisabledAndAutoRenewEnabledAndExpiredSinceMoreThan4DaysAndLessThan15Days' => [
		'config' => [
			'expired' => true,
			'auto_renew' => true,
			'expire_date' => strtotime( 'now - 10 days' ),
			'ocd' => 0,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket <span class="awaiting-mod">!</span>'
	],
	'shouldReturnSameWhenOCDDisabledAndAutoRenewEnabledAndExpiredSinceMoreThan15Days' => [
		'config' => [
			'expired' => true,
			'auto_renew' => true,
			'expire_date' => strtotime( 'now - 20 days' ),
			'ocd' => 0,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
];
