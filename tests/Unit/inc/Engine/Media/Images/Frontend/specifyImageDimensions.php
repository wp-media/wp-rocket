<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Images\Frontend;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\Images\Frontend;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Mockery;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Media\Images\Frontend::specify_image_dimensions
 * @group  Media
 */
class Test_SpecifyImageDimensions extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Media/Images/Frontend/specifyImageDimensions.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddMissedDimensions( $input, $config, $expected ) {
		$options = Mockery::mock( Options_Data::class );

		if ( isset( $config['image_dimensions'] ) ){
			$options->shouldReceive( 'get' )
			        ->once()
			        ->with( 'image_dimensions', false )
			        ->andReturn( $config['image_dimensions'] );
		}

		if ( isset( $config['rocket_specify_image_dimensions_filter'] ) ){
			Filters\expectApplied( 'rocket_specify_image_dimensions' )
				->once()
				->with( true )
				->andReturn( $config['rocket_specify_image_dimensions_filter'] );
		}

		$frontend = new Frontend( $options, $this->filesystem );

		if ( isset( $config['external'] ) || isset( $config['internal'] ) ) {
			Functions\expect( 'get_rocket_parse_url' )
				->zeroOrMoreTimes()
				->andReturnUsing(
					function( $value ) {
						return parse_url( $value );
					}
				);

			Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content' );

			Functions\when( 'wp_parse_url' )->alias( function( $url, $component = - 1 ) {
				return parse_url( $url, $component );
			} );

			Functions\when( 'home_url' )->justReturn( 'https://example.org/' );

			if ( isset( $config['rocket_specify_image_dimensions_for_distant_filter'] ) ){
				Filters\expectApplied( 'rocket_specify_image_dimensions_for_distant' )
					->once()
					->with( true )
					->andReturn( $config['rocket_specify_image_dimensions_for_distant_filter'] );
			}
		}

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $frontend->specify_image_dimensions( $input ) )
		);

	}
}
