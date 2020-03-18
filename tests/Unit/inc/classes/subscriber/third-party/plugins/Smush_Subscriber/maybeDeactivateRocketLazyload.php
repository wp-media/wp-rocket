<?php
namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Smush_Subscriber;

//use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Subscriber\Third_Party\Plugins\Smush_Subscriber;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Smush_Subscriber::maybe_deactivate_rocket_lazyload
 * @group ThirdParty
 * @group Smush
 */
class Test_MaybeDeactivateRocketLazyload extends SmushSubscriberTestCase {
	private $subscriber;

	public function testShouldNotDisableWPRocketLazyLoad() {
		// Smush not enabled, WPR enabled.
		$this->mock_is_smush_lazyload_enabled(
			false,
			[]
		);

		$this->setSubscriber( true, true, false, false );

		$this->subscriber->maybe_deactivate_rocket_lazyload();

		// Smush enabled, WPR not enabled.
		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'jpeg'   => true,
				'iframe' => true,
			]
		);

		$this->setSubscriber( false, false, false, false );

		$this->subscriber->maybe_deactivate_rocket_lazyload();
	}

	public function testShouldDisableWPRocketLazyLoadForImagesWhenSmushLazyLoadForImagesIsEnabled() {
		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'jpeg'   => true,
				'iframe' => false,
			]
		);

		$this->setSubscriber( true, true, true, false );

		$this->subscriber->maybe_deactivate_rocket_lazyload();
	}

	public function testShouldDisableWPRocketLazyLoadForIframesWhenSmushLazyLoadForIframesIsEnabled() {
		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'jpeg'   => false,
				'iframe' => true,
			]
		);

		$this->setSubscriber( true, true, false, true );

		$this->subscriber->maybe_deactivate_rocket_lazyload();
	}

	public function testShouldDisableWPRocketBothLazyLoadWhenSmushLazyLoadForImagesAndIframesIsEnabled() {
		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'jpeg'   => true,
				'iframe' => true,
			]
		);

		$this->setSubscriber( true, true, true, true );

		$this->subscriber->maybe_deactivate_rocket_lazyload();
	}

	private function setSubscriber( $getLazyload, $getLazyloadIframes, $setLazyload, $setLazyloadIframes ) {
		$options      = Mockery::mock( 'WP_Rocket\Admin\Options' );
		$options_data = Mockery::mock( 'WP_Rocket\Admin\Options_Data' );
		$options_data
			->shouldReceive( 'get' )
			->andReturnUsing(
				function ( $setting, $default = '' ) use ( $getLazyload, $getLazyloadIframes ) {
					if ( 'lazyload' === $setting ) {
						return $getLazyload;
					}
					if ( 'lazyload_iframes' === $setting ) {
						return $getLazyloadIframes;
					}
					return $default;
				}
			);

		$setLazyload        = $setLazyload ? 1 : 0;
		$setLazyloadIframes = $setLazyloadIframes ? 1 : 0;
		$setCount           = min( $setLazyload + $setLazyloadIframes, 1 );
		$options_data
			->shouldReceive( 'set' )
			->times( $setLazyload )
			->with( 'lazyload', 0 );

		$options_data
			->shouldReceive( 'set' )
			->times( $setLazyloadIframes )
			->with( 'lazyload_iframes', 0 );

		$options_data
			->shouldReceive( 'get_options' )
			->times( $setCount );

		$options
			->shouldReceive( 'set' )
			->times( $setCount );

		$this->subscriber = new Smush_Subscriber( $options, $options_data );
	}
}
