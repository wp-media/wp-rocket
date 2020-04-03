<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SimpleCustomCss;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\ThirdParty\Plugins\SimpleCustomCss;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\SimpleCustomCss::cache_sccss
 * @group  ThirdParty
 * @group  WithSCCSS
 */
class Test_CacheSccss extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Plugins/SimpleCustomCss/cacheSccss.php';
	private $sccss;

	public function setUp() {
		parent::setUp();
		$this->sccss = new SimpleCustomCss();

		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_CACHE_BUSTING_PATH' )->andReturn( 'wp-content/cache/busting/' );
		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_CACHE_BUSTING_URL' )->andReturn( 'http://example.org/wp-content/cache/busting/' );
		$this->filesystem->chmod(  'wp-content/cache/busting/index.php', 0644 );
		$this->filesystem->chmod(  'wp-content/cache/busting/', 0755 );
	}

	public function testFileExistsShouldEnqueueAndRemoveOriginalWhenFileExists() {
		$filepath = 'wp-content/cache/busting/1/sccss.css';
		Functions\expect( 'get_current_blog_id' )->andReturn( 1 );
		$this->filesystem->setFilemtime( $filepath, strtotime( '11 hours ago' ) );

		Functions\expect( 'wp_enqueue_style' )->once();
		Functions\expect( 'remove_action' )->once();

		$this->assertTrue( $this->filesystem->exists( $filepath ) );

		$this->sccss->cache_sccss();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testCreateFileAndBustingFolderAndEnqueue( $blog_id, $filepath ) {
		Functions\expect( 'get_current_blog_id' )->andReturn( $blog_id );

		$this->filesystem->setFilemtime( $filepath, strtotime( '11 hours ago' ) );

		Functions\expect( 'wp_enqueue_style' )->once();
		Functions\expect( 'remove_action' )->once();

		Functions\expect( 'get_option' )
			->with( 'sccss_settings' )
			->andReturn( [
				'sccss-content' => '.simple-custom-css { color: red; }',
			] );
		Functions\expect( 'wp_kses' )->andReturnFirstArg();

		$this->assertFalse( $this->filesystem->exists( $filepath ) );

		$this->sccss->cache_sccss();

		$this->assertTrue( $this->filesystem->exists( $filepath ) );
	}
}
