<?php

return [
	'testShouldReturnFalseWhenPropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID' => 1,
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenNotTrialCustomer' => [
		'data'     => json_decode( json_encode( [
			'ID'                 => 1,
			'is_trial_customer' => false,
		] ) ),
		'expected' => false,
	],
	'testShouldReturnTrueWhenTrialCustomer' => [
		'data'     => json_decode( json_encode( [
			'ID'                 => 1,
			'is_trial_customer' => true,
		] ) ),
		'expected' => true,
	],
	'testShouldReturnTrueWhenTrialCustomerIsTruthyNonBool' => [
		'data'     => json_decode( json_encode( [
			'ID'                 => 1,
			'is_trial_customer' => 1,
		] ) ),
		'expected' => true,
	],
];
