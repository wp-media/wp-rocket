<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Divi;

use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\WPThemeTestcase;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Divi::handle_save_template
 *
 * @group  ThirdParty
 * @group  Divi
 */
class Test_handleSaveTemplate extends WPThemeTestcase {
	use CapTrait;

	protected $path_to_test_data = '/inc/ThirdParty/Themes/Divi/handleSaveTemplate.php';

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
	}

	public function tear_down() : void {
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
