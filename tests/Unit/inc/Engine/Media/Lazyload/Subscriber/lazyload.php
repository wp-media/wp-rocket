<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\Subscriber;

use Mockery;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use WP_Rocket\Dependencies\RocketLazyload\Assets;
use WP_Rocket\Dependencies\RocketLazyload\Image;
use WP_Rocket\Dependencies\RocketLazyload\Iframe;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Media\Lazyload\Subscriber;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\Subscriber::lazyload
 *
 * @group Media
 * @group Lazyload
 */
class Test_Lazyload extends TestCase {
	private $image;
	private $iframe;
	private $options;
	private $subscriber;

	public function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->image   = Mockery::mock( Image::class );
		$this->iframe  = Mockery::mock( Iframe::class );

		$this->subscriber = new Subscriber(
			$this->options,
			Mockery::mock( Assets::class ),
			$this->image,
			$this->iframe
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $html, $expected ) {
		$this->constants = [
			'REST_REQUEST' => $config['is_rest_request'],
			'DONOTLAZYLOAD' => $config['is_not_lazy_load'],
		];

		$this->donotrocketoptimize = $config['is_rocket_optimize'];

		Functions\when( 'is_admin' )->justReturn( $config['is_admin'] );
		Functions\when( 'is_feed' )->justReturn( $config['is_feed'] );
		Functions\when( 'is_preview' )->justReturn( $config['is_preview'] );
		Functions\when( 'is_search' )->justReturn( $config['is_search'] );

		$this->options->shouldReceive( 'get' )
			->atMost()
			->once()
			->with( 'lazyload', 0 )
			->andReturn( $config['options']['lazyload'] );

		$this->options->shouldReceive( 'get' )
			->atMost()
			->once()
			->with( 'lazyload_iframes', 0 )
			->andReturn( $config['options']['lazyload_iframes'] );

		Filters\expectApplied( 'rocket_use_native_lazyload_images' )
			->andReturn( $config['is_native'] );

		if ( ! $config['is_native'] ) {
			$this->image->shouldReceive( 'lazyloadPictures' )
				->atMost()
				->once()
				->andReturn( $html['after_picture'] );
		}

		$this->image->shouldReceive( 'lazyloadImages' )
			->atMost()
			->once()
			->andReturn( $expected );

		$this->image->shouldReceive( 'lazyloadBackgroundImages' )
			->atMost()
			->once()
			->andReturn( $expected );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $this->subscriber->lazyload( $html['original'] ) )
		);
	}
}
