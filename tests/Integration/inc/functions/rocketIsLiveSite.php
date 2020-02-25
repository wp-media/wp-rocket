<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers ::rocket_is_live_site
 * @group Functions
 * @group API
 */
class Test_RocketIsLiveSite extends TestCase {

	public function testShouldReturnTrueWhenWPROCKETDEBUG() {
		Functions\when( 'rocket_get_constant' )->justReturn( true );

		$this->assertTrue( rocket_is_live_site() );
	}

	public function testShouldReturnFalseWhenNoHost() {
		Functions\when( 'rocket_get_constant' )->justReturn( false );

		$callback = function() {
			return 'http://';
		};

		add_filter( 'home_url', $callback );

		$this->assertFalse( rocket_is_live_site() );

		remove_filter( 'home_url', $callback );
	}

	public function testShouldReturnFalseWhenLocalOrStaging() {
		Functions\when( 'rocket_get_constant' )->justReturn( false );

		$urls   = $this->getLocalStagingSites();
		$urls[] = 'example.dev';
		$urls[] = 'example.dev.cc';
		foreach ( $urls as $domain ) {
			$callback = function() use ( $domain ) {
				return 'http://' . $domain;
			};

			add_filter( 'home_url', $callback );
			$this->assertFalse( rocket_is_live_site() );
			remove_filter( 'home_url', $callback );
		}
	}

	public function testShouldReturnTrueWhenDevTLDIsLiveSite() {
		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_DEBUG' )->andReturn( false );

		add_filter( 'rocket_tld_is_live_prod_site', [ $this, 'return_true' ] );
		foreach ( [ 'example.dev', 'example.dev.css' ] as $domain ) {
			$callback = function() use ( $domain ) {
				return 'http://' . $domain;
			};

			add_filter( 'home_url', $callback );
			$this->assertTrue( rocket_is_live_site() );
			remove_filter( 'home_url', $callback );
		}
		remove_filter( 'rocket_tld_is_live_prod_site', [ $this, 'return_true' ] );
	}

	public function testShouldReturnTrueWhenLiveSite() {
		Functions\when( 'rocket_get_constant' )->justReturn( false );

		$this->assertTrue( rocket_is_live_site() );
	}

	private function getLocalStagingSites() {
		return [
			'127.0.0.1',
			'localhost',
			'example.localhost',
			'example.local',
			'example.test',
			'example.docksal',
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
			'example.myftpupload.com',
		];
	}
}
