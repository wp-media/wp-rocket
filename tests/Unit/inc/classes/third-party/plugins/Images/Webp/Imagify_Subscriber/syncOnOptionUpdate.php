<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\Imagify_Subscriber;

use Brain\Monkey\Actions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber::sync_on_option_update
 * @group  ThirdParty
 * @group  Webp
 */
class Test_SyncOnOptionUpdate extends TestCase {

	public function testShouldSyncWhenOptionsChange() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		Actions\expectDone( 'rocket_third_party_webp_change' )->times( 4 );

		$old_value = [ 'display_nextgen' => 1, 'display_nextgen_method' => 'a' ];
		$new_value = [ 'display_nextgen' => 0, 'display_nextgen_method' => 'a' ];

		$subscriber->sync_on_option_update( $old_value, $new_value );

		$old_value = [ 'display_nextgen' => 0, 'display_nextgen_method' => 'a' ];
		$new_value = [ 'display_nextgen' => 1, 'display_nextgen_method' => 'a' ];

		$subscriber->sync_on_option_update( $old_value, $new_value );

		$old_value = [ 'display_nextgen' => 0, 'display_nextgen_method' => 'a' ];
		$new_value = [ 'display_nextgen' => 0, 'display_nextgen_method' => 'b' ];

		$subscriber->sync_on_option_update( $old_value, $new_value );

		$old_value = [ 'display_nextgen' => 1, 'display_nextgen_method' => 'a' ];
		$new_value = [ 'display_nextgen' => 1, 'display_nextgen_method' => 'b' ];

		$subscriber->sync_on_option_update( $old_value, $new_value );
	}

	public function testShouldNotSyncWhenOptionsDontChange() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		Actions\expectDone( 'rocket_third_party_webp_change' )->never();

		$old_value = [ 'display_nextgen' => 1, 'display_nextgen_method' => 'a' ];
		$new_value = [ 'display_nextgen' => 1, 'display_nextgen_method' => 'a' ];

		$subscriber->sync_on_option_update( $old_value, $new_value );
		$subscriber->sync_on_option_update( $old_value, $new_value );
	}
}
