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

		Functions\expect( 'rocket_has_constant' )->with( 'FS_CHMOD_DIR' )->andReturn( true );
		Functions\expect( 'rocket_get_constant' )->with( 'FS_CHMOD_DIR' )->andReturn( 0755 & ~ umask() );
	}

	public function testFileExistsShouldEnqueueAndRemoveOriginalWhenFileExists() {
		$bustingpath = 'wp-content/cache/busting/1/';
		$filepath    = 'wp-content/cache/busting/1/sccss.css';
		$url         = 'http://example.org/wp-content/cache/busting/1/sccss.css';

		$this->filesystem->setFilemtime( $filepath, strtotime( '11 hours ago' ) );

		Functions\expect( 'rocket_get_cache_busting_paths' )
			->with( 'sccss.css', 'css' )
			->andReturn( [
				'bustingpath' => $bustingpath,
				'filepath'    => $filepath,
				'url'         => $url,
			] );

		Functions\expect( 'wp_enqueue_style' )->once();
		Functions\expect( 'remove_action' )->once();

		$this->assertTrue( $this->filesystem->exists( $filepath ) );

		$this->sccss->cache_sccss();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testCreateFileAndBustingFolderAndEnqueue( $bustingpath, $filepath, $url ) {
		$this->filesystem->setFilemtime( $filepath, strtotime( '11 hours ago' ) );

		Functions\expect( 'rocket_get_cache_busting_paths' )
			->with( 'sccss.css', 'css' )
			->andReturn( [
				'bustingpath' => $bustingpath,
				'filepath'    => $filepath,
				'url'         => $url,
			] );

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
