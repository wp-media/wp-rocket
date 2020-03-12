<?php
namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Smush_Subscriber;

use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Smush_Subscriber::maybe_deactivate_rocket_lazyload
 * @group ThirdParty
 * @group Smush
 */
class Test_MaybeDeactivateRocketLazyload extends SmushSubscriberTestCase {

	public function testShouldNotDisableWPRocketLazyLoad() {
		Functions\expect( 'update_rocket_option' )->never();

		// Smush not enabled, WPR enabled.
		$this->mock_is_smush_lazyload_enabled(
			false,
			[]
		);

		Functions\when( 'get_rocket_option' )
			->alias(
				function( $option ) {
					switch ( $option ) {
						case 'lazyload':
						case 'lazyload_iframes':
							return true;
					}
					return false;
				}
			);

		$this->subscriber->maybe_deactivate_rocket_lazyload();

		// Smush enabled, WPR not enabled.
		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'jpeg'   => true,
				'iframe' => true,
			]
		);

		Functions\when( 'get_rocket_option' )->justReturn( false );

		$this->subscriber->maybe_deactivate_rocket_lazyload();
	}

	public function testShouldDisableWPRocketLazyLoadForImagesWhenSmushLazyLoadForImagesIsEnabled() {
		Functions\when( 'get_rocket_option' )
			->alias(
				function( $option ) {
					switch ( $option ) {
						case 'lazyload':
						case 'lazyload_iframes':
							return true;
					}
					return false;
				}
			);
		Functions\expect( 'update_rocket_option' )
			->once()
			->with( 'lazyload', 0 )
			->never()
			->with( 'lazyload_iframes', 0 );

		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'jpeg'   => true,
				'iframe' => false,
			]
		);

		$this->subscriber->maybe_deactivate_rocket_lazyload();
	}

	public function testShouldDisableWPRocketLazyLoadForIframesWhenSmushLazyLoadForIframesIsEnabled() {
		Functions\when( 'get_rocket_option' )
			->alias(
				function( $option ) {
					switch ( $option ) {
						case 'lazyload':
						case 'lazyload_iframes':
							return true;
					}
					return false;
				}
			);
		Functions\expect( 'update_rocket_option' )
			->never()
			->with( 'lazyload', 0 )
			->once()
			->with( 'lazyload_iframes', 0 );

		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'jpeg'   => false,
				'iframe' => true,
			]
		);

		$this->subscriber->maybe_deactivate_rocket_lazyload();
	}

	public function testShouldDisableWPRocketBothLazyLoadWhenSmushLazyLoadForImagesAndIframesIsEnabled() {
		Functions\when( 'get_rocket_option' )
			->alias(
				function( $option ) {
					switch ( $option ) {
						case 'lazyload':
						case 'lazyload_iframes':
							return true;
					}
					return false;
				}
			);
		Functions\expect( 'update_rocket_option' )
			->once()
			->with( 'lazyload', 0 )
			->once()
			->with( 'lazyload_iframes', 0 );

		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'jpeg'   => true,
				'iframe' => true,
			]
		);

		$this->subscriber->maybe_deactivate_rocket_lazyload();
	}
}
