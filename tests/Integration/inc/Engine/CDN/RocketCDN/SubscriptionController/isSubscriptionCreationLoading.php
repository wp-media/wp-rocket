<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

/**
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_IsSubscriptionCreationLoading extends AbstractSubscriptionControllerTestCase {

	public function testShouldReturnFalseWhenNoTokenEvenIfTransientSet(): void {
		set_transient( 'rocket_cdn_subscription_creation_in_progress', time(), HOUR_IN_SECONDS );

		$this->assertFalse( $this->subscription_controller->is_subscription_creation_loading() );
	}

	public function testShouldReturnFalseWhenTokenExistsAndNoTransient(): void {
		$this->set_rocketcdn_user_token();

		$this->assertFalse( $this->subscription_controller->is_subscription_creation_loading() );
	}

	public function testShouldReturnTrueWhenTokenExistsAndTransientSet(): void {
		$this->set_rocketcdn_user_token();
		set_transient( 'rocket_cdn_subscription_creation_in_progress', time(), HOUR_IN_SECONDS );

		$this->assertTrue( $this->subscription_controller->is_subscription_creation_loading() );
	}
}
