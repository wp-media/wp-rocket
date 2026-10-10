<?php
namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_get_logged_in_cookie_pattern
 * @group Functions
 * @group Options
 */
class Test_RocketGetLoggedInCookiePattern extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldNameTheLoggedInCookie( $config, $expected ) {
		$this->constants['COOKIEHASH'] = $config['hash'];

		// A case giving no name asks what the function answers where the constant is not there to
		// read, whatever the rest of the suite has defined in this process.
		if ( null === $config['cookie'] ) {
			Functions\when( 'rocket_has_constant' )->alias(
				function ( $name ) {
					return 'LOGGED_IN_COOKIE' !== $name && defined( $name );
				}
			);
		} else {
			$this->constants['LOGGED_IN_COOKIE'] = $config['cookie'];
		}

		$this->assertSame( $expected, rocket_get_logged_in_cookie_pattern() );
	}
}
