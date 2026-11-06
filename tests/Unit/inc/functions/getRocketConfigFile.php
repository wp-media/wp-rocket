<?php
namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::get_rocket_config_file
 * @group Functions
 * @group Files
 */
class Test_GetRocketConfigFile extends TestCase {
	/**
	 * Answers everything the writer asks the site, so only the cookie name is under test. The list of
	 * rejected cookies is not among them: it comes from the function this one has to agree with.
	 *
	 * @return void
	 */
	private function stub_the_site( array $rejected, $logged_user ) {
		Functions\when( 'get_option' )->alias(
			function ( $name ) use ( $rejected, $logged_user ) {
				return WP_ROCKET_SLUG === $name
					? [
						'cache_logged_user'    => $logged_user,
						'cache_reject_cookies' => $rejected,
					]
					: '';
			}
		);
		Functions\when( 'get_rocket_option' )->alias(
			function ( $name, $default = null ) use ( $rejected, $logged_user ) {
				if ( 'cache_logged_user' === $name ) {
					return $logged_user;
				}

				return 'cache_reject_cookies' === $name ? $rejected : $default;
			}
		);
		Functions\when( 'apply_filters' )->returnArg( 2 );
		Functions\when( 'get_rocket_cache_query_string' )->justReturn( [] );
		Functions\when( 'get_rocket_cache_reject_ua' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_reject_uri' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_mandatory_cookies' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_dynamic_cookies' )->justReturn( [] );
		Functions\when( 'rocket_get_ignored_parameters' )->justReturn( [] );
		Functions\when( 'get_rocket_i18n_subdomains' )->justReturn( [] );
		Functions\when( 'rocket_get_home_url' )->justReturn( 'http://example.org' );
		Functions\when( 'get_rocket_parse_url' )->justReturn(
			[
				'host' => 'example.org',
				'path' => '',
			]
		);
		Functions\when( 'wp_slash' )->returnArg();
	}

	/**
	 * @dataProvider configTestData
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldWriteTheRejectedCookies( $config, $expected ) {
		// The writer reads all four as constants, so the test gives them. One process per data set,
		// hence no guard: a data set that lost its own process has to say so.
		define( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );
		define( 'WP_ROCKET_CONFIG_PATH', 'vfs://public/wp-content/wp-rocket-config/' );
		define( 'COOKIEHASH', $config['hash'] );
		define( 'LOGGED_IN_COOKIE', $config['cookie'] );

		$this->stub_the_site( $config['rejected'], $config['logged_user'] );

		list( , $buffer ) = get_rocket_config_file();

		$this->assertStringContainsString( "\$rocket_cache_reject_cookies = '{$expected}';", $buffer );
		$this->assertStringContainsString( "\$rocket_cookie_hash = '{$config['hash']}';", $buffer );
	}
}
