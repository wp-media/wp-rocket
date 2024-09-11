<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Divi;

use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\WPThemeTestcase;
use WP_Rocket\ThirdParty\Themes\Divi;

/**
 * Test class covering \WP_Rocket\ThirdParty\Themes\Divi::handle_save_template
 *
 * @group Themes
 */
class Test_handleSaveTemplate extends WPThemeTestcase {
	use CapTrait;

	private $container;
	private $event;
	private $subscriber;

	protected $path_to_test_data = '/inc/ThirdParty/Themes/Divi/handleSaveTemplate.php';

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
		self::resetAdminCap();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		$this->container = apply_filters( 'rocket_container', '' );
		$this->event = $this->container->get( 'event_manager' );

		add_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
	}

	public function tear_down() {
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
	public function testTransientSet( $config, $expected ) {
		$options     = $this->container->get( 'options' );
		$options_api = $this->container->get( 'options_api' );
		$delayjs_html = $this->container->get( 'delay_js_html' );
		$used_css = $this->container->get( 'rucss_used_css_controller' );
		$options_api->set( 'settings', [] );
		$this->subscriber = new Divi( $options_api, $options, $delayjs_html, $used_css );

		$this->event->add_subscriber( $this->subscriber );

		$this->set_theme( 'divi', 'Divi' );

		$post = $this->factory->post->create_and_get( $config['template_post'] );

		if ( isset( $config['filter_return'] ) ) {
			add_filter( 'rocket_divi_bypass_save_template', $config['filter_return'] ? '__return_true' : '__return_false' );
		}

		if ( isset( $config['transient_return'] ) ) {
			if ( $config['transient_return'] ) {
				set_transient( 'rocket_divi_notice', true );
			} else {
				delete_transient( 'rocket_divi_notice' );
			}
		}

		if ( isset( $config['layout_post'] ) ) {
			$layout_post = $this->factory->post->create_and_get( $config['layout_post'] );
			update_post_meta( $layout_post->ID, '_et_header_layout_id', $post->ID );
		}

		do_action( 'et_save_post', $post->ID );

		$this->assertEquals( $expected['transient_set'], get_transient( 'rocket_divi_notice' ) );
	}
}
