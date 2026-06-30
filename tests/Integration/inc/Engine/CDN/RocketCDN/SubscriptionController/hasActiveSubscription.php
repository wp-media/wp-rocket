<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

/**
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_HasActiveSubscription extends AbstractSubscriptionControllerTestCase {

	public function set_up() {
		parent::set_up();
		$this->update_rocketcdn_settings( [ 'cdn' => 1, 'cdn_type' => 'rocketcdn' ] );
		$this->set_rocketcdn_user_token();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, bool $expected ): void {
		$this->mock_api( $config );
		$this->assertSame( $expected, $this->subscription_controller->has_active_subscription() );
	}
}
