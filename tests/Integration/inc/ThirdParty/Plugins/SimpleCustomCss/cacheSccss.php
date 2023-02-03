<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\SimpleCustomCss;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\SimpleCustomCss::cache_sccss
 * @group  ThirdParty
 * @group  WithSCCSS
 */
class Test_CacheSccss extends FilesystemTestCase {
	use DBTrait;

	protected $path_to_test_data = '/inc/ThirdParty/Plugins/SimpleCustomCss/cacheSccss.php';
	private $wp_styles;

	public static function set_up_before_class() {
		self::installFresh();
		parent::set_up_before_class();
	}

	public static function tear_down_after_class()
	{
		self::uninstallAll();
		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		$this->wp_styles = wp_styles();
	}

	public function testShouldEnqueueStyleAndDeregister() {
		$this->assertArrayNotHasKey( 'scss', $this->wp_styles->registered );

		do_action( 'wp_enqueue_scripts' );

		$this->wp_styles = wp_styles();

		$this->assertArrayHasKey( 'scss', $this->wp_styles->registered );
		$this->assertFalse( has_action( 'wp_enqueue_scripts', 'sccss_register_style' ) );
	}
}
