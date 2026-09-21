<?php

return [
	'returnedUnchangedWhenNotFree'                        => [
		'config'   => [
			'pages_count' => 0,
			'is_loading'  => false,
			'free'        => false,
		],
		'expected' => [
			'class_contains' => '',
			'same_as_input'  => true,
		],
	],

	'defaultDetailsKeptWhenActiveAndNoPagesAdded'         => [
		'config'   => [
			'applied_cdn_state'          => 'rocketcdn',
			'pages_count'                => 0,
			'is_loading'                 => false,
			'free'                       => true,
			'is_license_invalid'         => false,
			'is_reseller_license_banned' => false,
			'subscription_status'        => 'running',
			'plan_type'                  => 'free',
			'license_expired'            => false,
			'license_revoked'            => false,
		],
		'expected' => [
			'class_contains'  => '',
			'details'         => 'Start with your homepage.',
			'no_status_indicator' => false,
		],
	],

	'noStatusIndicatorSetAndDetailsClearedWhenPagesExist' => [
		'config'   => [
			'applied_cdn_state'          => 'rocketcdn',
			'pages_count'                => 2,
			'is_loading'                 => false,
			'free'                       => true,
			'is_license_invalid'         => false,
			'is_reseller_license_banned' => false,
			'subscription_status'        => 'running',
			'plan_type'                  => 'free',
			'license_expired'            => false,
			'license_revoked'            => false,
		],
		'expected' => [
			'class_contains'      => '',
			'details'             => '',
			'no_status_indicator' => true,
		],
	],

	'pausedOnboardingCopySetWhenCdnIsPaused'              => [
		'config'   => [
			'applied_cdn_state'          => 'nothing',
			'has_active_subscription'    => true,
			'pages_count'                => 0,
			'is_loading'                 => false,
			'free'                       => true,
			'is_license_invalid'         => false,
			'is_reseller_license_banned' => false,
			'subscription_status'        => 'running',
			'plan_type'                  => 'free',
			'license_expired'            => false,
			'license_revoked'            => false,
		],
		'expected' => [
			'class_contains' => '',
			'details'        => '<strong>Start with your homepages and add up to 2 more key pages.</strong> Includes unlimited traffic across 10 edge locations.',
		],
	],

	'expiredClassAndRenewalPromptSetWhenLicenceInvalid'   => [
		'config'   => [
			'applied_cdn_state'          => 'rocketcdn',
			'pages_count'                => 0,
			'is_loading'                 => false,
			'free'                       => true,
			'is_license_invalid'         => true,
			'is_reseller_license_banned' => false,
			'subscription_status'        => 'running',
			'plan_type'                  => 'free',
			'license_expired'            => true,
			'license_revoked'            => false,
		],
		'expected' => [
			'class_contains' => 'wpr-cdn-status--expired',
			'details'        => 'Renew now to keep using RocketCDN Free.',
		],
	],

	// Banned-reseller cases exercise the is_reseller_license_banned() branch
	// (Controller::get_free_status_indicator_texts() lines 1007-1009) which
	// clears `details` as the final step, overriding any earlier copy.

	'bannedResellerClearsPausedDetailsWhenCdnIsPaused'   => [
		'config'   => [
			'applied_cdn_state'          => 'nothing',
			'has_active_subscription'    => true,
			'pages_count'                => 0,
			'is_loading'                 => false,
			'free'                       => true,
			'is_license_invalid'         => false,
			'is_reseller_license_banned' => true,
			'subscription_status'        => 'running',
			'plan_type'                  => 'free',
			'license_expired'            => false,
			'license_revoked'            => true,
			'is_reseller'                => true,
			'ban_reason'                 => 'BANNED_WEBSITE',
		],
		'expected' => [
			'class_contains'             => '',
			'details'                    => '',
			'paused_details_not_contains' => 'Start with',
		],
	],

	'bannedResellerClearsRenewalPromptEvenWhenLicenceInvalid' => [
		'config'   => [
			'applied_cdn_state'          => 'rocketcdn',
			'pages_count'                => 0,
			'is_loading'                 => false,
			'free'                       => true,
			'is_license_invalid'         => true,
			'is_reseller_license_banned' => true,
			'subscription_status'        => 'running',
			'plan_type'                  => 'free',
			'license_expired'            => true,
			'license_revoked'            => true,
			'is_reseller'                => true,
			'ban_reason'                 => 'BANNED_WEBSITE',
		],
		'expected' => [
			'class_contains'             => 'wpr-cdn-status--expired',
			'details'                    => '',
			'paused_details_not_contains' => 'Renew',
		],
	],

	'renewalPromptShownEvenWhenPagesExist'               => [
		'config'   => [
			'applied_cdn_state'          => 'rocketcdn',
			'pages_count'                => 2,
			'is_loading'                 => false,
			'free'                       => true,
			'is_license_invalid'         => true,
			'is_reseller_license_banned' => false,
			'subscription_status'        => 'running',
			'plan_type'                  => 'free',
			'license_expired'            => true,
			'license_revoked'            => false,
		],
		'expected' => [
			'class_contains'      => 'wpr-cdn-status--expired',
			'details'             => 'Renew now to keep using RocketCDN Free.',
			'no_status_indicator' => false,
		],
	],
];
