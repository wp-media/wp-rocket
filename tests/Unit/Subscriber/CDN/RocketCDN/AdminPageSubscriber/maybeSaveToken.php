<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber::maybe_save_token
 * @group  RocketCDN
 */
class Test_MaybeShareToken extends TestCase {
	private $page;

	public function setUp() {
		parent::setUp();

		$this->page = new AdminPageSubscriber(
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' ),
			$this->createMock( 'WP_Rocket\Admin\Options_Data' ),
			$this->createMock( 'WP_Rocket\Admin\Settings\Beacon' ),
			'views/settings/rocketcdn'
        );

        $this->mockCommonWpFunctions();
    }

    /**
     * Test should do nothing when the token value is not set
     */
    public function testShouldDoNothingWhenTokenValueNotSet() {
        $value = [];

        $this->assertSame( $value, $this->page->maybe_save_token( $value ) );
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

        $this->assertSame( [], $this->page->maybe_save_token( $value ) );
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

        $this->assertSame( [], $this->page->maybe_save_token( $value ) );
    }
}
