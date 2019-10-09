<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\EwwwSubscriber {

	use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber;
	use WP_Rocket\Tests\Unit\TestCase;
	use Brain\Monkey\Actions;
	use Brain\Monkey\Filters;
	use Brain\Monkey\Functions;

	class TestLoadHooks extends TestCase {
		/**
		 * Test EWWW_Subscriber->load_hooks() should not register hooks when separate cache is disabled via the option.
		 */
		public function testShouldRegisterHooksWhenCacheIsDisabledByOption() {
			$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
			$optionsData
				->method( 'get' )
				->will(
					$this->returnValueMap(
						[
							[ 'cache_webp', '', 0 ],
						]
					)
				);

			$subscriber = new EWWW_Subscriber( $optionsData );

			Actions\expectAdded( 'activate_ewww-image-optimizer/ewww-image-optimizer.php' )
				->never();

			$subscriber->load_hooks();

			$this->assertTrue( true ); // Prevent "risky" warning.
		}

		/**
		 * Test EWWW_Subscriber->load_hooks() should register hooks when EWWW plugin is not available.
		 */
		public function testShouldRegisterHooksWhenPluginNotAvailable() {
			$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
			$optionsData
				->method( 'get' )
				->will(
					$this->returnValueMap(
						[
							[ 'cache_webp', '', 1 ],
						]
					)
				);

			$subscriber = new EWWW_Subscriber( $optionsData );

			Actions\expectAdded( 'activate_ewww-image-optimizer/ewww-image-optimizer.php' )
				->once()
				->with( [ $subscriber, 'plugin_activation' ], 20 );
			Actions\expectAdded( 'deactivate_ewww-image-optimizer/ewww-image-optimizer.php' )
				->once()
				->with( [ $subscriber, 'plugin_deactivation' ], 20 );
			Actions\expectAdded( 'rocket_cdn_cnames' )
				->never();

			Functions\When( 'plugin_basename')->justReturn('ewww-image-optimizer/ewww-image-optimizer.php');
			Functions\When( 'is_multisite')->justReturn( false );

			$subscriber->load_hooks();

			$this->assertTrue( true ); // Prevent "risky" warning.
		}

		/**
		 * Test EWWW_Subscriber->load_hooks() should call plugin_activation() and plugin_deactivation() when did action.
		 */
		public function testShouldCallCallbacksWhenDidAction() {
			$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
			$optionsData
				->method( 'get' )
				->will(
					$this->returnValueMap(
						[
							[ 'cache_webp', '', 1 ],
						]
					)
				);

			$subscriber = $this->getMockBuilder( EWWW_Subscriber::class )
				->setConstructorArgs( [ $optionsData ] )
				->setMethods( [ 'plugin_activation', 'plugin_deactivation' ] )
				->getMock();
			$subscriber
				->expects( $this->once() )
				->method( 'plugin_activation' );
			$subscriber
				->expects( $this->once() )
				->method( 'plugin_deactivation' );
			
			Functions\When( 'plugin_basename')->justReturn('ewww-image-optimizer/ewww-image-optimizer.php');
			Functions\When( 'is_multisite')->justReturn( false );

			do_action( 'activate_ewww-image-optimizer/ewww-image-optimizer.php' );
			do_action( 'deactivate_ewww-image-optimizer/ewww-image-optimizer.php' );

			$subscriber->load_hooks();
		}

		/**
		 * Test EWWW_Subscriber->load_hooks() should register hooks when EWWW plugin is available.
		 */
		public function testShouldRegisterHooksWhenPluginIsAvailable() {
			global $ewww_get_option;

			$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
			$optionsData
				->method( 'get' )
				->will(
					$this->returnValueMap(
						[
							[ 'cache_webp', '', 1 ],
						]
					)
				);

			Functions\When( 'plugin_basename')->justReturn('ewww-image-optimizer/ewww-image-optimizer.php');
			Functions\when( 'is_multisite' )
				->justReturn( false );

			$ewww_get_option = true;
			$subscriber      = new EWWW_Subscriber( $optionsData );

			Filters\expectAdded( 'rocket_cdn_cnames' )
				->once()
				->with( [ $subscriber, 'maybe_remove_images_cnames' ], 1000, 2 );
			Filters\expectAdded( 'rocket_allow_cdn_images' )
				->once()
				->with( [ $subscriber, 'maybe_remove_images_from_cdn_dropdown' ] );

			$option_names = [
				'ewww_image_optimizer_exactdn',
				'ewww_image_optimizer_webp_for_cdn',
			];

			foreach ( $option_names as $option_name ) {
				Filters\expectAdded( 'add_option_' . $option_name )
					->once()
					->with( [ $subscriber, 'trigger_webp_change' ] );
				Filters\expectAdded( 'update_option_' . $option_name )
					->once()
					->with( [ $subscriber, 'trigger_webp_change' ] );
				Filters\expectAdded( 'delete_option_' . $option_name )
					->once()
					->with( [ $subscriber, 'trigger_webp_change' ] );
			}

			$subscriber->load_hooks();

			$this->assertTrue( true ); // Prevent "risky" warning.
		}
	}

}
