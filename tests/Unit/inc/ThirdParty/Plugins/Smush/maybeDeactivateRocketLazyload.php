<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Smush;

use Mockery;
use WP_Rocket\ThirdParty\Plugins\Smush;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Smush::maybe_deactivate_rocket_lazyload
 * @group ThirdParty
 * @group Smush
 */
class Test_MaybeDeactivateRocketLazyload extends SmushSubscriberTestCase {
	private $subscriber;

	/**
	 * @dataProvider addDataProviderThatShouldNotDisableWPRocketLazyLoad
	 */
	public function testShouldNotDisableWPRocketLazyLoad( $lazyload_enabled, array $lazyload_formats, array $rocketSettings ) {

		$this->mock_is_smush_lazyload_enabled( $lazyload_enabled, $lazyload_formats );

		call_user_func_array( [ $this, 'setAssertsAndSubscriber' ], $rocketSettings );

		$this->subscriber->maybe_deactivate_rocket_lazyload();
	}

	/**
	 * @dataProvider addDataProviderThatShouldDisableWPRocketLazyLoad
	 */
	public function testShouldDisableWPRocketLazyLoad( $lazyload_enabled, array $lazyload_formats, array $rocketSettings ) {
		$this->mock_is_smush_lazyload_enabled( $lazyload_enabled, $lazyload_formats );

		call_user_func_array( [ $this, 'setAssertsAndSubscriber' ], $rocketSettings );

		$this->subscriber->maybe_deactivate_rocket_lazyload();
	}

	private function setAssertsAndSubscriber( $getLazyload, $getLazyloadIframes, $setLazyload, $setLazyloadIframes ) {
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

		$this->subscriber = new Smush( $options, $options_data );
	}

	public function addDataProviderThatShouldNotDisableWPRocketLazyLoad() {
		return $this->getTestData( __DIR__, 'maybeDeactivateRocketLazyloadNotDisable' );
	}

	public function addDataProviderThatShouldDisableWPRocketLazyLoad() {
		return $this->getTestData( __DIR__, 'maybeDeactivateRocketLazyloadDisable' );
	}
}
