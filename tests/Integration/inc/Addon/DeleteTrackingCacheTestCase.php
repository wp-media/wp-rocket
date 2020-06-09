<?php

namespace WP_Rocket\Tests\Integration\inc\Addon;

use Mockery;
use WP_Rocket\Busting\Busting_Factory;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

abstract class DeleteTrackingCacheTestCase extends FilesystemTestCase {
	protected $path_to_test_data = '';
	protected $option_name       = '';
	protected $subscriber_name   = '';
	protected $factory_types     = [];

	protected $subscriber;
	protected $original_factory;

	public function testShouldNotDeleteBustingFilesWhenNotClearingAllCache() {
		$this->mockFactory();

		// Enable tracking cache.
		add_filter( 'pre_get_rocket_option_' . $this->option_name, [ $this, 'return_true' ] );

		do_action( 'rocket_purge_cache', 'post', 123, '', '' );

		remove_filter( 'pre_get_rocket_option_' . $this->option_name, [ $this, 'return_true' ] );

		foreach ( $this->original_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		$this->restoreFactory();
	}

	public function testShouldNotDeleteBustingFilesWhenNotEnabled() {
		$this->mockFactory();

		// Disable tracking cache.
		add_filter( 'pre_get_rocket_option_' . $this->option_name, [ $this, 'return_false' ] );

		do_action( 'rocket_purge_cache', 'all', 0, '', '' );

		remove_filter( 'pre_get_rocket_option_' . $this->option_name, [ $this, 'return_false' ] );

		foreach ( $this->original_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		$this->restoreFactory();
	}

	public function testShouldDeleteBustingFilesWhenClearingAllCacheAndEnabled() {
		// Enable tracking cache.
		add_filter( 'pre_get_rocket_option_' . $this->option_name, [ $this, 'return_true' ] );

		do_action( 'rocket_purge_cache', 'all', 0, '', '' );

		remove_filter( 'pre_get_rocket_option_' . $this->option_name, [ $this, 'return_true' ] );

		foreach ( $this->original_files as $file ) {
			$this->assertFalse( $this->filesystem->exists( $file ) );
		}
	}

	protected function mockFactory() {
		$container        = apply_filters( 'rocket_container', '' );
		$this->subscriber = $container->get( $this->subscriber_name );

		$original_factory_ref   = $this->get_reflective_property( 'busting_factory', $this->subscriber );
		$this->original_factory = $original_factory_ref->getValue( $this->subscriber );

		$mocked_factory = Mockery::mock( Busting_Factory::class );

		foreach ( $this->factory_types as $factory_type ) {
			$mocked_factory->shouldReceive( 'type' )
			               ->never()
			               ->with( $factory_type );
		}

		$original_factory_ref->setValue( $this->subscriber, $mocked_factory );
	}

	protected function restoreFactory() {
		$this->set_reflective_property( $this->original_factory, 'busting_factory', $this->subscriber );
	}
}
