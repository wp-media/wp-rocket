<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization\AMP;

use Brain\Monkey\Functions;
use WP_Rocket\ThirdParty\Plugins\Optimization\AMP;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\AMP::is_amp_compatible_callback
 * @group ThirdParty
 * @group WithAmp
 */
class Test_IsAmpCompatibleCallback extends TestCase {
	private $subscriber;

	public function setUp() {
		parent::setUp();
		$this->subscriber = new AMP( $this->createMock( Options_Data::class ) );
	}

	public function testShouldBailoutIfAmpThemeOptionsAreNull() {
		Functions\expect( 'get_option' )
			->once()
			->with( 'amp-options', [] )
			->andReturn( null );

		$this->assertEquals( [], $this->subscriber->is_amp_compatible_callback( [] ) );
	}

	public function testShouldBailoutIfAmpThemeSupportIsNull() {
		Functions\expect( 'get_option' )
			->once()
			->with( 'amp-options', [] )
			->andReturn( [ 'theme_support' => null ] );

		$this->assertEquals( [], $this->subscriber->is_amp_compatible_callback( [] ) );
	}


	public function testShouldBailoutIfAmpIsNotTransitional() {
		Functions\expect( 'get_option' )
			->once()
			->with( 'amp-options', [] )
			->andReturn( [ 'theme_support' => 'standard' ] );

		$this->assertEquals( [], $this->subscriber->is_amp_compatible_callback( [] ) );
	}

	public function testShouldAddAmpWhenThemeSupportIsTransitional() {
		Functions\expect( 'get_option' )
			->once()
			->with( 'amp-options', [] )
			->andReturn( [ 'theme_support' => 'transitional' ] );

		$this->assertContains( 'amp', $this->subscriber->is_amp_compatible_callback( [] ) );
	}

}
