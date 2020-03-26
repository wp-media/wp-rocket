<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization\AMP;

use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\AMP::is_amp_compatible_callback
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\AMP::generate_config_file
 * @group ThirdParty
 * @group WithAmp
 */
class Test_IsAmpCompatibleCallback extends TestCase {

	public function testShouldAddAmpWhenThemeSupportIsTransitional() {
		Functions\expect( 'rocket_generate_config_file' )->once();
		$this->setSettings( 'theme_support', 'transitional' );
		$this->assertContains( 'amp', apply_filters( 'rocket_cache_query_strings', [] ) );
	}

	public function testShouldNotAddAmpWhenThemeSupportIsNotTransitional() {
		Functions\expect( 'rocket_generate_config_file' )->once();
		$this->setSettings( 'theme_support', 'standard' );
		$this->assertEquals( [], apply_filters( 'rocket_cache_query_strings', [] ) );
	}

	public function testShouldNotAddAmpWhenThemeSupportIsNotSet() {
		Functions\expect( 'rocket_generate_config_file' )->once();
		$this->setSettings( 'theme_support', null );
		$this->assertEquals( [], apply_filters( 'rocket_cache_query_strings', [] ) );
	}
}
