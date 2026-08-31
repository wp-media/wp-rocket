<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\FrontendSubscriber;

use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController\AbstractSubscriptionControllerTestCase;
use WPDieException;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\FrontendSubscriber::handle_manual_retry_pro_detection
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_HandleManualRetryProDetection extends AbstractSubscriptionControllerTestCase {
	use CapTrait;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::hasAdminCapBeforeClass();
		self::setAdminCap();
	}

	public static function tear_down_after_class() {
		self::resetAdminCap();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		unset( $_GET['_wpnonce'] );
	}

	public function tear_down() {
		unset( $_GET['_wpnonce'] );

		remove_filter( 'wp_redirect', [ $this, 'return_empty_string' ] );

		delete_transient( 'rocket_cdn_pro_detection_failed' );
		$this->getRocketContainer()->get( 'rocketcdn_queue' )->cancel_pro_detection_job();

		parent::tear_down();
	}

	public function return_empty_string() {
		return '';
	}

	/**
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected results.
	 * @return void
	 */
	public function testShouldDoAsExpected( array $config, array $expected ) {
		$user_id = $this->factory->user->create( [ 'role' => $config['user_role'] ] );
		wp_set_current_user( $user_id );

		switch ( $config['nonce'] ) {
			case 'valid':
				$_GET['_wpnonce'] = wp_create_nonce( 'rocket_retry_pro_detection' );
				break;
			case 'invalid':
				$_GET['_wpnonce'] = 'invalid';
				break;
			default:
				unset( $_GET['_wpnonce'] );
		}

		if ( ! empty( $config['pro_detection_failed_transient'] ) ) {
			set_transient( 'rocket_cdn_pro_detection_failed', true );
		}

		if ( ! empty( $config['token'] ) ) {
			$this->set_rocketcdn_user_token();
		}

		$this->mock_api( $config );

		add_filter( 'wp_redirect', [ $this, 'return_empty_string' ] );

		// handle_manual_retry_pro_detection() always ends in wp_nonce_ays(), wp_die(), or a
		// redirect + wp_die() under WP_ROCKET_IS_TESTING, so every scenario throws.
		try {
			do_action( 'admin_post_rocket_retry_pro_detection' );
			$this->fail( 'Expected a WPDieException to be thrown.' );
		} catch ( WPDieException $e ) {
			if ( isset( $expected['exception_message'] ) ) {
				$this->assertStringContainsString( $expected['exception_message'], $e->getMessage() );
			}
		}

		if ( isset( $expected['can_manage_options'] ) ) {
			$this->assertSame( $expected['can_manage_options'], current_user_can( 'rocket_manage_options' ) );
		}

		if ( isset( $expected['failed_transient_cleared'] ) && $expected['failed_transient_cleared'] ) {
			$this->assertFalse( get_transient( 'rocket_cdn_pro_detection_failed' ) );
		}

		if ( isset( $expected['job_scheduled'] ) ) {
			$queue = $this->getRocketContainer()->get( 'rocketcdn_queue' );
			$this->assertSame( $expected['job_scheduled'], $queue->is_scheduled( 'rocket_cdn_auto_detect', null ) );
		}
	}
}
