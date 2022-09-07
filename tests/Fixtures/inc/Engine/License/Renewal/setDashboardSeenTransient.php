<?php

return [
	'shouldDoNothingWhenNotExpired' => [
		'config' => [
			'expired' => false,
			'ocd' => true,
			'transient' => false,
			'expire_date' => strtotime( 'now + 20 days' ),
		],
		'expected' => false,
	],
	'shouldDoNothingWhenOCDDisabled' => [
		'config' => [
			'expired' => true,
			'ocd' => false,
			'transient' => false,
			'expire_date' => strtotime( 'now - 20 days' ),
		],
		'expected' => false,
	],
	'shouldDoNothingWhenTransientSet' => [
		'config' => [
			'expired' => true,
			'ocd' => true,
			'transient' => 1,
			'expire_date' => strtotime( 'now - 20 days' ),
		],
		'expected' => false,
	],
	'shouldSetTransient' => [
		'config' => [
			'expired' => true,
			'ocd' => true,
			'transient' => false,
			'expire_date' => strtotime( 'now - 7 days' ),
		],
		'expected' => true,
	],
];
