<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\PartialPreloadSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\PartialPreloadSubscriber;
use WP_Rocket\Engine\Preload\PartialProcess;

/**
 * @covers \WP_Rocket\Engine\Preload\PartialPreloadSubscriber::preload_after_automatic_cache_purge
 * @group  Preload
 */
class Test_PreloadAfterAutomaticCachePurge extends TestCase {
	private $urls = [];
	private $options;
	private $partial_process;
	private $subscriber;
	private $property;

	public function setUp() {
		$this->options         = Mockery::mock( Options_Data::class );
		$this->partial_process = Mockery::mock( PartialProcess::class );
		$this->subscriber      = new PartialPreloadSubscriber( $this->partial_process, $this->options );
		$this->property        = $this->get_reflective_property( 'urls', $this->subscriber );
		$this->property->setAccessible( true );

		Functions\when( 'untrailingslashit' )->alias( function( $string ) {
			return rtrim( $string, '/\\' );
		} );
	}

	protected function tearDown() {
		parent::tearDown();
		$this->urls = [];
		$this->property->setAccessible( false );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $permalink_structure, $option_value, $deleted, $expected ) {
		if ( $deleted ) {
			$this->options->shouldReceive( 'get' )->andReturn( $option_value );
		}

		Functions\when( 'get_option' )->justReturn( $permalink_structure );

		$this->subscriber->preload_after_automatic_cache_purge( $deleted );

		$this->property = $this->get_reflective_property( 'urls', $this->subscriber );
		$this->urls     = $this->property->getValue( $this->subscriber );

		if ( ! $expected ) {
			$this->assertEmpty( $this->urls );
		}

		foreach ( $expected as $url ) {
			$this->assertContains( $url, $this->urls );
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'preloadAfterAutomaticCachePurge' );
	}
}
