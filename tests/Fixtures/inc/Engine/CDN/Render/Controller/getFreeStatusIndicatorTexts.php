<?php

return [
	'returnedUnchangedWhenNotFree'                         => [
		'config'   => [
			'pages_count'             => 0,
			'is_subscription_loading' => false,
			'free'                    => false,
		],
		'expected' => [
			'same_as_input' => true,
		],
	],

	'defaultDetailsKeptWhenActiveAndNoPagesAdded'          => [
		'config'   => [
			'applied_cdn_state'          => 'rocketcdn_free',
			'pages_count'                => 0,
			'is_subscription_loading'    => false,
			'free'                       => true,
			'is_license_invalid'         => false,
			'is_reseller_license_banned' => false,
		],
		'expected' => [
			'details'             => 'Start with your homepage.',
			'no_status_indicator' => false,
		],
	],

	'noStatusIndicatorSetAndDetailsClearedWhenPagesExist'  => [
		'config'   => [
			'applied_cdn_state'          => 'rocketcdn_free',
			'pages_count'                => 2,
			'is_subscription_loading'    => false,
			'free'                       => true,
			'is_license_invalid'         => false,
			'is_reseller_license_banned' => false,
		],
		'expected' => [
			'details'             => '',
			'no_status_indicator' => true,
		],
	],

	'pausedOnboardingCopySetWhenCdnIsPaused'               => [
		'config'   => [
			'applied_cdn_state'          => 'nothing',
			'has_active_subscription'    => true,
			'pages_count'                => 0,
			'is_subscription_loading'    => false,
			'free'                       => true,
			'is_license_invalid'         => false,
			'is_reseller_license_banned' => false,
		],
		'expected' => [
			'details' => '<strong>Start with your homepages and add up to 2 more key pages.</strong> Includes unlimited traffic across 10 edge locations.',
		],
	],

	'expiredClassAndRenewalPromptSetWhenLicenceInvalid'    => [
		'config'   => [
			'applied_cdn_state'          => 'rocketcdn_free',
			'pages_count'                => 0,
			'is_subscription_loading'    => false,
			'free'                       => true,
			'is_license_invalid'         => true,
			'is_reseller_license_banned' => false,
		],
		'expected' => [
			'class_contains' => 'wpr-cdn-status--expired',
			'details'        => 'Renew now to keep using RocketCDN Free.',
		],
	],
];
