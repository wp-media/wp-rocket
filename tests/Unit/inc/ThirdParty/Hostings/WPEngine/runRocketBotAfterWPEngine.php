<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\WPEngine;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\WPEngine;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\WPEngine::run_rocket_bot_after_wpengine
 * @uses   ::rocket_has_constant
 * @uses   ::rocket_get_constant
 * @uses   ::run_rocket_bot
 * @uses   ::run_rocket_sitemap_preload
 *
 * @group  WPEngine
 * @group  ThirdParty
 */
class Test_RunRocketBotAfterWPEngine extends TestCase {
	private $wpengine;

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRunRocketBotAfterWPEngine( $config, $expected ) {
		Functions\when( 'current_user_can' )->justReturn( true );

		if ( isset( $config['wpe_param'] ) ) {
			Functions\expect( 'wpe_param' )
				->once()
				->with( 'purge-all' )
				->andReturn( $config['wpe_param'] );
		}

		if ( isset( $config['pwp_constant'] ) ) {
			Functions\expect( 'rocket_has_constant' )
				->once()
				->with( 'PWP_NAME' )
				->andReturn( $config['pwp_constant'] );
		}

		if ( isset( $config['check_admin_referer'] ) ) {
			Functions\expect( 'rocket_get_constant' )
				->once()
				->with( 'PWP_NAME' )
				->andReturn( 'pwp_constant' );
			Functions\expect( 'check_admin_referer' )
				->once()
				->andReturn( $config['check_admin_referer'] );
		}

		if ( $expected ) {
			Functions\expect( 'run_rocket_bot' )->once();
			Functions\expect( 'run_rocket_sitemap_preload' )->once();
		} else {
			Functions\expect( 'run_rocket_bot' )->never();
			Functions\expect( 'run_rocket_sitemap_preload' )->never();
		}
		$this->wpengine = new WPEngine();
		$this->wpengine->run_rocket_bot_after_wpengine();
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'runRocketBotAfterWPEngine' );
	}
}
