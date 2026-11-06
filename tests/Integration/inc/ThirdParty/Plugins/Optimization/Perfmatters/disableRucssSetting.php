<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\ThirdParty\Plugins\Optimization\Perfmatters::disable_rucss_option
 * 
 * @group Perfmatters
 */
class Test_DisableRucssOptionPerfmatters extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
        Functions\expect( 'get_option' )
			->once()
			->with( 'perfmatters_options' )
			->andReturn( $config['perfmatters_options'] );
            
		$this->assertSame( $expected, apply_filters( 'rocket_disable_rucss_setting', $config['rucss_status'] ) );
	}
}