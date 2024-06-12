<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\EwwwSubscriber;

use Brain\Monkey\{Actions, Filters, Functions};
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber::load_hooks
 * @group  ThirdParty
 * @group  Webp
 */
class Test_LoadHooks extends TestCase {

	public function testShouldRegisterHooksWhenCacheIsDisabledByOption() {
		$optionsData = Mockery::mock( Options_Data::class );
		$optionsData->expects()->get( 'cache_webp' )
			->once()
			->andReturns( 0 );

		$subscriber = new EWWW_Subscriber( $optionsData );

		Actions\expectAdded( 'activate_ewww-image-optimizer/ewww-image-optimizer.php' )->never();

		$subscriber->load_hooks();
	}

	public function testShouldRegisterHooksWhenPluginNotAvailable() {
		$optionsData = Mockery::mock( Options_Data::class );
		$optionsData->expects()->get( 'cache_webp' )
			->once()
			->andReturn( 1 );

		$subscriber = new EWWW_Subscriber( $optionsData );

		Actions\expectAdded( 'activate_ewww-image-optimizer/ewww-image-optimizer.php' )
			->once()
			->with( [ $subscriber, 'plugin_activation' ], 20 );
		Actions\expectAdded( 'deactivate_ewww-image-optimizer/ewww-image-optimizer.php' )
			->once()
			->with( [ $subscriber, 'plugin_deactivation' ], 20 );
		Actions\expectAdded( 'rocket_cdn_cnames' )
			->never();

		Functions\When( 'plugin_basename' )->justReturn( 'ewww-image-optimizer/ewww-image-optimizer.php' );
		Functions\When( 'is_multisite' )->justReturn( false );

		$subscriber->load_hooks();

		$this->assertTrue( true ); // Prevent "risky" warning.
	}

	public function testShouldCallCallbacksWhenDidAction() {
		$optionsData = Mockery::mock( Options_Data::class );
		$optionsData->expects()->get( 'cache_webp' )
			->once()
			->andReturn( 1 );

		$subscriber = new EWWW_Subscriber( $optionsData );

		Functions\When( 'plugin_basename' )->justReturn( 'ewww-image-optimizer/ewww-image-optimizer.php' );
		Functions\When( 'is_multisite' )->justReturn( false );
		Functions\when( 'ewww_image_optimizer_get_option' )->justReturn( true );

		do_action( 'activate_ewww-image-optimizer/ewww-image-optimizer.php' );
		do_action( 'deactivate_ewww-image-optimizer/ewww-image-optimizer.php' );

		$subscriber->load_hooks();
	}

	public function testShouldRegisterHooksWhenPluginIsAvailable() {
		global $ewww_get_option;

		$optionsData = Mockery::mock( Options_Data::class );
		$optionsData->expects()->get( 'cache_webp' )
			->once()
			->andReturn( 1 );

		Functions\When( 'plugin_basename' )->justReturn( 'ewww-image-optimizer/ewww-image-optimizer.php' );
		Functions\when( 'is_multisite' )->justReturn( false );

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
	}
}
