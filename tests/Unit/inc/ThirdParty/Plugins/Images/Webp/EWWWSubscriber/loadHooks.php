<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Images\Webp\EwwwSubscriber;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber;
use WP_Rocket\ThirdParty\Plugins\Images\Webp\EWWWSubscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Images\Webp\EWWWSubscriber::load_hooks
 * @group  ThirdParty
 * @group  Webp
 */
class Test_LoadHooks extends TestCase {

	public function testShouldRegisterHooksWhenCacheIsDisabledByOption() {
		$optionsData = Mockery::mock( Options_Data::class );
		$optionsData
			->shouldReceive( 'get' )
			->with( 'cache_webp' )
			->andReturn( 0 );

		$subscriber = new EWWWSubscriber( $optionsData );

		Actions\expectAdded( 'activate_ewww-image-optimizer/ewww-image-optimizer.php' )->never();

		$subscriber->load_hooks();
	}

	public function testShouldRegisterHooksWhenPluginNotAvailable() {
		$optionsData = Mockery::mock( Options_Data::class );
		$optionsData
			->shouldReceive( 'get' )
			->with( 'cache_webp' )
			->andReturn( 1 );

		$subscriber = new EWWWSubscriber( $optionsData );

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
		$optionsData
			->shouldReceive( 'get' )
			->with( 'cache_webp' )
			->andReturn( 1 );

		$subscriber = new EWWWSubscriber( $optionsData );

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
		$optionsData
			->shouldReceive( 'get' )
			->with( 'cache_webp' )
			->andReturn( 1 );

		Functions\When( 'plugin_basename' )->justReturn( 'ewww-image-optimizer/ewww-image-optimizer.php' );
		Functions\when( 'is_multisite' )->justReturn( false );

		$ewww_get_option = true;
		$subscriber      = new EWWWSubscriber( $optionsData );

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
