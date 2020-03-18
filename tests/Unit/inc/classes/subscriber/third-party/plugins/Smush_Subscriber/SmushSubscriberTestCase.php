<?php
namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Smush_Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\PHPUnit\Unit\TestCase;

abstract class SmushSubscriberTestCase extends TestCase {

	protected function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )
			->with( 'WP_SMUSH_PREFIX' )
			->andReturn( 'wp-smush-' );
	}

	protected function mock_is_smush_lazyload_enabled( $lazyload_enabled, array $lazyload_formats ) {
		$settings = Mockery::mock( 'alias:\Smush\Core\Settings' );
		$settings
			->shouldReceive( 'get_instance' )
			->andReturnUsing(
				function() use ( $lazyload_enabled, $lazyload_formats ) {
					$settings = Mockery::mock( '\WP_Rocket\Tests\Fixtures\ThirdPartyPlugins\Smush\Core\Settings' ); // Don't look for me, I don’t exist.
					$settings
						->shouldReceive( 'get' )
						->with( 'lazy_load' )
						->andReturn( (bool) $lazyload_enabled );
					$settings
						->shouldReceive( 'get_setting' )
						->with( 'wp-smush-lazy_load' )
						->andReturn(
							[
								'format' => $lazyload_formats,
							]
						);
					return $settings;
				}
			);

		return $settings;
	}
}
