<?php

namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber::maybe_save_token
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_MaybeSaveToken extends TestCase {
    /**
     * Test should do nothing when the rocketcdn token field is not set/empty
     */
    public function testShouldDoNothingWhenRocketCDNTokenNotSet() {
        update_option('wp_rocket_settings', []);

        $this->assertFalse(get_option('rocketcdn_user_token'));

        $this->assertSame( [], get_option('wp_rocket_settings'));
    }

    /**
     * Test should add a settings error when token length is not 40
     */
    public function testShouldAddSettingsErrorWhenTokenLengthIsNot40() {
        update_option('wp_rocket_settings', [
            'rocketcdn_token' => 'test'
        ]);

        $error = [
            'setting' => 'general',
            'code'    => 'rocketcdn-token',
            'message' => 'RocketCDN token length is not 40 characters.',
            'type'    => 'error',
        ];

        $this->assertContains( $error, get_settings_errors('general') );
        $this->assertFalse(get_option('rocketcdn_user_token'));

        $this->assertSame( [], get_option('wp_rocket_settings'));
    }

    /**
     * Test should update the rocketcdn_user_token option
     */
    public function testShouldUpdateRocketCDNUserTokenOption() {
        update_option('wp_rocket_settings', [
            'rocketcdn_token' => '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b'
        ]);

        $this->assertSame( '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b', get_option('rocketcdn_user_token'));
        $this->assertSame( [], get_option('wp_rocket_settings'));
    }
}