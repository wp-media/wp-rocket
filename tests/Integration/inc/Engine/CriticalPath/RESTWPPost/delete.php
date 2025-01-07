<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\RESTWPPost;

use WP_Rocket\Tests\Integration\RESTVfsTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\RESTWPPost::delete
 *
 * @uses \WP_Rocket\Engine\CriticalPath\ProcessorService::process_delete
 * @uses \WP_Rocket\Engine\CriticalPath\DataManager::delete_cpcss
 * @uses \WP_Rocket\Admin\Options_Data::get
 *
 * @group CriticalPath
 * @group CriticalRest
 */
class Test_Delete extends RESTVfsTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/RESTWPPost/delete.php';

	protected static $use_settings_trait = true;
	protected static $had_admin_cap      = false;

	private $post_id;
	private $post_type;
	private $is_mobile;
	private $files;
	private $options;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$admin                 = get_role( 'administrator' );
		static::$had_admin_cap = $admin->has_cap( 'rocket_regenerate_critical_css' );
	}

	public static function tear_down_after_class() {
		$admin = get_role( 'administrator' );
		if ( ! static::$had_cap ) {
			$admin->remove_cap( 'rocket_regenerate_critical_css' );
		}

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'async_css_mobile_cb' ] );

		$admin = get_role( 'administrator' );
		$admin->remove_cap( 'rocket_regenerate_critical_css' );

		parent::tear_down();
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testShouldDoExpectedWhenNotMultisite( $config, $expected ) {
		$cache_file_path = $this->filesystem->getUrl( "{$this->config['vfs_dir']}cache/wp-rocket/example.org/{$config['post_data']['post_title']}/index.html" );
		$this->assertTrue( $this->filesystem->exists( $cache_file_path ) );

		$this->setUpTest( 1, $config );

		$this->assertFilesExistBefore( $config );

		$dir_before = $this->filesystem->getListing( $this->files['dir'] );

		// Run it.
		$actual = $this->doRestDelete( "/wp-rocket/v1/cpcss/post/{$this->post_id}" );

		$this->assertSame( $expected, $actual );

		if ( ! empty ( $expected['success'] ) ) {
			$this->assertFalse( $this->filesystem->exists( $cache_file_path ) );
		} else {
			$this->assertTrue( $this->filesystem->exists( $cache_file_path ) );
		}

		// Check that the file(s) was(were) deleted.
		$this->assertFilesDeleted( $config );

		// Check the filesystem.
		$this->assertFileListings( $dir_before, $config );
	}

	private function setUpTest( $site_id, $config ) {
		$this->post_id   = ! isset( $config['post_data']['post_id'] )
			? $this->factory->post->create( $config['post_data'] )
			: $config['post_data']['post_id'];
		$this->post_type = isset( $config['post_data']['post_type'] ) ? $config['post_data']['post_type'] : 'post';
		$this->is_mobile = isset( $config['is_mobile'] ) ? $config['is_mobile'] : false;

		$dirs        = $this->filesystem->getUrl( "{$this->config['vfs_dir']}cache/critical-css/{$site_id}/" );
		$this->files = [
			'dir'        => $dirs,
			'non_mobile' => "{$dirs}posts/{$this->post_type}-{$this->post_id}.css",
			'mobile'     => "{$dirs}posts/{$this->post_type}-{$this->post_id}-mobile.css",
		];

		if ( $config['current_user_can'] ) {
			$this->setUpUser();
		}

		if ( isset( $config['options'] ) ) {
			$this->options = $config['options'];
			add_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'async_css_mobile_cb' ] );
		}
	}

	private function setUpUser() {
		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_regenerate_critical_css' );

		$user_id = $this->factory->user->create(
			[ 'role' => 'administrator' ]
		);

		wp_set_current_user( $user_id );
	}

	public function async_css_mobile_cb() {
		return $this->options['async_css_mobile'];
	}

	private function assertFilesExistBefore( $config ) {
		$this->assertSame(
			$config['cpcss_exists_before'],
			$this->filesystem->exists( $this->files['non_mobile'] )
		);

		if ( $this->is_mobile ) {
			$this->assertSame(
				$config['mobile_cpcss_exists_before'],
				$this->filesystem->exists( $this->files['mobile'] )
			);
		}
	}

	private function assertFilesDeleted( $config ) {
		$this->assertSame(
			$config['cpcss_exists_after'],
			$this->filesystem->exists( $this->files['non_mobile'] )
		);

		if ( $this->is_mobile ) {
			$this->assertSame(
				$config['mobile_cpcss_exists_after'],
				$this->filesystem->exists( $this->files['mobile'] )
			);
		}
	}

	private function assertFileListings( $dir_before, $config ) {
		$files = [];
		if ( $config['cpcss_exists_before'] && false === $config['cpcss_exists_after'] ) {
			$files[] = $this->files['non_mobile'];
		}
		if ( $config['mobile_cpcss_exists_before'] && false === $config['mobile_cpcss_exists_after'] ) {
			$files[] = $this->files['mobile'];
		}

		if ( empty( $files ) ) {
			return;
		}

		$entries = $this->filesystem->getListing( $this->files['dir'] );

		$dir_after = array_diff( $dir_before, $entries );
		$this->assertSame( $files, array_values( $dir_after ) );
	}
}
