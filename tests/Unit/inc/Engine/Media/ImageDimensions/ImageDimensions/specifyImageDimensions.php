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

	private $options;
	private $image_dimensions;

	public function setUp(): void
	{
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->image_dimensions = new ImageDimensions( $this->options, $this->filesystem );
	}

	public function testShouldReturnUnchangedHTMLWhenCannotSpecifyImageDimensions() {
		$this->options->shouldReceive( 'get' )
		              ->once()
		              ->with( 'image_dimensions', false )
		              ->andReturn( false );

		$this->assertSame(
			'<img src="http://example.com/path/to/image.jpg" />',
			$this->image_dimensions->specify_image_dimensions('<img src="http://example.com/path/to/image.jpg" />')
		);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddMissedDimensions( $input, $config, $expected ) {

		$site_url = $config['site_url'] ?? 'http://example.org/';
		$home_url = $config['home_url'] ?? 'http://example.org/';

		Functions\when( 'site_url' )->alias( function( $path = '') use ( $site_url ) {
			return $site_url . ltrim( $path, '/' );
		} );

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

			Functions\when( 'home_url' )->justReturn( $home_url );

			if ( isset( $config['internal'] ) ) {
				Functions\expect( 'wp_make_link_relative' )->andReturnUsing( function( $url ) {
					return preg_replace( '|^(https?:)?//[^/]+(/?.*)|i', '$2', $url );
				} );

				Functions\when( 'sanitize_text_field' )->returnArg();

				Functions\when( 'wp_unslash' )->alias(
					function ( $value ) {
						return stripslashes( $value );
					}
				);

				$_SERVER['DOCUMENT_ROOT'] = "vfs://public";

				Functions\expect( 'wp_basename' )->andReturnUsing( function ( $path, $suffix = '' ) {
					return urldecode( basename( str_replace( array( '%2F', '%5C' ), '/', urlencode( $path ) ), $suffix ) );
				} );
			}

			if ( isset( $config['rocket_specify_image_dimensions_for_distant_filter'] ) ){
				Filters\expectApplied( 'rocket_specify_image_dimensions_for_distant' )
					->once()
					->with( false )
					->andReturn( $config['rocket_specify_image_dimensions_for_distant_filter'] );
			}
		}
		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $this->image_dimensions->specify_image_dimensions( $input ) )
		);

	}
}
