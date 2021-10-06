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
 * @covers \WP_Rocket\Engine\Media\Lazyload\Subscriber::maybe_add_skip_attributes
 *
 * @group Media
 * @group Lazyload
 */
class Test_maybeAddSkipAttributes extends TestCase {
	private $assets;
	private $options;
	private $subscriber;

	public function setUp(): void {
		parent::setUp();

		$this->assets  = Mockery::mock( Assets::class );
		$this->options = Mockery::mock( Options_Data::class );

		$this->subscriber = new Subscriber(
			$this->options,
			$this->assets,
			Mockery::mock( Image::class ),
			Mockery::mock( Iframe::class )
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Filters\expectApplied( 'rocket_use_native_lazyload_images' )
		->andReturn( $config['is_native'] );

		$this->assertSame(
			$expected,
			$this->subscriber->maybe_add_skip_attributes( $config['exclusions'] )
		);
	}
}
