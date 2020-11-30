<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\ImagesDimensions\Subscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Media\ImagesDimensions\Subscriber::specify_image_dimensions
 * @group  ImagesDimensions
 * @group  Media
 */
class Test_SpecifyImageDimensions extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Media/ImagesDimensions/ImagesDimensions/specifyImageDimensions.php';

	private $config_data = [];

	public function tearDown() {
		if ( isset( $this->config_data['images_dimensions'] ) ){
			remove_filter( 'pre_get_rocket_option_images_dimensions', [$this, 'set_image_dimensions'] );
		}

		if ( isset( $this->config_data['rocket_specify_image_dimensions_filter'] ) ){
			remove_filter( 'rocket_specify_image_dimensions', [$this, 'filter_rocket_specify_image_dimensions'] );
		}

		if ( isset( $config['rocket_specify_dimension_images_inside_pictures_filter'] ) ){
			remove_filter( 'rocket_specify_dimension_images_inside_pictures', [$this, 'filter_rocket_specify_dimension_images_inside_pictures'] );
		}

		if ( isset( $this->config_data['external'] ) || isset( $this->config_data['internal'] ) ) {
			if ( isset( $this->config_data['rocket_specify_image_dimensions_for_distant_filter'] ) ){
				remove_filter( 'rocket_specify_image_dimensions_for_distant', [$this, 'filter_rocket_specify_image_dimensions_for_distant'] );
			}
		}

		unset( $GLOBALS['wp'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddMissedDimensions( $input, $config, $expected ) {
		$this->config_data = $config;

		if ( isset( $config['images_dimensions'] ) ){
			add_filter( 'pre_get_rocket_option_images_dimensions', [$this, 'set_image_dimensions'] );
		}

		if ( isset( $config['rocket_specify_image_dimensions_filter'] ) ){
			add_filter( 'rocket_specify_image_dimensions', [$this, 'filter_rocket_specify_image_dimensions'] );
		}

		if ( isset( $config['rocket_specify_dimension_images_inside_pictures_filter'] ) ){
			add_filter( 'rocket_specify_dimension_images_inside_pictures', [$this, 'filter_rocket_specify_dimension_images_inside_pictures'] );
		}

		if ( isset( $config['external'] ) || isset( $config['internal'] ) ) {
			if ( isset( $config['rocket_specify_image_dimensions_for_distant_filter'] ) ){
				add_filter( 'rocket_specify_image_dimensions_for_distant', [$this, 'filter_rocket_specify_image_dimensions_for_distant'] );
			}
		}

		$GLOBALS['wp'] = (object) [
			'query_vars' => [],
			'request'    => 'http://example.org'
		];

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( apply_filters( 'rocket_buffer', $input ) )
		);

	}

	public function set_image_dimensions( $value ) {
		return $this->config_data['images_dimensions'];
	}

	public function filter_rocket_specify_image_dimensions( $value ) {
		return $this->config_data['rocket_specify_image_dimensions_filter'];
	}

	public function filter_rocket_specify_image_dimensions_for_distant( $value ) {
		return $this->config_data['rocket_specify_image_dimensions_for_distant_filter'];
	}

	public function filter_rocket_specify_dimension_images_inside_pictures( $value ) {
		return $this->config_data['rocket_specify_dimension_images_inside_pictures_filter'];
	}
}
