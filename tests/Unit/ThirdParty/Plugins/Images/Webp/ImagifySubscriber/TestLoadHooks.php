<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\ImagifySubscriber;

use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

class TestLoadHooks extends TestCase {
	/**
	 * Test Imagify_Subscriber->load_hooks() should not register hooks when separate cache is disabled via the option.
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

		$subscriber = new Imagify_Subscriber( $optionsData );

		Actions\expectAdded( 'imagify_activation' )
			->never();

		$subscriber->load_hooks();

		$this->assertTrue( true ); // Prevent "risky" warning.
	}

	/**
	 * Test Imagify_Subscriber->load_hooks() should register hooks when Imagify plugin is not available.
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

		$subscriber  = new Imagify_Subscriber( $optionsData );
		$option_name = 'imagify_settings';

		Actions\expectAdded( 'imagify_activation' )
			->once()
			->with( [ $subscriber, 'plugin_activation' ], 20 );
		Actions\expectAdded( 'imagify_deactivation' )
			->once()
			->with( [ $subscriber, 'plugin_deactivation' ], 20 );
		Actions\expectAdded( 'add_site_option_' . $option_name )
			->never();
		Actions\expectAdded( 'add_option_' . $option_name )
			->never();

		$subscriber->load_hooks();

		$this->assertTrue( true ); // Prevent "risky" warning.
	}

	/**
	 * Test Imagify_Subscriber->load_hooks() should register hooks when Imagify plugin is available.
	 */
	public function testShouldRegisterHooksWhenPluginIsAvailable() {
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

		Functions\when( 'is_multisite' )
			->justReturn( false );

		define( 'IMAGIFY_VERSION', '1.2.3-nous-irons-au-bois' );

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

		$this->assertTrue( true ); // Prevent "risky" warning.
	}
}
