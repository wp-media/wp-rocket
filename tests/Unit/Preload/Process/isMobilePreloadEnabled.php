<?php
namespace WP_Rocket\Tests\Unit\Preload\Process;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Preload\Process;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Preload\Process::is_mobile_preload_enabled
 * @group Preload
 */
class Test_isMobilePreloadEnabled extends TestCase {

	public function testShouldReturnTrueWhenOptionsEnabled() {
		$stub = $this->getMockForAbstractClass( Process::class );

		Functions\when( 'get_rocket_option' )->alias( function( $option, $default = '' ) {
			switch ( $option ) {
				case 'manual_preload':
				case 'cache_mobile':
				case 'do_caching_mobile_files':
					return 1;
			}
			return $default;
		} );

		$this->assertTrue( $stub->is_mobile_preload_enabled() );
	}

	public function testShouldReturnFalseWhenOptionsDisabled() {
		$stub = $this->getMockForAbstractClass( Process::class );

		Functions\when( 'get_rocket_option' )->alias( function( $option, $default = '' ) {
			switch ( $option ) {
				case 'manual_preload':
					return 0;
				case 'cache_mobile':
				case 'do_caching_mobile_files':
					return 1;
			}
			return $default;
		} );

		$this->assertFalse( $stub->is_mobile_preload_enabled() );

		Functions\when( 'get_rocket_option' )->alias( function( $option, $default = '' ) {
			switch ( $option ) {
				case 'manual_preload':
					return 1;
				case 'cache_mobile':
					return 0;
				case 'do_caching_mobile_files':
					return 1;
			}
			return $default;
		} );

		$this->assertFalse( $stub->is_mobile_preload_enabled() );

		Functions\when( 'get_rocket_option' )->alias( function( $option, $default = '' ) {
			switch ( $option ) {
				case 'manual_preload':
				case 'cache_mobile':
					return 1;
				case 'do_caching_mobile_files':
					return 0;
			}
			return $default;
		} );

		$this->assertFalse( $stub->is_mobile_preload_enabled() );
	}

	public function testShouldReturnBooleanWhenFiltered() {
		$stub = $this->getMockForAbstractClass( Process::class );

		Functions\when( 'get_rocket_option' )->alias( function( $option, $default = '' ) {
			switch ( $option ) {
				case 'manual_preload':
					return 0;
				case 'cache_mobile':
				case 'do_caching_mobile_files':
					return 1;
			}
			return $default;
		} );

		Filters\expectApplied( 'rocket_mobile_preload_enabled' )
			->once()
			->andReturn( 'boobar' ); // Simulate a filter.

		$this->assertTrue( $stub->is_mobile_preload_enabled() );

		Functions\when( 'get_rocket_option' )->alias( function( $option, $default = '' ) {
			switch ( $option ) {
				case 'manual_preload':
				case 'cache_mobile':
				case 'do_caching_mobile_files':
					return 1;
			}
			return $default;
		} );

		Filters\expectApplied( 'rocket_mobile_preload_enabled' )
			->once()
			->andReturn( '' ); // Simulate a filter.

		$this->assertFalse( $stub->is_mobile_preload_enabled() );
	}
}
