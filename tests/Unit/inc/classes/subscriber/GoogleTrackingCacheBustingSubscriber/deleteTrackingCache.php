<?php

namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\GoogleTrackingCacheBustingSubscriber;

use \Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Busting\Busting_Factory;
use WP_Rocket\Busting\Google_Analytics;
use WP_Rocket\Subscriber\Google_Tracking_Cache_Busting_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\Google_Tracking_Cache_Busting_Subscriber::delete_tracking_cache
 * @group  ThirdParty
 * @group  GoogleTracking
 */
class Test_DeleteTrackingCache extends TestCase {

	public function testShouldNotDeleteBustingFilesWhenNotClearingAllCache() {
		$subscriber = new Google_Tracking_Cache_Busting_Subscriber( $this->getFactory( false ), $this->getOptionsData( true ) );
		$deleted    = $subscriber->delete_tracking_cache( 'post' );
		$this->assertFalse( $deleted );
	}

	public function testShouldNotDeleteBustingFilesWhenNotEnabled() {
		$subscriber = new Google_Tracking_Cache_Busting_Subscriber( $this->getFactory( false ), $this->getOptionsData( false ) );
		$deleted    = $subscriber->delete_tracking_cache( 'all' );
		$this->assertFalse( $deleted );
	}

	public function testShouldDeleteBustingFilesWhenClearingAllCacheAndEnabled() {
		$subscriber = new Google_Tracking_Cache_Busting_Subscriber( $this->getFactory( true ), $this->getOptionsData( true ) );
		$deleted    = $subscriber->delete_tracking_cache( 'all' );
		$this->assertTrue( $deleted );
	}

	private function getFactory( $shouldDelete ) {
		$factory = Mockery::mock( Busting_Factory::class );

		if ( ! $shouldDelete ) {
			$factory->shouldReceive( 'type' )
			        ->never()
			        ->with( 'ga' );
			return $factory;
		}

		$factory->shouldReceive( 'type' )
		        ->once()
		        ->with( 'ga' )
		        ->andReturnUsing(
					function() {
						$mock = Mockery::mock( Google_Analytics::class );
						$mock->shouldReceive( 'delete' )
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
		        ->with( 'google_analytics_cache', 0 )
		        ->andReturn( (bool) $isEnabled );

		return $options;
	}
}
