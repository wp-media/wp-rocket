<?php

return [
	'testShouldReturnNullWhenLicenseIsInfinite' => [
		'data'   => json_decode( json_encode( [
			'licence_account'    => -1,
			'licence_expiration' => strtotime( 'next year' ),
		] ) ),
		'expected' => '',
	],
	'testShouldReturnNullWhenLicenseIsExpired' => [
		'data'   => json_decode( json_encode( [
			'licence_account'    => 1,
			'licence_expiration' => strtotime( 'last month' ),
		] ) ),
		'expected' => '',
	],
	'testShouldDisplaySectionWhenLicenseIsNotExpiredAndNotInfinite' => [
		'data'   => json_decode( json_encode( [
			'licence_account'    => 1,
			'licence_expiration' => strtotime( 'next year' ),
		] ) ),
		'expected' => '<p>
		You can use WP Rocket on more websites by upgrading your license (you will only pay the price difference between your current and new licenses).
		<button id="wpr-popin-upgrade-toggle">Upgrade Now</button>
		</p>',
	],
];
