<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\LazyloadSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use RocketLazyload\Assets;
use RocketLazyload\Image;
use RocketLazyload\Iframe;
use WP_Rocket\Admin\Options_Data;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Media\LazyloadSubscriber;

/**
 * @covers \WP_Rocket\Engine\Media\LazyloadSubscriber::insert_lazyload_script
 *
 * @group Media
 * @group Lazyload
 */
class Test_InsertLazyloadScript extends TestCase {
	private $assets;
	private $options;
	private $subscriber;

	public function setUp() {
		parent::setUp();

		Functions\when( 'is_admin' )->justReturn( false );
		Functions\when( 'is_feed' )->justReturn( false );
		Functions\when( 'is_preview' )->justReturn( false );
		Functions\when( 'is_search' )->justReturn( false );
		Functions\when( 'rocket_get_constant' )->justReturn( 'http://example.org/wp-content/plugins/wp-rocket/assets/' );

		$this->assets  = Mockery::mock( Assets::class );
		$this->options = Mockery::mock( Options_Data::class );

		$this->subscriber = new LazyloadSubscriber(
			$this->options,
			$this->assets,
			Mockery::mock( Image::class ),
			Mockery::mock( Iframe::class )
		);
	}

	private function getActualHtml() {
		ob_start();
		$this->subscriber->insert_lazyload_script();
		return $this->format_the_html( ob_get_clean() );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldInsertLazyloadScript( $options, $expected ) {
		foreach ( $options as $key => $value ) {
			$this->options->shouldReceive( 'get' )
				->with( $key, 0 )
				->andReturn( $value );
		}

		$this->assets->shouldReceive( 'getInlineLazyloadScript' )
			->zeroOrMoreTimes()
			->andReturn( $expected['unit']['inline_script'] );

		$this->assets->shouldReceive( 'insertLazyloadScript' )
			->zeroOrMoreTimes()
			->andReturnUsing( function() use ( $expected ) {
				echo $expected['unit']['script'];
			} );

		if ( isset( $options['threshold'] ) ) {
			Filters\expectApplied( 'rocket_lazyload_threshold' )
				->once()
				->andReturn( $options['threshold'] );
		}

		if ( isset( $options['use_native'] ) ) {
			Filters\expectApplied( 'rocket_use_native_lazyload' )
				->once()
				->andReturn( $options['use_native'] );
		}

		if ( isset( $options['polyfill'] ) ) {
			Filters\expectApplied( 'rocket_lazyload_polyfill' )
				->once()
				->andReturn( $options['polyfill'] );
		}

		$this->assertSame(
			$this->format_the_html( $expected['unit']['result'] ),
			$this->getActualHtml()
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'insertLazyloadScript' );
	}
}
