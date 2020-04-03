<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SimpleCustomCss;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\ThirdParty\Plugins\SimpleCustomCss;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\SimpleCustomCss::update_cache_file
 * @group  ThirdParty
 * @group  WithSCCSS
 */
class Test_DeleteCacheFile extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Plugins/SimpleCustomCss/updateCacheFile.php';
	private $sccss;

	public function setUp() {
		parent::setUp();
		$this->sccss = new SimpleCustomCss();

		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_CACHE_BUSTING_PATH' )->andReturn( 'wp-content/cache/busting/' );
		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_CACHE_BUSTING_URL' )->andReturn( 'http://example.org/wp-content/cache/busting/' );

		Functions\expect( 'rocket_clean_domain' );
		Functions\expect( 'wp_kses' )->andReturnFirstArg();

		Functions\expect( 'get_option' )
			->with( 'sccss_settings' )
			->andReturn( [
				'sccss-content' => '.simple-custom-css { color: red; }',
			] );

		$this->filesystem->chmod(  'wp-content/cache/busting/index.php', 0644 );
		$this->filesystem->chmod(  'wp-content/cache/busting/', 0755 );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDeleteTheFileAndRecreateIt( $blog_id, $filepath ) {
		$this->filesystem->setFilemtime( $filepath, strtotime( '11 hours ago' ) );
		Functions\expect( 'get_current_blog_id' )->andReturn( $blog_id );

		$this->sccss->update_cache_file();
		$this->assertTrue( $this->filesystem->exists( $filepath ) );
	}

}
