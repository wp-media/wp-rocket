<?php

return [
	'shouldReturnSameWhenAutoRenew' => [
		'config' => [
			'auto_renew' => true,
			'expired' => false,
			'ocd' => 1,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
	'shouldReturnSameWhenNotExpired' => [
		'config' => [
			'auto_renew' => false,
			'expired' => false,
			'ocd' => 1,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
	'shouldReturnSameWhenOCDDisabled' => [
		'config' => [
			'auto_renew' => false,
			'expired' => true,
			'ocd' => 0,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
	'shouldReturnSameWhenTransient' => [
		'config' => [
			'auto_renew' => false,
			'expired' => true,
			'ocd' => 1,
			'transient' => 1,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket'
	],
	'shouldReturnUpdatedTitle' => [
		'config' => [
			'auto_renew' => false,
			'expired' => true,
			'ocd' => 1,
			'transient' => false,
		],
		'title' => 'WP Rocket',
		'expected' => 'WP Rocket <span class="awaiting-mod">!</span>'
	],
];
