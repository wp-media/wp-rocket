<?php

// Used by the integration test only: the customer data.
$user = json_decode( json_encode( [
	'licence_account'    => -1,
	'licence_expiration' => strtotime( 'next year' ),
	'date_created'       => strtotime( 'last year' ),
] ) );

$expired_user = json_decode( json_encode( [
	'licence_account'    => -1,
	'licence_expiration' => strtotime( 'last year' ),
	'date_created'       => strtotime( 'last year' ),
] ) );

$base = [
	'user'            => $user,
	'user_id'         => 42,
	'boxes'           => '',
	'in_boxes'        => false,
	'async_css'       => true,
	'expired_license' => false,
	'rucss_status'    => [ 'disable' => false, 'text' => '' ],
];

return [
	'shouldDisplayBanner' => [
		'config'   => $base,
		'expected' => true,
	],
	'shouldDisplayBannerWhenOtherBoxesAreDismissed' => [
		'config'   => array_merge( $base, [ 'boxes' => [ 'rocket_activation_notice' ] ] ),
		'expected' => true,
	],
	'dismissedShouldNotDisplayBanner' => [
		'config'   => array_merge( $base, [ 'boxes' => [ 'switch_to_rucss_notice' ], 'in_boxes' => true ] ),
		'expected' => false,
	],
	'asyncCssDisabledShouldNotDisplayBanner' => [
		'config'   => array_merge( $base, [ 'async_css' => false ] ),
		'expected' => false,
	],
	'expiredLicenseShouldNotDisplayBanner' => [
		'config'   => array_merge( $base, [ 'expired_license' => true, 'user' => $expired_user ] ),
		'expected' => false,
	],
	'rucssDisabledShouldNotDisplayBanner' => [
		'config'   => array_merge( $base, [ 'rucss_status' => [ 'disable' => true, 'text' => 'disabled' ] ] ),
		'expected' => false,
	],
];
