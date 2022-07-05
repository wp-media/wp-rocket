<?php

return [
	'shouldReturnSameWhenWhiteLabel' => [
		'config' => [
			'white_label' => true,
			'user' => json_decode( json_encode( [
				'has_auto_renew' => true,
				'licence_expiration' => strtotime( 'now + 20 days' ),
			] ) ),
			'ocd' => 1,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
	'shouldReturnSameWhenAutoRenew' => [
		'config' => [
			'white_label' => false,
			'user' => json_decode( json_encode( [
				'has_auto_renew' => true,
				'licence_expiration' => strtotime( 'now + 20 days' ),
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
				'has_auto_renew' => false,
				'licence_expiration' => strtotime( 'now + 20 days' ),
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
				'has_auto_renew' => false,
				'licence_expiration' => strtotime( 'now - 20 days' ),
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
				'has_auto_renew' => false,
				'licence_expiration' => strtotime( 'now - 20 days' ),
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
				'has_auto_renew' => false,
				'licence_expiration' => strtotime( 'now - 20 days' ),
			] ) ),
			'ocd' => 1,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket <span class="awaiting-mod">!</span>'
	],
];
