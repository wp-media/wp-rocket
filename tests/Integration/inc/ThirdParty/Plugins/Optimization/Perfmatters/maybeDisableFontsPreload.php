<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\Optimization\Perfmatters::maybe_disable_fonts_preload
 * 
 * @group Perfmatters
 */
class Test_MaybeDisableFontsPreload extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
        Functions\expect( 'get_option' )
			->once()
			->with( 'perfmatters_options' )
			->andReturn( $config['perfmatters_options'] );
            
		$this->assertSame( $expected, apply_filters( 'rocket_enable_rucss_fonts_preload', true ) );
	}
}