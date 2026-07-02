<?php

return [
	'testShouldReturnEmptyStringForPaidSubscriber'    => [
		'config'   => [
			'is_paid' => true,
			'badge'   => 'NEW',
		],
		'expected' => '',
	],
	'testShouldReturnBadgeForNonPaidUser'             => [
		'config'   => [
			'is_paid' => false,
			'badge'   => 'NEW',
		],
		'expected' => 'NEW',
	],
	'testShouldReturnBadgeForUserWithNoSubscription'  => [
		'config'   => [
			'is_paid' => false,
			'badge'   => 'NEW',
		],
		'expected' => 'NEW',
	],
];
