<?php

return [
	'testShouldReturnFalseWhenNotObject' => [
		'data'     => [],
		'expected' => false,
	],
	'testShouldReturnFalseWhenPropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID' => 1,
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenNotAutoRenew' => [
		'data'     => json_decode( json_encode( [
			'ID'             => 1,
			'has_auto_renew' => false,
		] ) ),
		'expected' => false,
	],
	'testShouldReturnTrueWhenAutoRenew' => [
		'data'     => json_decode( json_encode( [
			'ID'             => 1,
			'has_auto_renew' => true,
		] ) ),
		'expected' => true,
	],
];
