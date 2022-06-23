<?php

return [
	'shouldDoNothingWhenAutoRenew' => [
		'config' => [
			'auto_renew' => true,
			'expired' => false,
			'ocd' => true,
			'transient' => false,
			'expire_date' => strtotime( 'now - 20 days' ),
		],
		'expected' => false,
	],
	'shouldDoNothingWhenNotExpired' => [
		'config' => [
			'auto_renew' => false,
			'expired' => false,
			'ocd' => true,
			'transient' => false,
			'expire_date' => strtotime( 'now + 20 days' ),
		],
		'expected' => false,
	],
	'shouldDoNothingWhenOCDDisabled' => [
		'config' => [
			'auto_renew' => false,
			'expired' => true,
			'ocd' => false,
			'transient' => false,
			'expire_date' => strtotime( 'now - 20 days' ),
		],
		'expected' => false,
	],
	'shouldDoNothingWhenTransientSet' => [
		'config' => [
			'auto_renew' => false,
			'expired' => true,
			'ocd' => true,
			'transient' => 1,
			'expire_date' => strtotime( 'now - 20 days' ),
		],
		'expected' => false,
	],
	'shouldSetTransient' => [
		'config' => [
			'auto_renew' => false,
			'expired' => true,
			'ocd' => true,
			'transient' => false,
			'expire_date' => strtotime( 'now - 7 days' ),
		],
		'expected' => true,
	],
];
