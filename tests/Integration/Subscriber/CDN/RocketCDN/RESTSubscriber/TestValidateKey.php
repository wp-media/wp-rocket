<?php
namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\RESTSubscriber;

use Brain\Monkey;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rest_Request;

/**
 * @coversDefaultClass \WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber
 * @group RocketCDN
 */
class TestValidateKey extends TestCase {
    /**
	 * @covers ::validate_key
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

        $request     = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/enable' );
        $options_api = new Options( 'wp_rocket_' );
        $options     = new Options_Data( $options_api->get( 'settings' ) );
        $rocketcdn   = new RESTSubscriber( $options_api, $options );

        $this->assertTrue( $rocketcdn->validate_key( '0123456' ) );
    }

    /**
	 * @covers ::validate_key
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

	    $request     = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/enable' );
        $options_api = new Options( 'wp_rocket_' );
        $options     = new Options_Data( $options_api->get( 'settings' ) );
        $rocketcdn   = new RESTSubscriber( $options_api, $options );

        $this->assertFalse( $rocketcdn->validate_key( '000000' ) );
    }
}
