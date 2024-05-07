<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\Subscriber;

use Mockery;
use Brain\Monkey\Filters;
use WP_Rocket\Dependencies\RocketLazyload\Assets;
use WP_Rocket\Dependencies\RocketLazyload\Image;
use WP_Rocket\Dependencies\RocketLazyload\Iframe;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\Lazyload\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\Subscriber::maybe_disable_core_lazyload
 *
 * @group Media
 * @group Lazyload
 */
class Test_MaybeDisableCoreLazyload extends TestCase {
	private $options;
	private $subscriber;

	public function setUp(): void {
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
			->atMost()
			->once()
			->with( 'lazyload', 0 )
			->andReturn( $config['lazyload'] );

		$this->options->shouldReceive( 'get' )
			->atMost()
			->once()
			->with( 'lazyload_iframes', 0 )
			->andReturn( $config['lazyload_iframes'] );

		Filters\expectApplied( 'do_rocket_lazyload' )
			->atMost()
			->once()
			->andReturn( $config['lazyload_filter'] );

		Filters\expectApplied( 'do_rocket_lazyload_iframes' )
			->atMost()
			->once()
			->andReturn( $config['lazyload_iframes_filter'] );

		$this->assertSame(
			$expected,
			$this->subscriber->maybe_disable_core_lazyload( $config['value'], $config['tag_name'] )
		);
	}
}
