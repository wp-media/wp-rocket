<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\Imagify_Subscriber;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber::sync_on_option_delete
 * @group  ThirdParty
 * @group  Webp
 */
class Test_SyncOnOptionDelete extends TestCase {

	public function testShouldSyncWhenServingWebp() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber = new Imagify_Subscriber( $optionsData );

		Actions\expectDone( 'rocket_third_party_webp_change' )->once();
		Functions\when( 'get_imagify_option' )->justReturn( true );

		$subscriber->store_option_value_before_delete( 'imagify_settings' );
		$subscriber->sync_on_option_delete( 'imagify_settings' );
	}

	public function testShouldNotSyncWhenNotServingWebp() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber = new Imagify_Subscriber( $optionsData );

		Actions\expectDone( 'rocket_third_party_webp_change' )->never();
		Functions\when( 'get_imagify_option' )->justReturn( false );

		$subscriber->store_option_value_before_delete( 'imagify_settings' );
		$subscriber->sync_on_option_delete( 'imagify_settings' );
	}
}
