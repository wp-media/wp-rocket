<?php

namespace WP_Rocket\Tests\Integration\inc\Addons\Cloudflare\Subscriber;

/**
 * @covers WPMedia\Cloudflare\Subscriber::deactivate_devmode
 * @group  DoCloudflare
 * @group  Addons
 */
class Test_DeactivateDevMode extends TestCase {

	public function testShouldNotDeactivateDevMode() {
		$this->setApiCredentialsInOptions( [ 'cloudflare_devmode' => 'off' ] );

		do_action( 'rocket_cron_deactivate_cloudflare_devmode' );

		$settings = get_option( 'wp_rocket_settings' );
		$this->assertSame( 'off', $settings['cloudflare_devmode'] );
	}

	public function testShouldDeactivateDevMode() {
		$this->setApiCredentialsInOptions( [ 'cloudflare_devmode' => 'on' ] );

		do_action( 'rocket_cron_deactivate_cloudflare_devmode' );

		$options = $this->get_reflective_property( 'options', self::$subscriber );
		$options = $options->getValue( self::$subscriber );
		$this->assertSame( 'off', $options->get( 'cloudflare_devmode' ) );
		$settings = get_option( 'wp_rocket_settings' );
		$this->assertSame( 'off', $settings['cloudflare_devmode'] );
	}
}
