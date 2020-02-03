<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber::maybe_save_token
 * @group  RocketCDN
 */
class Test_MaybeShareToken extends TestCase {
	private $data_manager;

	public function setUp() {
		parent::setUp();

		$this->data_manager = new DataManagerSubscriber(
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' ),
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\CDNOptionsManager' )
        );

        $this->mockCommonWpFunctions();
    }

    /**
     * Test should do nothing when the token value is not set
     */
    public function testShouldDoNothingWhenTokenValueNotSet() {
        $value = [];

        $this->assertSame( $value, $this->data_manager->maybe_save_token( $value ) );
    }

    /**
     * Test should add a settings error when the token length is not 40
     */
    public function testShouldAddErrorWhenTokenLengthIsNot40() {
        Functions\when('sanitize_text_field')->returnArg();
        Functions\expect('add_settings_error')
            ->once()
            ->with('general', 'rocketcdn-token', 'RocketCDN token length is not 40 characters.', 'error');

        $value = [
            'rocketcdn_token' => 'test',
        ];

        $this->assertSame( [], $this->data_manager->maybe_save_token( $value ) );
    }

    /**
     * Test should update the value of rocketcdn_user_token option
     */
    public function testShouldUpdateRocketCDNUserTokenOption() {
        Functions\when('sanitize_text_field')->returnArg();
        Functions\expect('update_option')
            ->once()
            ->with('rocketcdn_user_token', '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b' );

        $value = [
            'rocketcdn_token' => '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b',
        ];

        $this->assertSame( [], $this->data_manager->maybe_save_token( $value ) );
    }
}
