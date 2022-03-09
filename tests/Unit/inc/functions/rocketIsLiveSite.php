<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::rocket_is_live_site
 * @group  Functions
 * @group  API
 */
class Test_RocketIsLiveSite extends TestCase {

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

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

		$urls   = $this->getLocalStagingSites();
		foreach ( $urls as $domain ) {
			Functions\when( 'wp_parse_url' )->justReturn( $domain );

			$this->assertFalse( rocket_is_live_site() );
		}
	}

	public function testShouldReturnTrueWhenLiveSite() {
		Functions\when( 'rocket_get_constant' )->justReturn( false );

		$live_tlds = [
			'.org',
			'.org.uk',
			'.com',
			'.co.uk',
			'.dev',
			'.me',
			'.me.uk',
		];
		foreach ( $live_tlds as $tld ) {
			Functions\expect( 'home_url' )->once()->andReturn( "http://example{$tld}" );
			Functions\expect( 'wp_parse_url' )->once()->andReturn( "example{$tld}" );

			$this->assertTrue( rocket_is_live_site() );
		}
	}

	private function getLocalStagingSites() {
		return [
			'127.0.0.1',
			'localhost',
			'example.localhost',
			'example.local',
			'example.test',
			'example.dev.cc',
			'example.docksal',
			'example.docksal.site',
			'example.lndo.site',
			'example.wpengine.com',
			'example.wpenginepowered.com',
			'example.pantheonsite.io',
			'example.flywheelsites.com',
			'example.flywheelstaging.com',
			'example.kinsta.com',
			'example.kinsta.cloud',
			'example.cloudwaysapps.com',
			'example.azurewebsites.net',
			'example.wpserveur.net',
			'example-liquidwebsites.com',
			'example.myftpupload.com',
			'example.wpstage.net',
			'example.wpsc.site',
			'example.runcloud.link',
			'example.onrocket.site',
			'example.bigscoots-staging.com',
			'example.singlestaging.com',
		];
	}
}
