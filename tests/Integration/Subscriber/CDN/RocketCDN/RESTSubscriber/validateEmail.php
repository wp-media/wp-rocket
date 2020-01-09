<?php

namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\RESTSubscriber;

use Brain\Monkey;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber::validate_email
 * @group RocketCDN
 */
class Test_ValidateEmail extends TestCase {
	/**
	 * Test should return true when the provided email is the same as the one in the WPR options.
	 */
	public function testShouldReturnTrueWhenEmailIsValid() {
		update_option(
			'wp_rocket_settings',
			[
				'consumer_email' => 'dummy@wp-rocket.me',
			]
		);

		// Overload the "WP_ROCKET_EMAIL" constant to return false, forcing the code to use the options value.
		Monkey\Functions\expect( 'rocket_has_constant' )
			->once()
			->with( 'WP_ROCKET_EMAIL' )
			->andReturn( false );

		$options_api = new Options( 'wp_rocket_' );
		$options     = new Options_Data( $options_api->get( 'settings' ) );
		$rocketcdn   = new RESTSubscriber( $options_api, $options );

		$this->assertTrue( $rocketcdn->validate_email( 'dummy@wp-rocket.me' ) );
	}

	/**
	 * Test should return false when the provided email is different from the one in the WPR options
	 */
	public function testShouldReturnFalseWhenEmailIsInvalid() {
		update_option(
			'wp_rocket_settings',
			[
				'consumer_email' => 'dummy@wp-rocket.me',
				'consumer_key'   => '123456',
			]
		);

		// Overload the "WP_ROCKET_EMAIL" constant to return false, forcing the code to use the options value.
		Monkey\Functions\expect( 'rocket_has_constant' )
			->once()
			->with( 'WP_ROCKET_EMAIL' )
			->andReturn( false );

		$options_api = new Options( 'wp_rocket_' );
		$options     = new Options_Data( $options_api->get( 'settings' ) );
		$rocketcdn   = new RESTSubscriber( $options_api, $options );

		$this->assertFalse( $rocketcdn->validate_email( 'nulled@wp-rocket.me' ) );
	}
}
