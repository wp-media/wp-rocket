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

		foreach ( $this->getLocalStagingSites() as $domain ) {
			$callback = function() use ( $domain ) {
				return 'http://' . $domain;
			};

			add_filter( 'home_url', $callback );
			$this->assertFalse( rocket_is_live_site() );
			remove_filter( 'home_url', $callback );
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
			$callback = function() use ( $tld ) {
				return "http://example{$tld}";
			};

			add_filter( 'home_url', $callback );
			$this->assertTrue( rocket_is_live_site() );
			remove_filter( 'home_url', $callback );
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
