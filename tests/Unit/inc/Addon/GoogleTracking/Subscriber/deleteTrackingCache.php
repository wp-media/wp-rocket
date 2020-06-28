<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\GoogleTracking\Subscriber;

use Mockery;
use WP_Rocket\Addon\GoogleTracking\GoogleAnalytics;
use WP_Rocket\Addon\GoogleTracking\GoogleTagManager;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Addon\Busting\BustingFactory;
use WP_Rocket\Addon\GoogleTracking\Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Addon\GoogleTracking\Subscriber::delete_tracking_cache
 * @group  Addon
 * @group  GoogleTracking
 */
class Test_DeleteTrackingCache extends TestCase {

	public function testShouldNotDeleteBustingFilesWhenNotClearingAllCache() {
		$subscriber = new Subscriber( $this->getFactory( false ), $this->getOptionsData( true ) );
		$deleted    = $subscriber->delete_tracking_cache( 'post' );
		$this->assertFalse( $deleted );
	}

	public function testShouldNotDeleteBustingFilesWhenNotEnabled() {
		$subscriber = new Subscriber( $this->getFactory( false ), $this->getOptionsData( false ) );
		$deleted    = $subscriber->delete_tracking_cache( 'all' );
		$this->assertFalse( $deleted );
	}

	public function testShouldDeleteBustingFilesWhenClearingAllCacheAndEnabled() {
		$subscriber = new Subscriber( $this->getFactory( true ), $this->getOptionsData( true ) );
		$deleted    = $subscriber->delete_tracking_cache( 'all' );
		$this->assertTrue( $deleted );
	}

	private function getFactory( $shouldDelete ) {
		$factory = Mockery::mock( BustingFactory::class );

		if ( ! $shouldDelete ) {
			$factory->shouldReceive( 'type' )
			        ->never()
					->with( 'gtm' );

			$factory->shouldReceive( 'type' )
			        ->never()
			        ->with( 'ga' );
			return $factory;
		}

		$factory->shouldReceive( 'type' )
		        ->once()
		        ->with( 'gtm' )
		        ->andReturnUsing(
					function() {
						$mock = Mockery::mock( GoogleTagManager::class );
						$mock->shouldReceive( 'delete' )
						     ->once()
						     ->andReturn( true );
						return $mock;
					}
				);

		$factory->shouldReceive( 'type' )
		        ->once()
		        ->with( 'ga' )
		        ->andReturnUsing(
					function() {
						$mock = Mockery::mock( GoogleAnalytics::class );
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
