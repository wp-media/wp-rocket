<?php
/**
 * Fixture data for Test_CancelRefundFlows::testShouldReflectSubscriptionStateAfterCancelRefund.
 *
 * Each entry covers one Section-2 scenario from the QA spec.
 * Keys match the assertion conditions checked in the test method.
 */

$cdn_url = 'https://abcd1234.delivery.rocketcdn.me';

// Reusable API response helpers.
$status_404 = [
	'response' => [ 'code' => 404, 'message' => 'Not Found' ],
	'body'     => '',
];

$status_200_running_paid = [
	'response' => [ 'code' => 200, 'message' => 'OK' ],
	'body'     => json_encode(
		[
			'success'          => true,
			'website_id'       => 12345,
			'website_activated' => true,
			'cdn_url'          => $cdn_url,
			'status'           => 'running',
			'next_date_update' => '2026-12-01 00:00:00',
			'website_attached' => true,
			'plan_type'        => 'paid',
			'plan_page_limit'  => null,
			'subscription_id'  => 67890,
		]
	),
];

$website_search_200_pending_paid = [
	'response' => [ 'code' => 200, 'message' => 'OK' ],
	'body'     => json_encode(
		[
			'subscription_status'   => 'cancelled',
			'status'                => 'pending_deletion',
			'subscription_plan_type' => 'paid',
			'cdn_url'               => $cdn_url,
		]
	),
];

return [

	// -------------------------------------------------------------------------
	// TC-2.1 – Cancel paid immediately + delete website
	// -------------------------------------------------------------------------
	'TC-2.1-cancelPaidAndDeleteWebsite' => [
		'config'   => [
			// Subscription cancelled and website deleted → both endpoints return 404.
			'subscription_api_response' => $status_404,
			'website_search_response'   => $status_404,
			// A page added while the subscription was free before the paid upgrade.
			'free_pages'                => [
				[ 'url' => 'http://example.org/', 'title' => 'Home' ],
			],
		],
		'expected' => [
			'has_active_subscription'          => false,
			'is_in_grace_period'               => false,
			'is_cancelled_outside_grace_period' => true,
			'is_paid'                          => false,
			// Context falls back to the base 'rocketcdn' type (neither free nor paid).
			'context_driver'                   => 'rocketcdn',
			// No active subscription → FrontendSubscriber returns empty CNAMEs → no rewriting.
			'cname_applied'                    => false,
			// The pre-paid free page must still be in the DB.
			'free_pages_count_in_db'           => 1,
		],
	],

	// -------------------------------------------------------------------------
	// TC-2.2 – Cancel paid, website NOT deleted (grace period / Stripe flow)
	// -------------------------------------------------------------------------
	'TC-2.2-cancelPaidGracePeriodStripe' => [
		'config'   => [
			// subscription/status → 404; website/search → 200 pending_deletion (paid).
			'subscription_api_response' => $status_404,
			'website_search_response'   => $website_search_200_pending_paid,
			// Pre-set persistent flag so the grace-period handler skips cache clearing.
			'forced_pause_tracking'     => [ 'persistent' => true ],
		],
		'expected' => [
			'has_active_subscription'          => false,
			'is_in_grace_period'               => true,
			'is_cancelled_outside_grace_period' => false,
			'is_paid'                          => true,
			// Context resolves to paid because is_paid() is true (plan_type comes from website/search).
			'context_driver'                   => 'rocketcdn_paid',
			// CDN is force-paused during the grace period → no CNAME applied.
			'cname_applied'                    => false,
			// Element must be disabled while CDN is paused.
			'should_disable_element'           => true,
		],
	],

	// -------------------------------------------------------------------------
	// TC-2.3 – Refund (cancel from account + delete from CDN app)
	// -------------------------------------------------------------------------
	'TC-2.3-refundAndDeleteWebsite' => [
		'config'   => [
			// Identical API state to TC-2.1: website gone, outside grace period.
			'subscription_api_response' => $status_404,
			'website_search_response'   => $status_404,
			'free_pages'                => [
				[ 'url' => 'http://example.org/', 'title' => 'Home' ],
				[ 'url' => 'http://example.org/about/', 'title' => 'About' ],
			],
		],
		'expected' => [
			'has_active_subscription'          => false,
			'is_in_grace_period'               => false,
			'is_cancelled_outside_grace_period' => true,
			'context_driver'                   => 'rocketcdn',
			'cname_applied'                    => false,
			'free_pages_count_in_db'           => 2,
		],
	],

	// -------------------------------------------------------------------------
	// TC-2.4 – Re-purchase paid CDN after refund/cancel
	// -------------------------------------------------------------------------
	'TC-2.4-repurchasedPaidActive' => [
		'config'   => [
			// Active paid subscription restored.
			'subscription_api_response' => $status_200_running_paid,
		],
		'expected' => [
			'has_active_subscription'          => true,
			'is_in_grace_period'               => false,
			'is_cancelled_outside_grace_period' => false,
			'is_paid'                          => true,
			'context_driver'                   => 'rocketcdn_paid',
			// Active subscription → FrontendSubscriber injects the CDN URL → rewriting happens.
			'cname_applied'                    => true,
			// With an active subscription and CDN enabled, the element must NOT be disabled.
			'should_disable_element'           => false,
		],
	],

	// -------------------------------------------------------------------------
	// TC-2.6 – Upgrade from 3.22.0.1 with cancel but no website delete
	// -------------------------------------------------------------------------
	'TC-2.6-upgradeWithGracePeriod' => [
		'config'   => [
			// Same API state as TC-2.2: subscription gone, website still pending deletion.
			'subscription_api_response' => $status_404,
			'website_search_response'   => $website_search_200_pending_paid,
			'forced_pause_tracking'     => [ 'persistent' => true ],
		],
		'expected' => [
			'has_active_subscription'          => false,
			'is_in_grace_period'               => true,
			'is_cancelled_outside_grace_period' => false,
			'is_paid'                          => true,
			'context_driver'                   => 'rocketcdn_paid',
			'cname_applied'                    => false,
			'should_disable_element'           => true,
		],
	],

];
