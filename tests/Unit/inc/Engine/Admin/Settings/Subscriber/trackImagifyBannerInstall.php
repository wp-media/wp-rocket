<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Subscriber;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Dependencies\WPMedia\PluginFamily\Controller\PluginFamily;
use WP_Rocket\Engine\Admin\Settings\{Page, Subscriber};
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Settings\Subscriber::track_imagify_banner_install
 *
 * @group Admin
 * @group SettingsPage
 */
class Test_TrackImagifyBannerInstall extends TestCase {
	/**
	 * Tests that the click is tracked only with a valid nonce and capability.
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected result.
	 *
	 * @dataProvider configTestData
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldTrackOnlyWhenAuthorized( $config, $expected ) {
		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/vendors/classes/class-imagify-partner.php';

		$_REQUEST['_wpnonce'] = 'nonce';

		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\expect( 'wp_verify_nonce' )->once()->with( 'nonce', 'install_imagify_from_partner' )->andReturn( $config['nonce_valid'] );
		Functions\when( 'current_user_can' )->justReturn( $config['can_manage'] );

		if ( $expected['tracked'] ) {
			Actions\expectDone( 'rocket_mixpanel_track_event' )
				->once()
				->with(
					'Button Clicked',
					[
						'button' => 'imagify banner install',
						'source' => 'image & media',
					]
				);
		} else {
			Actions\expectDone( 'rocket_mixpanel_track_event' )->never();
		}

		( new Subscriber( Mockery::mock( Page::class ), Mockery::mock( PluginFamily::class ) ) )->track_imagify_banner_install();

		unset( $_REQUEST['_wpnonce'] );
	}
}
