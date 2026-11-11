<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSS;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\CriticalCSS::get_current_page_critical_css
 *
 * @group  CriticalPath
 * @group  vfs
 */
class Test_GetCurrentPageCriticalCSS extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSS/getCurrentPageCriticalCss.php';

	public function setUp() : void {
		parent::setUp();

		Functions\when( 'home_url' )->justReturn( 'http://example.org/' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected_file ) {
		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		foreach ( $config['expected_type'] as $expected_type ) {
			if ( ! empty( $expected_type['param'] ) ) {
				Functions\expect( $expected_type['type'] )
					->once()
					->with( $expected_type['param'] )
					->andReturn( $expected_type['return'] );
			} else {
				Functions\expect( $expected_type['type'] )
					->once()
					->andReturn( $expected_type['return'] );
			}
		}

		foreach ( $config['excluded_type'] as $excluded_type ) {
			Functions\expect( $excluded_type )->never();
		}

		$options = Mockery::mock( Options_Data::class );
		$options->shouldReceive( 'get' )
		    ->zeroOrMoreTimes()
		    ->with( 'do_caching_mobile_files', 0 )
		    ->andReturn( $config['settings']['do_caching_mobile_files'] );
		$options->shouldReceive( 'get' )
			->zeroOrMoreTimes()
			->with( 'async_css_mobile', 0 )
			->andReturn( $config['settings']['async_css_mobile'] );

		Functions\when( 'wp_is_mobile' )->justReturn( $config['wp_is_mobile'] );

		$critical_css = new CriticalCSS(
			Mockery::mock( CriticalCSSGeneration::class ),
			$options,
			$this->filesystem
		);

		$current_page_critical_css = $critical_css->get_current_page_critical_css();

		if ( ! empty( $expected_file ) ) {
			$this->assertSame(
				$this->filesystem->getUrl( $expected_file ),
				$current_page_critical_css
			);
		} else {
			$this->assertSame(
				$expected_file,
				$current_page_critical_css
			);
		}
	}
}
