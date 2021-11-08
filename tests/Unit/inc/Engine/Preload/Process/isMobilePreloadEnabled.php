<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Process;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Preload\PartialProcess;

/**
 * @covers \WP_Rocket\Engine\Preload\AbstractProcess::is_mobile_preload_enabled
 * @group Preload
 */
class Test_IsMobilePreloadEnabled extends TestCase {
	private $process;

	public function setUp() : void {
		parent::setUp();

		$this->process = new PartialProcess();
	}

	public function testShouldReturnTrueWhenOptionsEnabled() {
		Functions\when( 'get_rocket_option' )->alias( function( $option, $default = '' ) {
			switch ( $option ) {
				case 'manual_preload':
				case 'cache_mobile':
				case 'do_caching_mobile_files':
					return 1;
			}
			return $default;
		} );

		$this->assertTrue( $this->process->is_mobile_preload_enabled() );
	}

	public function testShouldReturnFalseWhenOptionsDisabled() {
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

		$this->assertFalse( $this->process->is_mobile_preload_enabled() );

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

		$this->assertFalse( $this->process->is_mobile_preload_enabled() );

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

		$this->assertFalse( $this->process->is_mobile_preload_enabled() );
	}

	public function testShouldReturnBooleanWhenFiltered() {
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

		$this->assertTrue( $this->process->is_mobile_preload_enabled() );

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

		$this->assertFalse( $this->process->is_mobile_preload_enabled() );
	}
}
