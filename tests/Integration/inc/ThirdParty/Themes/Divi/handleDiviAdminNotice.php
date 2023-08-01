<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Divi;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\WPThemeTestcase;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Divi::handle_save_template
 *
 * @group  ThirdParty
 * @group  Divi
 * @group  AdminOnly
 */
class Test_HandleDiviAdminNotice extends WPThemeTestcase {
	use CapTrait;

	protected $path_to_test_data = '/inc/ThirdParty/Themes/Divi/handleDiviAdminNotice.php';

	private static $container;

	private static $user_without_permission;
	private static $user_with_permission;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::hasAdminCapBeforeClass();

		self::setAdminCap();
		self::$user_with_permission    = static::factory()->user->create( [ 'role' => 'administrator' ] );
		self::$user_without_permission = static::factory()->user->create( [ 'role' => 'editor' ] );

		self::$container = apply_filters( 'rocket_container', '' );
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		self::resetAdminCap();

		self::$container->get( 'event_manager' )->remove_subscriber( self::$container->get( 'divi' ) );

	}

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );

		self::$container->get( 'event_manager' )->add_subscriber( self::$container->get( 'divi' ) );
		$this->unregisterAllCallbacksExcept( 'admin_notices', 'handle_divi_admin_notice' );
	}

	public function tear_down() : void {
		remove_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		$this->restoreWpFilter( 'admin_notices' );

		parent::tear_down();
	}

	public function set_stylesheet() {
		return 'Divi';
	}

	/**
	 * @dataProvider ProviderTestData
	 */
	public function testAdminNotice( $config, $expected ) {
		$this->set_theme( 'divi', 'Divi' );


		if ( isset( $config['rucss_option'] ) ) {
			add_filter( 'pre_get_rocket_option_remove_unused_css', $config['rucss_option'] ? '__return_true' : '__return_false' );
		}

		if ( isset( $config['capability'] ) ) {
			if ( $config['capability'] ) {
				wp_set_current_user( self::$user_with_permission );
			} else {
				wp_set_current_user( self::$user_without_permission );
			}
		}

		if ( isset( $config['transient_return'] ) ) {
			if ( $config['transient_return'] ) {
				set_transient( 'rocket_divi_notice', true );
			} else {
				delete_transient( 'rocket_divi_notice' );
			}
		}

		if ( $expected['notice_show'] && $expected['notice_html'] ) {
			Functions\expect( 'wp_create_nonce' )
				->once()
				->andReturn( '12345' );
			$this->assertStringContainsStringIgnoringCase(
				$this->format_the_html( $expected['notice_html'] ),
				$this->getActualHtml()
			);
		} else {
			$this->assertEmpty( $this->getActualHtml() );
		}
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'admin_notices' );

		return $this->format_the_html( ob_get_clean() );
	}

}
