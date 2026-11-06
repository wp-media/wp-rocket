<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\Subscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Dependencies\RocketLazyload\Assets;
use WP_Rocket\Dependencies\RocketLazyload\Image;
use WP_Rocket\Dependencies\RocketLazyload\Iframe;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\Lazyload\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\Subscriber::add_exclusions
 *
 * @group Media
 * @group Lazyload
 */
class Test_AddExclusions extends TestCase {
	private $options;
	private $subscriber;

	public function setUp() : void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );

		$this->subscriber = new Subscriber(
			$this->options,
			Mockery::mock( Assets::class ),
			Mockery::mock( Image::class ),
			Mockery::mock( Iframe::class )
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->options->shouldReceive( 'get' )
			->once()
			->with( 'exclude_lazyload', [] )
			->andReturn( $config['option'] );

		$this->assertSame(
			array_values( $expected ),
			array_values( $this->subscriber->add_exclusions( $config['exclusions'] ) )
		);
	}
}
