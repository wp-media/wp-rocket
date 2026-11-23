<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering ::rocket_get_external_url
 *
 * @group Functions
 * @group Formatting
 */
class Test_RocketGetExternalUrl extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		if ( ! defined( 'WP_ROCKET_WEB_MAIN' ) ) {
			define( 'WP_ROCKET_WEB_MAIN', 'https://wp-rocket.me/' );
		}

		Functions\when( 'add_query_arg' )->alias(
			function ( $args, $url ) {
				return $url . '?' . http_build_query( $args );
			}
		);
	}

	/**
	 * @dataProvider providerLocales
	 */
	public function testContactIsTheSamePageForEveryLocale( $locale ) {
		Functions\when( 'get_user_locale' )->justReturn( $locale );

		$this->assertSame(
			'https://wp-rocket.me/contact/?utm_source=wp_plugin&utm_medium=wp_rocket',
			rocket_get_external_url( 'contact', [ 'utm_source' => 'wp_plugin', 'utm_medium' => 'wp_rocket' ] )
		);
	}

	public function testContactWithoutQueryArgs() {
		$this->assertSame( 'https://wp-rocket.me/contact/', rocket_get_external_url( 'contact' ) );
	}

	public function testSupportKeepsItsLocalizedPaths() {
		Functions\when( 'get_user_locale' )->justReturn( 'fr_FR' );

		$this->assertSame( 'https://wp-rocket.me/fr/support/', rocket_get_external_url( 'support' ) );
	}

	public function providerLocales() {
		return [
			'default' => [ 'en_US' ],
			'french'  => [ 'fr_FR' ],
			'italian' => [ 'it_IT' ],
			'german'  => [ 'de_DE' ],
			'spanish' => [ 'es_ES' ],
		];
	}
}
