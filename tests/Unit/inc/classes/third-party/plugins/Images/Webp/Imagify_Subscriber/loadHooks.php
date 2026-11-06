<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\Imagify_Subscriber;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber::load_hooks
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

		$subscriber = new Imagify_Subscriber( $optionsData );

		Actions\expectAdded( 'imagify_activation' )->never();

		$subscriber->load_hooks();
	}

	public function testShouldRegisterHooksWhenPluginNotAvailable() {
		$optionsData = Mockery::mock( Options_Data::class );
		$optionsData
			->shouldReceive( 'get' )
			->with( 'cache_webp' )
			->andReturn( 1 );

		$subscriber  = new Imagify_Subscriber( $optionsData );
		$option_name = 'imagify_settings';

		Functions\expect( 'rocket_has_constant' )
			->once()
			->with( 'IMAGIFY_VERSION' )
			->andReturn( false );
		Actions\expectAdded( 'imagify_activation' )
			->once()
			->with( [ $subscriber, 'plugin_activation' ], 20 );
		Actions\expectAdded( 'imagify_deactivation' )
			->once()
			->with( [ $subscriber, 'plugin_deactivation' ], 20 );
		Actions\expectAdded( 'add_site_option_' . $option_name )->never();
		Actions\expectAdded( 'add_option_' . $option_name )->never();

		$subscriber->load_hooks();
	}

	public function testShouldRegisterHooksWhenPluginIsAvailable() {
		$optionsData = Mockery::mock( Options_Data::class );
		$optionsData
			->shouldReceive( 'get' )
			->with( 'cache_webp' )
			->andReturn( 1 );

		Functions\when( 'is_multisite' )->justReturn( false );

		Functions\expect( 'rocket_has_constant' )
			->once()
			->with( 'IMAGIFY_VERSION' )
			->andReturn( true );

		$subscriber  = new Imagify_Subscriber( $optionsData );
		$option_name = 'imagify_settings';

		Filters\expectAdded( 'add_option_' . $option_name )
			->once()
			->with( [ $subscriber, 'sync_on_option_add' ], 10, 2 );
		Filters\expectAdded( 'update_option_' . $option_name )
			->once()
			->with( [ $subscriber, 'sync_on_option_update' ], 10, 2 );
		Filters\expectAdded( 'delete_option' )
			->once()
			->with( [ $subscriber, 'store_option_value_before_delete' ] );
		Filters\expectAdded( 'delete_option_' . $option_name )
			->once()
			->with( [ $subscriber, 'sync_on_option_delete' ] );

		$subscriber->load_hooks();
	}
}
