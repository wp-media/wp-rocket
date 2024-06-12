<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Support\Data;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Support\Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Support\Data::get_support_data
 *
 * @group Support
 */
class Test_GetSupportData extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $options_array, $expected ) {
		$this->rocket_version = '3.7.5';

		$options = Mockery::mock( Options_Data::class );
		$data    = new Data( $options );

		$options->shouldReceive( 'get_options' )
			->once()
			->andReturn( $options_array );

		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\expect( 'get_bloginfo' )
			->with( 'version' )
			->andReturn( '5.5' );
		
		Functions\when( 'wp_get_theme' )->alias( function() {
			return new \WP_Theme( 'default', 'wp-content/themes' );
		} );

		Functions\when( 'rocket_get_active_plugins' )
			->justReturn(
				[
					'Hello Dolly'
				]
			);

		$this->assertSame(
			$expected,
			$data->get_support_data()
		);
	}
}
