<?php

return [
	'testShouldReturnFalseWhenDisplayIsFalse'        => [
		'config'   => [
			'display' => false,
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenSubscriptionLoading'   => [
		'config'   => [
			'display'    => true,
			'is_loading' => true,
		],
		'expected' => false,
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
