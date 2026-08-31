<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\FrontendSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController\AbstractSubscriptionControllerTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\FrontendSubscriber::auto_detect_pro_subscription
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_AutoDetectProSubscription extends AbstractSubscriptionControllerTestCase {

	public function tear_down() {
		delete_transient( 'rocket_cdn_pro_detection_failed' );

		$this->getRocketContainer()->get( 'rocketcdn_queue' )->cancel_pro_detection_job();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ): void {
		$this->mock_api( $config );

		if ( ! empty( $config['token'] ) ) {
			$this->set_rocketcdn_user_token();
		}

		$subscriber = $this->getRocketContainer()->get( 'rocketcdn_frontend_subscriber' );
		$queue      = $this->getRocketContainer()->get( 'rocketcdn_queue' );

		$subscriber->auto_detect_pro_subscription();

		if ( null === $expected['scheduled_attempt'] ) {
			$this->assertFalse( $queue->is_scheduled( 'rocket_cdn_auto_detect', null ) );
		} else {
			$this->assertTrue( $queue->is_scheduled( 'rocket_cdn_auto_detect', [ 'attempt' => $expected['scheduled_attempt'] ] ) );
		}
	}
}
