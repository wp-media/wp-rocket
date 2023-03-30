<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\Optimization\RapidLoad::disable_rucss_option
 * 
 * @group RapidLoad
 */
class Test_DisableRucssOptionRapidLoad extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
        Functions\expect( 'get_option' )
			->once()
			->with( 'autoptimize_uucss_settings' )
			->andReturn( $config['autoptimize_uucss_settings'] );

		$this->assertSame( $expected, apply_filters( 'rocket_disable_rucss_setting', $config['rucss_status'] ) );
	}
}
