<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SimpleCustomCss;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\ThirdParty\Plugins\SimpleCustomCss;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\SimpleCustomCss::cache_sccss
 *
 * @group  ThirdParty
 * @group  WithSCCSS
 */
class Test_CacheSccss extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Plugins/SimpleCustomCss/cacheSccss.php';
	private   $busting_path      = 'vfs://public/wp-content/cache/busting/';
	private   $busting_url       = 'http://example.org/wp-content/cache/busting/';

	public function testFileExistsShouldEnqueueAndRemoveOriginalWhenFileExists() {
		$filepath = 'wp-content/cache/busting/1/sccss.css';

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$this->filesystem->setFilemtime( $filepath, strtotime( '11 hours ago' ) );

		Functions\expect( 'wp_enqueue_style' )->once()->andReturnNull();

		$this->assertTrue( $this->filesystem->exists( $filepath ) );

		$scss = new SimpleCustomCss( $this->busting_path, $this->busting_url );
		$scss->cache_sccss();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testCreateFileAndBustingFolderAndEnqueue( $blog_id, $filepath ) {
		Functions\when( 'get_current_blog_id' )->justReturn( $blog_id );

		$this->filesystem->setFilemtime( $filepath, strtotime( '11 hours ago' ) );

		Functions\expect( 'wp_enqueue_style' )->once()->andReturnNull();

		Functions\expect( 'get_option' )
			->with( 'sccss_settings' )
			->andReturn(
				[
					'sccss-content' => '.simple-custom-css { color: red; }',
				]
			);
		Functions\expect( 'wp_kses' )->andReturnFirstArg();

		$this->assertFalse( $this->filesystem->exists( $filepath ) );

		$scss = new SimpleCustomCss( $this->busting_path, $this->busting_url );
		$scss->cache_sccss();

		$this->assertTrue( $this->filesystem->exists( $filepath ) );
	}
}
