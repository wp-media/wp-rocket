<?php
namespace WP_Rocket\Tests\Unit\Functions\Options;

use WPMedia\PHPUnit\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers rocket_is_live_site()
 * @group Functions
 * @group API
 */
class Test_RocketIsLiveSite extends TestCase {
	public function setUp() {
		parent::setUp();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/api.php';
	}

	public function testShouldReturnTrueWhenWPROCKETDEBUG() {
		Functions\when( 'rocket_get_constant' )->justReturn( true );

		$this->assertTrue( rocket_is_live_site() );
	}

	public function testShouldReturnFalseWhenNoHost() {
		Functions\when( 'rocket_get_constant' )->justReturn( false );
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\when( 'wp_parse_url' )->justReturn( null );

		$this->assertFalse( rocket_is_live_site() );
	}

	public function testShouldReturnFalseWhenLocalOrStaging() {
		Functions\when( 'rocket_get_constant' )->justReturn( false );
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );

		$local_staging = [
			'127.0.0.1',
			'localhost',
			'example.localhost',
			'example.local',
			'example.dev',
			'example.test',
			'example.docksal',
			'example.dev.cc',
			'example.lndo.site',
			'example.wpengine.com',
			'example.pantheonsite.io',
			'example.flywheelsites.com',
			'example.flywheelstaging.com',
			'example.kinsta.com',
			'example.kinsta.cloud',
			'example.cloudwaysapps.com',
			'example.azurewebsites.net',
			'example.wpserveur.net',
			'example-liquidwebsites.com',
			'example.myftpupload.com'
		];

		foreach ( $local_staging as $domain ) {
			Functions\when( 'wp_parse_url' )->justReturn( $domain );

			$this->assertFalse( rocket_is_live_site() );
		}
	}

	public function testShouldReturnTrueWhenLiveSite() {
		Functions\when( 'rocket_get_constant' )->justReturn( false );
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\when( 'wp_parse_url' )->justReturn( 'example.org' );

		$this->assertTrue( rocket_is_live_site() );
	}
}
