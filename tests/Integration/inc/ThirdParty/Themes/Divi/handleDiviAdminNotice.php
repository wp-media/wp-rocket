<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Divi;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\WPThemeTestcase;
use WP_Rocket\ThirdParty\Themes\Divi;

/**
 * Test class covering \WP_Rocket\ThirdParty\Themes\Divi::handle_save_template
 *
 * @group Themes
 * @group AdminOnly
 */
class Test_HandleDiviAdminNotice extends WPThemeTestcase {
	use CapTrait;

	private $container;
	private $event;
	private $subscriber;

	protected $path_to_test_data = '/inc/ThirdParty/Themes/Divi/handleDiviAdminNotice.php';

	private static $user_without_permission;
	private static $user_with_permission;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::hasAdminCapBeforeClass();

		self::setAdminCap();
		self::$user_with_permission    = static::factory()->user->create( [ 'role' => 'administrator' ] );
		self::$user_without_permission = static::factory()->user->create( [ 'role' => 'editor' ] );
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		self::resetAdminCap();
	}

	public function set_up() {
		parent::set_up();

		$this->go_to( admin_url( 'options-general.php' ) );

		add_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		$this->container = apply_filters( 'rocket_container', '' );
		$this->event = $this->container->get( 'event_manager' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'admin_notices' );

		$this->event->remove_subscriber( $this->subscriber );

		remove_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );

		parent::tear_down();
	}

	public function set_stylesheet() {
		return 'Divi';
	}

	/**
	 * @dataProvider ProviderTestData
	 */
	public function testAdminNotice( $config, $expected ) {
		$options     = $this->container->get( 'options' );
		$options_api = $this->container->get( 'options_api' );
		$delayjs_html = $this->container->get( 'delay_js_html' );
		$used_css = $this->container->get( 'rucss_used_css_controller' );
		$options_api->set( 'settings', [] );
		$this->subscriber = new Divi( $options_api, $options, $delayjs_html, $used_css );

		$this->event->add_subscriber( $this->subscriber );

		$this->unregisterAllCallbacksExcept( 'admin_notices', 'handle_divi_admin_notice' );

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
				->twice()
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
