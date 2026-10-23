<?php

return [
	'testShouldReturnFalseWhenDisplayIsFalse'        => [
		'config'   => [
			'display' => false,
		],
		'expected' => false,
	],
	'testShouldReturnTrueWhenSubscriptionLoading'    => [
		'config'   => [
			'display'     => true,
			'is_loading'  => true,
			'is_reseller' => false,
		],
		'expected' => true,
	],
	'testShouldReturnFalseForResellerAccount'        => [
		'config'   => [
			'display'     => true,
			'is_loading'  => false,
			'is_reseller' => true,
		],
		'expected' => false,
	],
	'testShouldReturnTrueForNonResellerAccount'      => [
		'config'   => [
			'display'     => true,
			'is_loading'  => false,
			'is_reseller' => false,
		],
		'expected' => true,
	],
];
