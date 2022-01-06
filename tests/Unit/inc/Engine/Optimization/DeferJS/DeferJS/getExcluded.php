<?php

declare( strict_types=1 );

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DeferJS\DeferJS;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DeferJS\DeferJS;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DeferJS\DeferJS::get_excluded
 *
 * @group  DeferJS
 */
class Test_GetExcluded extends TestCase {

	private $options;
	private $defer_js;

	public function setUp(): void {
		parent::setUp();

		$this->options  = Mockery::mock( Options_Data::class );
		$this->defer_js = new DeferJS( $this->options );
	}

	public function testShouldReturnEmptyWhenCannotDefer() {
		$this->options->shouldReceive( 'get' )
		              ->with( 'defer_all_js', 0 )
		              ->once()
		              ->andReturn( false );

		$this->assertEmpty( $this->defer_js->get_excluded() );
	}

	public function testShouldDeferDefaultItems() {
		$this->options->shouldReceive( 'get' )
		              ->with( 'defer_all_js', 0 )
		              ->once()
		              ->andReturn( true );

		$this->options->shouldReceive( 'get' )
		              ->with( 'exclude_defer_js', [] )
		              ->once()
		              ->andReturn( [] );

		$this->assertContains(
			'gist.github.com',
			$this->defer_js->get_excluded()
		);
	}

	public function testShouldDeferUserExcludedItems() {
		$this->options->shouldReceive( 'get' )
		              ->with( 'defer_all_js', 0 )
		              ->once()
		              ->andReturn( true );

		$this->options->shouldReceive( 'get' )
		              ->with( 'exclude_defer_js', [] )
		              ->once()
		              ->andReturn(
			              [
				              '/path/to/my/userfile.js'
			              ]
		              );

		$this->assertContains(
			'/path/to/my/userfile.js',
			$this->defer_js->get_excluded()
		);
	}

	public function testShouldUniquelyMergeDefaultAndUserExclusions() {
		$this->options->shouldReceive( 'get' )
		              ->with( 'defer_all_js', 0 )
		              ->once()
		              ->andReturn( true );

		$this->options->shouldReceive( 'get' )
		              ->with( 'exclude_defer_js', [] )
		              ->once()
		              ->andReturn(
			              [
				              // user adds an item already in default list.
				              'gist.github.com'
			              ]
		              );

		$excluded_items = $this->defer_js->get_excluded();

		$this->assertFalse(
			count( array_unique( $excluded_items ) ) < count( $excluded_items )
		);
	}
}
