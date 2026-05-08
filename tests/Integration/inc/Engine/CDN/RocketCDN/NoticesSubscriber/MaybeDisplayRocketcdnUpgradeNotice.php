<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::maybe_display_rocketcdn_upgrade_notice
 *
 * @group RocketCDN
 * @group AdminOnly
 */
class Test_MaybeDisplayRocketcdnUpgradeNotice extends TestCase {
	private static $admin_user_id = 0;
	private static $editor_user_id = 0;
	private $show_upgrade_notice = null;

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

		$this->unregisterAllCallbacksExcept( 'admin_notices', 'maybe_display_rocketcdn_upgrade_notice' );
	}

	public function tear_down() {
		delete_user_meta( self::$admin_user_id, 'rocket_boxes' );
		delete_user_meta( self::$editor_user_id, 'rocket_boxes' );
		remove_filter( 'pre_get_rocket_option_rocket_show_rocketcdn_upgrade_notice', [ $this, 'set_show_upgrade_notice' ] );
		set_current_screen( 'front' );

		$this->restoreWpHook( 'admin_notices' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ) {
		$user_id = 'administrator' === $config['role'] ? self::$admin_user_id : self::$editor_user_id;

		if ( array_key_exists( 'show_upgrade_notice', $config ) ) {
			$this->show_upgrade_notice = $config['show_upgrade_notice'];
			add_filter( 'pre_get_rocket_option_rocket_show_rocketcdn_upgrade_notice', [ $this, 'set_show_upgrade_notice' ], 10, 2 );
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

	public function set_show_upgrade_notice( $value, $default ) {
		return null === $this->show_upgrade_notice ? $default : $this->show_upgrade_notice;
	}
}
