<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SimpleCustomCss;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\ThirdParty\Plugins\SimpleCustomCss;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\SimpleCustomCss::delete_cache_file
 * @group  ThirdParty
 * @group  WithSCCSS
 */
class Test_DeleteCacheFile extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Plugins/SimpleCustomCss/deleteCacheFile.php';
	private $sccss;

	public function setUp() {
		parent::setUp();
		$this->sccss = new SimpleCustomCss();

		Functions\expect( 'rocket_clean_domain' );
		Functions\expect( 'wp_kses' )->andReturnFirstArg();

		Functions\expect( 'get_option' )
			->with( 'sccss_settings' )
			->andReturn( [
				'sccss-content' => '.simple-custom-css { color: red; }',
			] );

		Functions\expect( 'rocket_has_constant' )->with( 'FS_CHMOD_DIR' )->andReturn( true );
		Functions\expect( 'rocket_get_constant' )->with( 'FS_CHMOD_DIR' )->andReturn( 0755 & ~ umask() );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDeleteTheFileAndRecreateIt( $bustingpath, $filepath, $url ) {
		$this->filesystem->setFilemtime( $filepath, strtotime( '11 hours ago' ) );

		Functions\expect( 'rocket_get_cache_busting_paths' )
			->with( 'sccss.css', 'css' )
			->andReturn( [
				'bustingpath' => $bustingpath,
				'filepath'    => $filepath,
				'url'         => $url,
			] );

		$this->sccss->delete_cache_file();
		$this->assertTrue( $this->filesystem->exists( $filepath ) );
	}

}
