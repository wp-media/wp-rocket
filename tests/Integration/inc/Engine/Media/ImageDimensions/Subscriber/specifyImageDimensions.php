<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\ImageDimensions\Subscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\ImageDimensions\Subscriber::specify_image_dimensions
 * @group  ImageDimensions
 * @group  Media
 */
class Test_SpecifyImageDimensions extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Media/ImageDimensions/ImageDimensions/specifyImageDimensions.php';

	private $config_data = [];

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept('rocket_buffer', 'specify_image_dimensions', 17);
	}
	public function tear_down() {
		if ( isset( $this->config_data['image_dimensions'] ) ){
			remove_filter( 'pre_get_rocket_option_image_dimensions', [$this, 'set_image_dimensions'] );
		}

		if ( isset( $this->config_data['rocket_specify_image_dimensions_filter'] ) ){
			remove_filter( 'rocket_specify_image_dimensions', [$this, 'filter_rocket_specify_image_dimensions'] );
		}

		if ( isset( $this->config_data['rocket_specify_dimension_skip_pictures_filter'] ) ){
			remove_filter( 'rocket_specify_dimension_skip_pictures', [$this, 'filter_rocket_specify_dimension_skip_pictures'] );
		}

		if ( isset( $this->config_data['external'] ) || isset( $this->config_data['internal'] ) ) {
			if ( isset( $this->config_data['rocket_specify_image_dimensions_for_distant_filter'] ) ){
				remove_filter( 'rocket_specify_image_dimensions_for_distant', [$this, 'filter_rocket_specify_image_dimensions_for_distant'] );
			}
		}

		remove_filter( 'site_url', [ $this, 'setSiteUrl' ] );
		remove_filter( 'home_url', [ $this, 'setHomeUrl' ] );

		unset( $GLOBALS['wp'] );

		$this->restoreWpHook('rocket_buffer');

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddMissedDimensions( $input, $config, $expected ) {
		$this->config_data = $config;

		$_SERVER['DOCUMENT_ROOT'] = "vfs://public";

		if ( isset( $config['image_dimensions'] ) ){
			add_filter( 'pre_get_rocket_option_image_dimensions', [$this, 'set_image_dimensions'] );
		}

		if ( isset( $config['rocket_specify_image_dimensions_filter'] ) ){
			add_filter( 'rocket_specify_image_dimensions', [$this, 'filter_rocket_specify_image_dimensions'] );
		}

		if ( isset( $config['rocket_specify_dimension_skip_pictures_filter'] ) ){
			add_filter( 'rocket_specify_dimension_skip_pictures', [$this, 'filter_rocket_specify_dimension_skip_pictures'] );
		}

		if ( isset( $config['external'] ) || isset( $config['internal'] ) ) {
			if ( isset( $config['rocket_specify_image_dimensions_for_distant_filter'] ) ){
				add_filter( 'rocket_specify_image_dimensions_for_distant', [$this, 'filter_rocket_specify_image_dimensions_for_distant'] );
			}
		}

		add_filter( 'site_url', [ $this, 'setSiteUrl' ] );
		add_filter( 'home_url', [ $this, 'setHomeUrl' ] );

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
		return $this->config_data['image_dimensions'];
	}

	public function filter_rocket_specify_image_dimensions( $value ) {
		return $this->config_data['rocket_specify_image_dimensions_filter'];
	}

	public function filter_rocket_specify_image_dimensions_for_distant( $value ) {
		return $this->config_data['rocket_specify_image_dimensions_for_distant_filter'];
	}

	public function filter_rocket_specify_dimension_skip_pictures( $value ) {
		return $this->config_data['rocket_specify_dimension_skip_pictures_filter'];
	}

	public function setSiteUrl( $site_url ) {
		return $this->config_data['site_url'] ?? $site_url;
	}

	public function setHomeUrl( $home_url ) {
		return $this->config_data['home_url'] ?? $home_url;
	}
}
