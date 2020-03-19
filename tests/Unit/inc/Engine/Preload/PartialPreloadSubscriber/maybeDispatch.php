<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\PartialPreloadSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\PartialPreloadSubscriber;
use WP_Rocket\Engine\Preload\PartialProcess;

/**
 * @covers \WP_Rocket\Engine\Preload\PartialPreloadSubscriber::maybe_dispatch
 * @group  Preload
 */
class Test_MaybePreloadMobileHomepage extends TestCase {

	public function testShouldNotDispatchWhenNoUrls() {
		$options         = $this->createMock( Options_Data::class );
		$partial_process = $this->createMock( PartialProcess::class );
		$partial_process
			->expects( $this->never() )
			->method( 'is_mobile_preload_enabled' );

		Functions\when( 'wp_doing_ajax' )
			->justReturn( false );

		$subscriber = new PartialPreloadSubscriber( $partial_process, $options );

		$subscriber->maybe_dispatch();
	}

	public function testShouldDispatchWhenUrlsAndNoMobilePreload() {
		$queue           = [];
		$urls            = [
			'https://example.org/',
			'https://example.org/test/',
		];
		$options         = $this->createMock( Options_Data::class );
		$partial_process = $this->getMockBuilder( PartialProcess::class )
		                        ->setMethods( [ 'is_mobile_preload_enabled', 'push_to_queue', 'save', 'dispatch' ] )
		                        ->getMock();
		$partial_process
			->expects( $this->once() )
			->method( 'is_mobile_preload_enabled' )
			->willReturn( false );
		$partial_process
			->expects( $this->exactly( 2 ) )
			->method( 'push_to_queue' )
			->will( $this->returnCallback( function( $item ) use ( &$queue ) {
				$queue[] = $item;
			} ) );
		$partial_process
			->expects( $this->once() )
			->method( 'save' )
			->willReturnSelf();
		$partial_process
			->expects( $this->once() )
			->method( 'dispatch' )
			->willReturn( null );

		Functions\when( 'wp_doing_ajax' )
			->justReturn( false );

		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
			return parse_url( $url, $component );
		} );

		Functions\when( 'get_rocket_cache_reject_uri' )
			->justReturn( '/foo/|/bar/|/(?:.+/)?embed/' );

		$subscriber = new PartialPreloadSubscriber( $partial_process, $options );

		$this->set_reflective_property( $urls, 'urls', $subscriber );

		$subscriber->maybe_dispatch();

		$this->assertSame( $urls, $queue );
		$this->assertCount( 2, $queue );
	}

	public function testShouldDispatchWhenUrlsAndMobilePreload() {
		$queue           = [];
		$urls            = [
			'https://example.org/',
			'https://example.org/test/',
		];
		$options         = $this->createMock( Options_Data::class );
		$partial_process = $this->getMockBuilder( PartialProcess::class )
		                        ->setMethods( [ 'is_mobile_preload_enabled', 'push_to_queue', 'save', 'dispatch' ] )
		                        ->getMock();
		$partial_process
			->expects( $this->once() )
			->method( 'is_mobile_preload_enabled' )
			->willReturn( true );
		$partial_process
			->expects( $this->exactly( 4 ) )
			->method( 'push_to_queue' )
			->will( $this->returnCallback( function( $item ) use ( &$queue ) {
				$queue[] = $item;
			} ) );
		$partial_process
			->expects( $this->once() )
			->method( 'save' )
			->willReturnSelf();
		$partial_process
			->expects( $this->once() )
			->method( 'dispatch' )
			->willReturn( null );

		Functions\when( 'wp_doing_ajax' )
			->justReturn( false );

		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
			return parse_url( $url, $component );
		} );

		Functions\when( 'get_rocket_cache_reject_uri' )
			->justReturn( '/foo/|/bar/|/(?:.+/)?embed/' );

		$subscriber = new PartialPreloadSubscriber( $partial_process, $options );

		$this->set_reflective_property( $urls, 'urls', $subscriber );

		$subscriber->maybe_dispatch();

		$expected = [
			'https://example.org/',
			[ 'url' => 'https://example.org/', 'mobile' => true ],
			'https://example.org/test/',
			[ 'url' => 'https://example.org/test/', 'mobile' => true ],
		];

		$this->assertSame( $expected, $queue );
		$this->assertCount( 4, $queue );
	}
}
