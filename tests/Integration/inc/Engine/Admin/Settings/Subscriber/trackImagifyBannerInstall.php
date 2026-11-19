<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Settings\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Settings\Subscriber::track_imagify_banner_install
 *
 * @group AdminOnly
 * @group SettingsPage
 */
class Test_TrackImagifyBannerInstall extends TestCase {
	public function testCallbackIsRegisteredBeforeImagifyPartner() {
		$hook = 'admin_post_install_imagify_from_partner_wp-rocket';

		global $wp_filter;
		$this->assertArrayHasKey( $hook, $wp_filter );
		$this->assertArrayHasKey( 5, $wp_filter[ $hook ]->callbacks );

		$callback = current( $wp_filter[ $hook ]->callbacks[5] );
		$this->assertSame( 'track_imagify_banner_install', $callback['function'][1] );
	}
}
