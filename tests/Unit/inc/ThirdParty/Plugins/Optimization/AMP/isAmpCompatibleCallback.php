<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization\AMP;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Plugins\Optimization\AMP;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\AMP::is_amp_compatible_callback
 * @group ThirdParty
 * @group WithAmp
 */
class Test_IsAmpCompatibleCallback extends TestCase {
	private $amp;

	public function setUp() {
		parent::setUp();

		$this->amp = new AMP( $this->createMock( Options_Data::class ) );
	}

	public function testShouldBailoutIfAmpThemeOptionsAreNull() {
		Functions\expect( 'get_option' )
			->once()
			->with( 'amp-options', [] )
			->andReturn( null );

		$this->assertEquals( [], $this->amp->is_amp_compatible_callback( [] ) );
	}

	public function testShouldBailoutIfAmpThemeSupportIsNull() {
		Functions\expect( 'get_option' )
			->once()
			->with( 'amp-options', [] )
			->andReturn( [ 'theme_support' => null ] );

		$this->assertEquals( [], $this->amp->is_amp_compatible_callback( [] ) );
	}


	public function testShouldBailoutIfAmpIsNotTransitional() {
		Functions\expect( 'get_option' )
			->once()
			->with( 'amp-options', [] )
			->andReturn( [ 'theme_support' => 'standard' ] );

		$this->assertEquals( [], $this->amp->is_amp_compatible_callback( [] ) );
	}

	public function testShouldAddAmpWhenThemeSupportIsTransitional() {
		Functions\expect( 'get_option' )
			->once()
			->with( 'amp-options', [] )
			->andReturn( [ 'theme_support' => 'transitional' ] );

		$this->assertContains( 'amp', $this->amp->is_amp_compatible_callback( [] ) );
	}

}
