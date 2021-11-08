<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\PartialPreloadSubscriber;

use Mockery;
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
	private $queue = [];

	protected function tearDown() {
		parent::tearDown();
		$this->queue = [];
	}

	public function testShouldNotDispatchWhenNoUrls() {
		$options         = Mockery::mock( Options_Data::class );
		$partial_process = Mockery::mock( PartialProcess::class );
		$partial_process
			->shouldReceive( 'is_mobile_preload_enabled' )
			->never();

		Functions\when( 'wp_doing_ajax' )
			->justReturn( false );

		$subscriber = new PartialPreloadSubscriber( $partial_process, $options );

		$subscriber->maybe_dispatch();

		$this->assertEmpty( $this->queue );
	}

	public function testShouldDispatchWhenUrlsAndNoMobilePreload() {
		$urls = [
			'https://example.org/',
			'https://example.org/test/',
		];

		$this->getSubscriber( $urls, false )->maybe_dispatch();

		foreach ( $urls as $url ) {
			$this->assertContains( $url, $this->queue );
		}
		$this->assertCount( 2, $this->queue );
	}

	public function testShouldDispatchWhenUrlsAndMobilePreload() {
		$urls     = [
			'https://example.org/',
			'https://example.org/test/',
		];
		$expected = [
			'https://example.org/',
			[ 'url' => 'https://example.org/', 'mobile' => true ],
			'https://example.org/test/',
			[ 'url' => 'https://example.org/test/', 'mobile' => true ],
		];

		$this->getSubscriber( $urls, true )->maybe_dispatch();

		foreach ( $expected as $url ) {
			$this->assertContains( $url, $this->queue );
		}
		$this->assertCount( 4, $this->queue );
	}

	private function getSubscriber( array $urls, $mobile_preload_enabled ) {
		$nbr_items_in_queue = $mobile_preload_enabled ? 4 : 2;
		$this->queue        = [];
		$options            = Mockery::mock( Options_Data::class );
		$partial_process    = Mockery::mock( PartialProcess::class );
		$partial_process
			->shouldReceive( 'is_mobile_preload_enabled' )
			->once()
			->andReturn( $mobile_preload_enabled );
		$partial_process
			->shouldReceive( 'push_to_queue' )
			->times( $nbr_items_in_queue )
			->andReturnUsing( function( $item ) {
				$this->queue[] = $item;
			} );
		$partial_process
			->shouldReceive( 'save' )
			->once()
			->andReturnSelf();
		$partial_process
			->shouldReceive( 'dispatch' )
			->once()
			->andReturn( null );

		Functions\when( 'wp_doing_ajax' )
			->justReturn( false );

		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
			return parse_url( $url, $component );
		} );

		Functions\when( 'get_rocket_cache_reject_uri' )
			->justReturn( '/foo/|/bar/|/(?:.+/)?embed/' );

		$subscriber = new PartialPreloadSubscriber( $partial_process, $options );

		$this->set_reflective_property( $urls, 'urls', $subscriber );

		return $subscriber;
	}
}
