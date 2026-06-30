<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Plugin\Admin\NoticeSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Plugin\Admin\NoticeSubscriber::maybe_display_post_activation_notice
 *
 * @group Plugin
 * @group AdminOnly
 */
class Test_MaybeDisplayPostActivationNotice extends TestCase {

	private static $admin_user_id  = 0;
	private static $editor_user_id = 0;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::setAdminCap();

		self::$admin_user_id  = static::factory()->user->create( [ 'role' => 'administrator' ] );
		self::$editor_user_id = static::factory()->user->create( [ 'role' => 'editor' ] );
	}

	public function set_up() {
		parent::set_up();

		if ( ! function_exists( 'rocket_notice_html' ) ) {
			require_once WP_ROCKET_ADMIN_UI_PATH . 'notices.php';
		}

		$this->unregisterAllCallbacksExcept( 'admin_notices', 'maybe_display_post_activation_notice', 10 );
	}

	public function tear_down() {
		delete_option( 'rocketcdn_user_token' );
		delete_user_meta( self::$admin_user_id, 'rocket_boxes' );
		delete_user_meta( self::$editor_user_id, 'rocket_boxes' );
		set_current_screen( 'front' );
		$this->restoreWpHook( 'admin_notices' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ) {
		$user_id = 'administrator' === $config['role'] ? self::$admin_user_id : self::$editor_user_id;

		if ( array_key_exists( 'previous_version', $config ) ) {
			$settings                     = get_option( WP_ROCKET_SLUG, [] );
			$settings['previous_version'] = $config['previous_version'];
			update_option( WP_ROCKET_SLUG, $settings );
		}

		if ( ! empty( $config['rocketcdn_user_token'] ) ) {
			update_option( 'rocketcdn_user_token', $config['rocketcdn_user_token'] );
		}

		wp_set_current_user( $user_id );
		set_current_screen( $config['screen'] );

		if ( isset( $config['boxes'] ) ) {
			update_user_meta( $user_id, 'rocket_boxes', $config['boxes'] );
		} else {
			delete_user_meta( $user_id, 'rocket_boxes' );
		}

		$actual = $this->get_actual_html();

		if ( $expected['display'] ) {
			$this->assertStringContainsStringIgnoringCase(
				$this->format_the_html( $expected['html'] ),
				$actual
			);

			return;
		}

		$this->assertStringNotContainsStringIgnoringCase(
			$this->format_the_html( $expected['html'] ),
			$actual
		);
	}

	private function get_actual_html(): string {
		ob_start();
		do_action( 'admin_notices' );

		return $this->format_the_html( ob_get_clean() );
	}
}
