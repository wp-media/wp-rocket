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
	'shouldReturnSameWhenAutoRenewAndExpiredSinceLessThan4Days' => [
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
	'shouldReturnSameWhenOCDDisabled' => [
		'config' => [
			'white_label' => false,
			'user' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 20 days' ),
				'has_auto_renew' => false,
			] ) ),
			'ocd' => 0,
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
	'shouldReturnUpdatedTitle' => [
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
];
