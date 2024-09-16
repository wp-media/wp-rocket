<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\ImageDimensions\Subscriber;

use Mockery;
use WP_Rocket\Engine\Media\ImageDimensions\ImageDimensions;
use WP_Rocket\Engine\Media\ImageDimensions\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\ImageDimensions\Subscriber::image_dimensions_query_string
 *
 * @group ImageDimensions
 * @group Media
 */
class Test_ImageDimensionsQueryString extends TestCase {
	private $dimensions;
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->dimensions  = Mockery::mock( ImageDimensions::class );
		$this->subscriber = new Subscriber( $this->dimensions );
	}

	public function tear_down() {
		unset( $_GET );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $html, $expected ) {
		if ( isset( $config['query_string'] ) ) {
			$_GET[ $config['query_string'] ] = 1;
		}

		$this->dimensions
			->shouldReceive( 'specify_image_dimensions' )
		    ->with( $html )
		    ->andReturn( $expected );

		$this->assertSame(
			$expected,
			$this->subscriber->image_dimensions_query_string( $html )
		);
	}
}
