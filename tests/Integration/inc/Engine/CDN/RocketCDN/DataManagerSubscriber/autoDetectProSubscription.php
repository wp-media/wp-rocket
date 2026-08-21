<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController\AbstractSubscriptionControllerTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::auto_detect_pro_subscription
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_AutoDetectProSubscription extends AbstractSubscriptionControllerTestCase {

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

		if ( isset( $config['pre_set_cdn_state'] ) ) {
			// Mimics the state already written by wp_rocket_first_install before this job ever runs.
			$this->update_rocketcdn_settings( [ 'cdn_state' => $config['pre_set_cdn_state'] ] );
		}

		$queue = $this->getRocketContainer()->get( 'rocketcdn_queue' );

		do_action( 'rocket_cdn_auto_detect', $config['attempt'] );

		$settings = $this->options_api->get( 'settings', [] );
		$this->assertSame( $expected['cdn_state'], $settings['cdn_state'] ?? null );

		$this->assertSame(
			$expected['failed_transient'],
			false !== get_transient( 'rocket_cdn_pro_detection_failed' )
		);

		if ( null === $expected['scheduled_next_attempt'] ) {
			$this->assertFalse( $queue->is_scheduled( 'rocket_cdn_auto_detect' ) );
		} else {
			$this->assertTrue( $queue->is_scheduled( 'rocket_cdn_auto_detect', [ 'attempt' => $expected['scheduled_next_attempt'] ] ) );
		}
	}
}
