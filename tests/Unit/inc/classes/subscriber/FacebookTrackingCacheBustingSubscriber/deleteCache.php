<?php

namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\FacebookTrackingCacheBustingSubscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Busting\Busting_Factory;
use WP_Rocket\Busting\Facebook_Pickles;
use WP_Rocket\Busting\Facebook_DSK;
use WP_Rocket\Subscriber\Facebook_Tracking_Cache_Busting_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\Facebook_Tracking_Cache_Busting_Subscriber::delete_cache
 * @group  ThirdParty
 * @group  FacebookTracking
 */
class Test_DeleteCache extends TestCase {

	public function testShouldNotDeleteBustingFilesWhenNotClearingAllCache() {
		$subscriber = new Facebook_Tracking_Cache_Busting_Subscriber( $this->getFactory( false ), $this->getOptionsData( true ) );
		$deleted    = $subscriber->delete_cache( 'post' );
		$this->assertFalse( $deleted );
	}

	public function testShouldNotDeleteBustingFilesWhenNotEnabled() {
		$subscriber = new Facebook_Tracking_Cache_Busting_Subscriber( $this->getFactory( false ), $this->getOptionsData( false ) );
		$deleted    = $subscriber->delete_cache( 'all' );
		$this->assertFalse( $deleted );
	}

	public function testShouldDeleteBustingFilesWhenClearingAllCacheAndEnabled() {
		$subscriber = new Facebook_Tracking_Cache_Busting_Subscriber( $this->getFactory( true ), $this->getOptionsData( true ) );
		$deleted    = $subscriber->delete_cache( 'all' );
		$this->assertTrue( $deleted );
	}

	private function getFactory( $shouldDelete ) {
		$factory = Mockery::mock( Busting_Factory::class );

		if ( ! $shouldDelete ) {
			$factory->shouldReceive( 'type' )
			        ->never()
			        ->with( 'fbsdk' );
			$factory->shouldReceive( 'type' )
			        ->never()
			        ->with( 'fbpix' );
			return $factory;
		}

		$factory->shouldReceive( 'type' )
		        ->once()
		        ->with( 'fbsdk' )
		        ->andReturnUsing(
					function() {
						$mock = Mockery::mock( Facebook_SDK::class );
						$mock->shouldReceive( 'delete' )
						     ->once();
						return $mock;
					}
				);
		$factory->shouldReceive( 'type' )
		        ->once()
		        ->with( 'fbpix' )
		        ->andReturnUsing(
					function() {
						$mock = Mockery::mock( Facebook_Pickles::class );
						$mock->shouldReceive( 'delete_all' )
						     ->once()
						     ->andReturn( true );
						return $mock;
					}
				);

		return $factory;
	}

	private function getOptionsData( $isEnabled ) {
		$options = Mockery::mock( Options_Data::class );

		$options->shouldReceive( 'get' )
		        ->with( 'facebook_pixel_cache', 0 )
		        ->andReturn( (bool) $isEnabled );

		return $options;
	}
}
