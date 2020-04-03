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
class Test_UpdateCacheFile extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Plugins/SimpleCustomCss/updateCacheFile.php';
	private $busting_path;
	private $busting_url = 'http://example.org/wp-content/cache/busting/';

	public function setUp() {
		parent::setUp();

		$this->busting_path = $this->filesystem->getUrl( 'wp-content/cache/busting/' );

		Functions\expect( 'rocket_clean_domain' )
			->once();
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
		Functions\when( 'get_current_blog_id' )->justReturn( $blog_id );

		$sccss = new SimpleCustomCss( $this->busting_path, $this->busting_url );
		$sccss->update_cache_file();
		$this->assertTrue( $this->filesystem->exists( $filepath ) );
	}

}
