<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\Subscriber;

use Mockery;
use Brain\Monkey\{Filters, Functions};
use WP_Rocket\Dependencies\RocketLazyload\{Assets, Image, Iframe};
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\Lazyload\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\Subscriber::insert_lazyload_script
 *
 * @group Media
 * @group Lazyload
 */
class Test_InsertLazyloadScript extends TestCase {
	private $assets;
	private $options;
	private $subscriber;

	public function setUp(): void {
		parent::setUp();

		$this->assets  = Mockery::mock( Assets::class );
		$this->options = Mockery::mock( Options_Data::class );

		$this->subscriber = new Subscriber(
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
	 * @dataProvider configTestData
	 */
	public function testShouldInsertLazyloadScript( $config, $expected ) {
		$this->script_debug = true;
		$this->donotrocketoptimize = isset( $config['is_not_rocket_optimize'] ) ? $config['is_not_rocket_optimize'] : false;
		$this->rest_request = isset( $config['is_rest_request'] )    ? $config['is_rest_request']    : false;

		$options = $config['options'];
		$is_admin           = isset( $config['is_admin'] )           ? $config['is_admin']           : false;
		$is_feed            = isset( $config['is_feed'] )            ? $config['is_feed']            : false;
		$is_preview         = isset( $config['is_preview'] )         ? $config['is_preview']         : false;
		$is_search          = isset( $config['is_search'] )          ? $config['is_search']          : false;
		$is_lazy_load       = isset( $config['is_lazy_load'] )       ? $config['is_lazy_load']       : true;
		$donotcachepage     = isset( $config['donotcachepage'] ) ? $config['donotcachepage'] : false;

		Functions\when( 'is_admin' )->justReturn( $is_admin );
		Functions\when( 'is_feed' )->justReturn( $is_feed );
		Functions\when( 'is_preview' )->justReturn( $is_preview );
		Functions\when( 'is_search' )->justReturn( $is_search );
		Functions\expect( 'rocket_get_constant' )
			->with( 'DONOTLAZYLOAD', 'DONOTCACHEPAGE', 'WP_ROCKET_ASSETS_JS_URL' )
			->andReturn(! $is_lazy_load, $donotcachepage, 'http://example.org/wp-content/plugins/wp-rocket/assets/' );

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
			->andReturnUsing( function () use ( $expected ) {
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

		if ( isset( $options['use_native_images'] ) ) {
			Filters\expectApplied( 'rocket_use_native_lazyload_images' )
				->atMost()
				->once()
				->andReturn( $options['use_native_images'] );
		}

		$this->assertSame(
			$this->format_the_html( $expected['unit']['result'] ),
			$this->getActualHtml()
		);
	}
}
