<?php

namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\third_party\plugins\Mobile\Amp_Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Subscriber\Third_Party\Plugins\Mobile\Amp_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Mobile\Amp_Subscriber::is_amp_compatible_callback
 * @group ThirdParty
 * @group Amp
 */
class Test_IsAmpCompatibleCallback extends TestCase {
	public function testShouldBailoutIfAmpThemeOptionsAreNull() {
		$subscriber = new Amp_Subscriber();

		Functions\expect( 'get_option' )
			->once()
			->with( 'amp-options', [] )
			->andReturn( null );

		$this->assertEquals( [], $subscriber->is_amp_compatible_callback( [] ) );
	}

	public function testShouldBailoutIfAmpThemeSupportIsNull() {
		$subscriber = new Amp_Subscriber();

		Functions\expect( 'get_option' )
			->once()
			->with( 'amp-options', [] )
			->andReturn( [ 'theme_support' => null ] );

		$this->assertEquals( [], $subscriber->is_amp_compatible_callback( [] ) );
	}


	public function testShouldBailoutIfAmpIsNotTransitional() {
		$subscriber = new Amp_Subscriber();

		Functions\expect( 'get_option' )
			->once()
			->with( 'amp-options', [] )
			->andReturn( [ 'theme_support' => 'standard' ] );

		$this->assertEquals( [], $subscriber->is_amp_compatible_callback( [] ) );
	}

	public function testShouldAddAmpWhenThemeSupportIsTransitional() {
		$subscriber = new Amp_Subscriber();

		Functions\expect( 'get_option' )
			->once()
			->with( 'amp-options', [] )
			->andReturn( [ 'theme_support' => 'transitional' ] );

		$this->assertContains( 'amp', $subscriber->is_amp_compatible_callback( [] ) );
	}

}
