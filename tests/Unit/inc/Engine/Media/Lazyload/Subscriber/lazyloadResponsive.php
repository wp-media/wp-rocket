<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\Subscriber;

use Mockery;
use Brain\Monkey\Filters;
use WP_Rocket\Dependencies\RocketLazyload\Assets;
use WP_Rocket\Dependencies\RocketLazyload\Image;
use WP_Rocket\Dependencies\RocketLazyload\Iframe;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Media\Lazyload\Subscriber;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\Subscriber::lazyload_responsive
 *
 * @group Media
 * @group Lazyload
 */
class Test_LazyloadResponsive extends TestCase {
	private $image;
	private $subscriber;

	public function setUp() : void {
		parent::setUp();

		$this->image = Mockery::mock( Image::class );

		$this->subscriber = new Subscriber(
			Mockery::mock( Options_Data::class ),
			Mockery::mock( Assets::class ),
			$this->image,
			Mockery::mock( Iframe::class )
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $html, $expected ) {
		Filters\expectApplied( 'rocket_use_native_lazyload_images' )
		->andReturn( $config['is_native'] );

		$this->image->shouldReceive( 'lazyloadResponsiveAttributes' )
			->atMost()
			->once()
			->with( $html )
			->andReturn( $expected );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $this->subscriber->lazyload_responsive( $html ) )
		);
	}
}
