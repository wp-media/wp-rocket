<?php
namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\RESTSubscriber;

use Brain\Monkey;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber::validate_key
 * @group RocketCDN
 */
class Test_ValidateKey extends TestCase {
    /**
	 * Test should return true when the provided key is the same as the one in the WPR options.
	 */
    public function testShouldReturnTrueWhenKeyIsValid() {
        update_option(
            'wp_rocket_settings',
            [
                'consumer_key' => '0123456',
            ]
        );

	    // Overload the "WP_ROCKET_KEY" constant to return false, forcing the code to use the options value.
	    Monkey\Functions\expect( 'rocket_has_constant' )
		    ->once()
		    ->with( 'WP_ROCKET_KEY' )
		    ->andReturn( false );

        $options_api = new Options( 'wp_rocket_' );
        $options     = new Options_Data( $options_api->get( 'settings' ) );
        $rocketcdn   = new RESTSubscriber( $options_api, $options );

        $this->assertTrue( $rocketcdn->validate_key( '0123456' ) );
    }

    /**
	 * Test should return false when the provided key is different from the one in the WPR options
	 */
    public function testShouldReturnFalseWhenKeylIsInvalid() {
        update_option(
            'wp_rocket_settings',
            [
                'consumer_key' => '0123456',
            ]
        );

	    // Overload the "WP_ROCKET_KEY" constant to return false, forcing the code to use the options value.
	    Monkey\Functions\expect( 'rocket_has_constant' )
		    ->once()
		    ->with( 'WP_ROCKET_KEY' )
		    ->andReturn( false );

        $options_api = new Options( 'wp_rocket_' );
        $options     = new Options_Data( $options_api->get( 'settings' ) );
        $rocketcdn   = new RESTSubscriber( $options_api, $options );

        $this->assertFalse( $rocketcdn->validate_key( '000000' ) );
    }
}
