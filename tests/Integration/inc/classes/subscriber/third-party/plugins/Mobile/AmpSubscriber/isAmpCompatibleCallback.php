<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\third_party\plugins\Mobile\Amp_Subscriber;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Mobile\Amp_Subscriber::is_amp_compatible_callback
 * @group ThirdParty
 * @group WithAmp
 */
class Test_IsAmpCompatibleCallback extends TestCase {
	public function testShouldAddAmpWhenThemeSupportIsTransitional() {
		$this->setSettings( 'theme_support', 'transitional' );
		$this->assertContains( 'amp', apply_filters( 'get_rocket_option_cache_query_strings', [] ) );
	}

	public function testShouldNotAddAmpWhenThemeSupportIsNotTransitional() {
		$this->setSettings( 'theme_support', 'standard' );
		$this->assertEquals( [], apply_filters( 'get_rocket_option_cache_query_strings', [] ) );
	}

	public function testShouldNotAddAmpWhenThemeSupportIsNotSet() {
		$this->setSettings( 'theme_support', null );
		$this->assertEquals( [], apply_filters( 'get_rocket_option_cache_query_strings', [] ) );
	}
}
