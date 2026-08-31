<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\FrontendSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController\AbstractSubscriptionControllerTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\FrontendSubscriber::scheduled_auto_detect_pro_subscription
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_ScheduledAutoDetectProSubscription extends AbstractSubscriptionControllerTestCase {

	public function set_up() {
		parent::set_up();

		$this->set_rocketcdn_user_token();
	}

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

		$queue = $this->getRocketContainer()->get( 'rocketcdn_queue' );

		if ( ! empty( $config['pre_set_failed_transient'] ) ) {
			// Mimics a previous run having already given up, to verify a conclusive result clears it.
			set_transient( 'rocket_cdn_pro_detection_failed', true, WEEK_IN_SECONDS );
		}

		if ( isset( $config['pre_scheduled_attempt'] ) ) {
			// Mimics a still-pending job (e.g. from the automatic backoff chain), to verify a
			// conclusive result cancels it instead of leaving it to run alongside a new one.
			$queue->schedule_pro_detection_job( $config['pre_scheduled_attempt'] );
		}

		do_action( 'rocket_cdn_auto_detect', $config['attempt'] );

		$this->assertSame(
			$expected['failed_transient'],
			false !== get_transient( 'rocket_cdn_pro_detection_failed' )
		);

		if ( null === $expected['scheduled_next_attempt'] ) {
			$this->assertFalse( $queue->is_scheduled( 'rocket_cdn_auto_detect', null ) );
		} else {
			$this->assertTrue( $queue->is_scheduled( 'rocket_cdn_auto_detect', [ 'attempt' => $expected['scheduled_next_attempt'] ] ) );
		}
	}
}
