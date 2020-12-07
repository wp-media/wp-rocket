<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\ImageDimensions\ImageDimensions;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\ImageDimensions\ImageDimensions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Media\ImageDimensions\ImageDimensions::specify_image_dimensions
 * @group  ImageDimensions
 * @group  Media
 */
class Test_SpecifyImageDimensions extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Media/ImageDimensions/ImageDimensions/specifyImageDimensions.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddMissedDimensions( $input, $config, $expected ) {
		$options = Mockery::mock( Options_Data::class );

		if (
			isset( $config['image_dimensions'] )
			&&
			(
				! isset( $config['rocket_specify_image_dimensions_filter'] )
				||
				! $config['rocket_specify_image_dimensions_filter']
			)
		){
			$options->shouldReceive( 'get' )
			        ->once()
			        ->with( 'image_dimensions', false )
			        ->andReturn( $config['image_dimensions'] );
		}

		if ( isset( $config['rocket_specify_image_dimensions_filter'] ) ){
			Filters\expectApplied( 'rocket_specify_image_dimensions' )
				->once()
				->with( false )
				->andReturn( $config['rocket_specify_image_dimensions_filter'] );
		}

		if ( isset( $config['rocket_specify_dimension_skip_pictures_filter'] ) ){
			Filters\expectApplied( 'rocket_specify_dimension_skip_pictures' )
				->once()
				->with( true )
				->andReturn( $config['rocket_specify_dimension_skip_pictures_filter'] );
		}

		$frontend = new ImageDimensions( $options, $this->filesystem );

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
