<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\Facebook_Tracking_Cache_Busting_Subscriber;

use \Mockery;
use WP_Rocket\Busting\Busting_Factory;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Subscriber\Facebook_Tracking_Cache_Busting_Subscriber::delete_cache
 * @group  ThirdParty
 * @group  FacebookTracking
 */
class Test_DeleteCache extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/classes/subscriber/Facebook_Tracking_Cache_Busting_Subscriber/deleteCache.php';
	private $subscriber;
	private $original_factory;

	public function testShouldNotDeleteBustingFilesWhenNotClearingAllCache() {
		$this->mockFactory();

		// Enable facebook cache.
		add_filter( 'pre_get_rocket_option_facebook_pixel_cache', [ $this, 'return_true' ] );

		do_action( 'rocket_purge_cache', 'post', 123, '', '' );

		remove_filter( 'pre_get_rocket_option_facebook_pixel_cache', [ $this, 'return_true' ] );

		foreach ( $this->original_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		$this->restoreFactory();
	}

	public function testShouldNotDeleteBustingFilesWhenNotEnabled() {
		$this->mockFactory();

		// Disable facebook cache.
		add_filter( 'pre_get_rocket_option_facebook_pixel_cache', [ $this, 'return_false' ] );

		do_action( 'rocket_purge_cache', 'all', 0, '', '' );

		remove_filter( 'pre_get_rocket_option_facebook_pixel_cache', [ $this, 'return_false' ] );

		foreach ( $this->original_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		$this->restoreFactory();
	}

	public function testShouldDeleteBustingFilesWhenClearingAllCacheAndEnabled() {
		// Enable facebook cache.
		add_filter( 'pre_get_rocket_option_facebook_pixel_cache', [ $this, 'return_true' ] );

		do_action( 'rocket_purge_cache', 'all', 0, '', '' );

		remove_filter( 'pre_get_rocket_option_facebook_pixel_cache', [ $this, 'return_true' ] );

		foreach ( $this->original_files as $file ) {
			$this->assertFalse( $this->filesystem->exists( $file ) );
		}
	}

	private function mockFactory() {
		$container        = apply_filters( 'rocket_container', '' );
		$this->subscriber = $container->get( 'facebook_tracking_subscriber' );

		$original_factory_ref   = $this->get_reflective_property( 'busting_factory', $this->subscriber );
		$this->original_factory = $original_factory_ref->getValue( $this->subscriber );

		$mocked_factory = Mockery::mock( Busting_Factory::class );
		$mocked_factory->shouldReceive( 'type' )
		               ->never()
		               ->with( 'fbsdk' );
		$mocked_factory->shouldReceive( 'type' )
		               ->never()
		               ->with( 'fbpix' );

		$original_factory_ref->setValue( $this->subscriber, $mocked_factory );
	}

	private function restoreFactory() {
		$this->set_reflective_property( $this->original_factory, 'busting_factory', $this->subscriber );
	}
}
