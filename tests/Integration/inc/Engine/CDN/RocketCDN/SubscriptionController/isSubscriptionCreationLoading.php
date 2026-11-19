<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

/**
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_IsSubscriptionCreationLoading extends AbstractSubscriptionControllerTestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, bool $expected ): void {
		if ( ! empty( $config['has_token'] ) ) {
			$this->set_rocketcdn_user_token();
		}

		if ( ! empty( $config['has_transient'] ) ) {
			set_transient( 'rocket_cdn_subscription_creation_in_progress', time(), HOUR_IN_SECONDS );
		}

		$this->assertSame( $expected, $this->subscription_controller->is_subscription_creation_loading() );
	}
}
