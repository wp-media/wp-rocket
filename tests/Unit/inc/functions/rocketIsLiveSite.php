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

	public static function setUpBeforeClass() {
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
		$urls[] = 'example.dev';
		$urls[] = 'example.dev.cc';
		foreach ( $urls as $domain ) {
			Functions\when( 'wp_parse_url' )->justReturn( $domain );

			$this->assertFalse( rocket_is_live_site() );
		}
	}

	public function testShouldReturnFalseWhenIsLiveProdSiteFilterOnButLocalOrStaging() {
		Functions\when( 'rocket_get_constant' )->justReturn( false );
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );

		add_filter( 'rocket_tld_is_live_prod_site', '__return_true' );
		foreach ( $this->getLocalStagingSites() as $domain ) {
			Functions\when( 'wp_parse_url' )->justReturn( $domain );

			$this->assertFalse( rocket_is_live_site() );
		}
		remove_filter( 'rocket_tld_is_live_prod_site', '__return_false' );
	}

	public function testShouldReturnTrueWhenDevTLDIsLiveSite() {
		Functions\when( 'rocket_get_constant' )->justReturn( false );
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );

		foreach ( [ 'example.dev', 'example.dev.cc' ] as $domain ) {
			Functions\when( 'wp_parse_url' )->justReturn( $domain );
			Filters\expectApplied( 'rocket_tld_is_live_prod_site' )
				->once()
				->with( false )
				->andReturn( true );

			$this->assertTrue( rocket_is_live_site() );
		}
	}

	public function testShouldReturnTrueWhenLiveSite() {
		Functions\when( 'rocket_get_constant' )->justReturn( false );
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\when( 'wp_parse_url' )->justReturn( 'example.org' );

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
