<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SimpleCustomCss;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\ThirdParty\Plugins\SimpleCustomCss;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\SimpleCustomCss::update_cache_file
 *
 * @group  ThirdParty
 * @group  WithSCCSS
 */
class Test_UpdateCacheFile extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Plugins/SimpleCustomCss/updateCacheFile.php';
	private   $busting_path      = 'vfs://public/wp-content/cache/busting/';
	private   $busting_url       = 'http://example.org/wp-content/cache/busting/';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDeleteTheFileAndRecreateIt( $blog_id, $filepath ) {
		$this->filesystem->setFilemtime( $filepath, strtotime( '11 hours ago' ) );

		Functions\when( 'get_current_blog_id' )->justReturn( $blog_id );
		Functions\expect( 'rocket_clean_domain' )->once()->andReturnNull();
		Functions\expect( 'wp_kses' )->andReturnFirstArg();
		Functions\expect( 'get_option' )
			->with( 'sccss_settings' )
			->andReturn(
				[
					'sccss-content' => '.simple-custom-css { color: red; }',
				]
			);

		$sccss = new SimpleCustomCss( $this->busting_path, $this->busting_url );
		$sccss->update_cache_file();
		$this->assertTrue( $this->filesystem->exists( $filepath ) );
	}
}
