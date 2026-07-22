<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::maybe_add_rocketcdn_to_recommendations_api_params
 *
 * Note: This test covers early return scenarios (when customer data is missing, subscription is not running,
 * or white label is disabled). Full integration testing with active RocketCDN subscription requires
 * the actual RocketCDN plugin with valid subscription data.
 *
 * @uses   \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
 * @uses   ::rocket_get_constant
 *
 * @group  AdminOnly
 * @group  RocketCDN
 * @group  RocketCDNAdminPage
 * @group  Recommendations
 */
class Test_MaybeAddRocketcdnToRecommendationsApiParams extends TestCase {
	public function set_up() {
		parent::set_up();

		 $this->unregisterAllCallbacksExcept( 'rocket_insights_api_recommendations_params', 'maybe_add_rocketcdn_to_recommendations_api_params', 10 );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_insights_api_recommendations_params' );

		delete_transient( 'rocketcdn_customer_data' );
		delete_transient( 'rocketcdn_status' );
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->white_label = $config['white_label'];

		if ( $config['has_customer_data'] ) {
			set_transient( 'rocketcdn_customer_data', [ 'customer_id' => '123' ], HOUR_IN_SECONDS );
		}

		if ( isset( $config['subscription_status'] ) ) {
			$status = [ 'subscription_status' => $config['subscription_status'] ];

			if ( isset( $config['plan_type'] ) ) {
				$status['plan_type'] = $config['plan_type'];
			}

			set_transient( 'rocketcdn_status', $status, HOUR_IN_SECONDS );
		}

		$result = wpm_apply_filters_typed( 'array', 'rocket_insights_api_recommendations_params', $config['params'] );

		$this->assertSame( $expected['params'], $result );
	}
}
