<?php

return [
	'shouldReturnSameWhenWhiteLabel' => [
		'config' => [
			'white_label' => true,
			'user' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now + 20 days' ),
				'has_auto_renew' => false,
			] ) ),
			'ocd' => 1,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],

	'shouldReturnSameWhenNotExpired' => [
		'config' => [
			'white_label' => false,
			'user' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now + 20 days' ),
				'has_auto_renew' => false,
			] ) ),
			'ocd' => 1,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
	'shouldReturnSameWhenTransient' => [
		'config' => [
			'white_label' => false,
			'user' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 20 days' ),
				'has_auto_renew' => false,
			] ) ),
			'ocd' => 1,
			'transient' => 1,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
	'shouldReturnUpdatedTitleWhenOCDEnabledAndExpiredSinceLessThan4Days' => [
		'config' => [
			'white_label' => false,
			'user' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 3 days' ),
				'has_auto_renew' => false,
			] ) ),
			'ocd' => 1,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket <span class="awaiting-mod">!</span>'
	],
	'shouldReturnUpdatedTitleWhenOCDEnabledAndExpiredSinceMoreThan15Days' => [
		'config' => [
			'white_label' => false,
			'user' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 20 days' ),
				'has_auto_renew' => false,
			] ) ),
			'ocd' => 1,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket <span class="awaiting-mod">!</span>'
	],
	'shouldReturnSameWhenOCDEnabledAndAutoRenewAndExpiredSinceLessThan4Days' => [
		'config' => [
			'white_label' => false,
			'user' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 3 days' ),
				'has_auto_renew' => true,
			] ) ),
			'ocd' => 1,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
	'shouldReturnUpdatedTitleWhenOCDEnabledAndAutoRenewEnabledAndExpiredSinceMoreThan4Days' => [
		'config' => [
			'white_label' => false,
			'user' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 10 days' ),
				'has_auto_renew' => true,
			] ) ),
			'ocd' => 1,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket <span class="awaiting-mod">!</span>'
	],
	'shouldReturnUpdatedTitleWhenOCDDisabledAndExpiredSinceLessThan4Days' => [
		'config' => [
			'white_label' => false,
			'user' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 3 days' ),
				'has_auto_renew' => false,
			] ) ),
			'ocd' => 0,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket <span class="awaiting-mod">!</span>'
	],
	'shouldReturnSameWhenOCDDisabledAndExpiredSinceMoreThan4Days' => [
		'config' => [
			'white_label' => false,
			'user' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 10 days' ),
				'has_auto_renew' => false,
			] ) ),
			'ocd' => 0,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
	'shouldReturnSameWhenOCDDisabledAndAutoRenewEnabledAndExpiredSinceLessThan4Days' => [
		'config' => [
			'white_label' => false,
			'user' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 3 days' ),
				'has_auto_renew' => true,
			] ) ),
			'ocd' => 0,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
	'shouldReturnUpdatedTitleWhenOCDDisabledAndAutoRenewEnabledAndExpiredSinceMoreThan4DaysAndLessThan15Days' => [
		'config' => [
			'white_label' => false,
			'user' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 10 days' ),
				'has_auto_renew' => true,
			] ) ),
			'ocd' => 0,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket <span class="awaiting-mod">!</span>'
	],
	'shouldReturnSameWhenOCDDisabledAndAutoRenewEnabledAndExpiredSinceMoreThan15Days' => [
		'config' => [
			'white_label' => false,
			'user' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 20 days' ),
				'has_auto_renew' => true,
			] ) ),
			'ocd' => 0,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
];
