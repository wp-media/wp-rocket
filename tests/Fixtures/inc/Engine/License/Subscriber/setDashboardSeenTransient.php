<?php

return [
	'shouldDoNothingWhenNotExpired' => [
		'config' => [
			'user' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now + 20 days' ),
			] ) ),
			'ocd' => true,
			'transient' => false,
		],
		'expected' => false,
	],
	'shouldDoNothingWhenOCDDisabled' => [
		'config' => [
			'user' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 20 days' ),
			] ) ),
			'ocd' => false,
			'transient' => false,
		],
		'expected' => false,
	],
	'shouldDoNothingWhenTransientSet' => [
		'config' => [
			'user' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 20 days' ),
			] ) ),
			'ocd' => true,
			'transient' => 1,
		],
		'expected' => 1,
	],
	'shouldSetTransient' => [
		'config' => [
			'user' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 20 days' ),
			] ) ),
			'ocd' => true,
			'transient' => false,
		],
		'expected' => 1,
	],
];
