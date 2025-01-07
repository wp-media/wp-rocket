<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WP_Rocket\Tests\Integration\ContentTrait;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::delete_cpcss
 * @uses   \WP_Rocket\Admin\Options_Data::get
 *
 * @group  CriticalPath
 * @group  vfs
 */
class Test_DeleteCpcss extends FilesystemTestCase {
	use ContentTrait;

	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSSSubscriber/deleteCpcss.php';

	private $async_css;
	private $async_css_mobile;
	private static $post_id;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$post_id = $factory->post->create();

		// Re-enable ATF optimization.
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );
	}

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'async_css' ] );
		add_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'async_css_mobile' ] );
	}

	public function tear_down() {
		// Re-enable ATF optimization.
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'async_css' ] );
		remove_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'async_css_mobile' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		if ( $config['current_user_can'] ) {
			$this->setUpUser();
		}

		$post_type              = isset( $config['post']['type'] ) ? $config['post']['type'] : 'post';
		$this->async_css        = isset( $config['async_css'] ) ? $config['async_css'] : 0;
		$this->async_css_mobile = isset( $config['async_css_mobile'] ) ? $config['async_css_mobile'] : 0;

		$item_path              = 'wp-content/cache/critical-css/1/posts' . DIRECTORY_SEPARATOR . "{$post_type}-" . self::$post_id . ".css";
		$this->filesystem->put_contents( $item_path, '.cpcss { color: red; }');
		$this->assertTrue( $this->filesystem->exists( $item_path ) );

		$mobile_item_path = '';

		if ( $this->async_css_mobile ) {
			$mobile_item_path = 'wp-content/cache/critical-css/1/posts' . DIRECTORY_SEPARATOR . "{$post_type}-" . self::$post_id . "-mobile.css";
			$this->filesystem->put_contents( $mobile_item_path, '.cpcss-mobile { color: red; }');
			$this->assertTrue( $this->filesystem->exists( $mobile_item_path ) );
		}

		do_action( 'before_delete_post', self::$post_id, self::factory()->post->get_object_by_id( self::$post_id ) );

		if ( ! $config['current_user_can'] || ! $config['async_css'] ) {
			// should bail out & files will exist.
			$this->assertTrue( $this->filesystem->exists( $item_path ) );
			if ( $this->async_css_mobile ) {
				$this->assertTrue( $this->filesystem->exists( $mobile_item_path ) );
			}
		}

		if ( $expected['desktop'] ) {
			$this->assertFalse( $this->filesystem->exists( $item_path ) );
		}

		if ( $expected['mobile'] ) {
			$this->assertFalse( $this->filesystem->exists( $mobile_item_path ) );
		}
	}

	protected function setUpUser() {
		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_regenerate_critical_css' );

		$user_id = $this->factory->user->create(
			[ 'role' => 'administrator' ]
		);

		wp_set_current_user( $user_id );
	}

	public function async_css() {
		return $this->async_css;
	}

	public function async_css_mobile() {
		return $this->async_css_mobile;
	}
}
