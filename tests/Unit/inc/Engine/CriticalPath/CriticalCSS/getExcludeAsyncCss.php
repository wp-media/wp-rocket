<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSS;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Mockery;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\CriticalCSS::get_exclude_async_css
 *
 * @group  CriticalPath
 */
class Test_GetExcludeAsyncCss extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSS/getExcludeAsyncCss.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldGetExcludedFiles( $config, $expected ) {
		Functions\expect( 'get_current_blog_id' )->andReturn( 1 );
		Functions\expect( 'home_url' )->once()->with( '/' )->andReturn( 'http://example.com/' );

		Filters\expectApplied( 'rocket_exclude_async_css' )
			->once()
			->with( [] )
			->andReturn( $config );

		$critical_css = new CriticalCSS(
			Mockery::mock( CriticalCSSGeneration::class ),
			Mockery::mock( Options_Data::class ),
			$this->filesystem
		);

		$actual = array_values( $critical_css->get_exclude_async_css() );
		$this->assertEquals( $expected, $actual );
	}

}
