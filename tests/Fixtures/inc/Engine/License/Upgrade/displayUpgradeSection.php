<?php

return [
	'testShouldReturnNullWhenLicenseIsInfinite' => [
		'config'   => [
			'license_account'    => -1,
			'licence_expiration' => false,
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenLicenseIsExpired' => [
		'config'   => [
			'license_account'    => 1,
			'licence_expiration' => true,
		],
		'expected' => null,
	],
	'testShouldDisplaySectionWhenLicenseIsNotExpiredAndNotInfinite' => [
		'config'   => [
			'license_account'    => 1,
			'licence_expiration' => false,
		],
		'expected' => '',
	],
];
