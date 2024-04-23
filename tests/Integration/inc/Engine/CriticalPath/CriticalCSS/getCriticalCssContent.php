<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSS;

use WP_Rocket\Tests\Integration\ContentTrait;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\CriticalCSS::get_critical_css_content
 * @uses   ::rocket_get_constant
 * @uses   \WP_Rocket\Admin\Options_Data::get
 *
 * @group  CriticalPath
 * @group  vfs
 */
class Test_GetCriticalCssContent extends FilesystemTestCase {
	use ContentTrait, DBTrait;

	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSS/getCriticalCssContent.php';

	protected static $use_settings_trait = true;
	private static   $user_id;
	protected static $critical_css;

	private $async_css_mobile;
	private $cache_mobile;
	private $fallback_css;
	private $is_mobile;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::installFresh();

		$container          = apply_filters( 'rocket_container', null );
		self::$critical_css = $container->get( 'critical_css' );

		self::$user_id = static::factory()->user->create(
			[
				'role'          => 'adminstrator',
				'user_nicename' => 'rocket_tester',
			]
		);
	}

	public static function tear_down_after_class()
	{
		self::uninstallAll();
		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		add_filter( 'wp_is_mobile', [ $this, 'is_mobile' ] );
		add_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'cache_mobile' ] );
		add_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'async_css_mobile' ] );
		add_filter( 'pre_get_rocket_option_critical_css', [ $this, 'getFallbackCss' ] );

		wp_set_current_user( self::$user_id );
		set_current_screen( 'front' );
	}

	public function tear_down() {
		remove_filter( 'wp_is_mobile', [ $this, 'is_mobile' ] );
		remove_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'cache_mobile' ] );
		remove_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'async_css_mobile' ] );
		remove_filter( 'pre_get_rocket_option_critical_css', [ $this, 'getFallbackCss' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->is_mobile        = $config['wp_is_mobile'];
		$this->cache_mobile     = $config['settings']['do_caching_mobile_files'];
		$this->async_css_mobile = $config['settings']['async_css_mobile'];
		$this->fallback_css     = $config['settings']['critical_css'];

		$this->goToContentType( $config );

		$this->assertSame(
			$expected,
			self::$critical_css->get_critical_css_content()
		);
	}

	public function async_css_mobile() {
		return $this->async_css_mobile;
	}

	public function cache_mobile() {
		return $this->cache_mobile;
	}

	public function is_mobile() {
		return $this->is_mobile;
	}

	public function getFallbackCss() {
		return $this->fallback_css;
	}
}
