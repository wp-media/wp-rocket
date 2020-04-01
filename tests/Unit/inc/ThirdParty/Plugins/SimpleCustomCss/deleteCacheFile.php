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
	protected $path_to_test_data = '/inc/ThirdParty/Plugins/SimpleCustomCss/cacheSccss.php';
	private $sccss;

	public function setUp() {
		parent::setUp();
		$this->sccss = new SimpleCustomCss();

		Functions\expect( 'rocket_clean_domain' );
		Functions\expect( 'wp_kses' )->andReturnFirstArg();

		if ( ! defined('FS_CHMOD_FILE')) {
			define( 'FS_CHMOD_FILE', ( 0644 & ~ umask() ) );
		}
	}

	public function testShouldDeleteTheFileAndRecreateIt() {
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

		$this->assertTrue( $this->filesystem->exists( $filepath ) );

		$this->sccss->delete_cache_file();
		$this->assertTrue( $this->filesystem->exists( $filepath ) );
	}


}
