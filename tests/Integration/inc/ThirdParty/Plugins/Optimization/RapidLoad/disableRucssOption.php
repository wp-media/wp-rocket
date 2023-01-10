<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization;

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
		$this->assertSame( $expected, apply_filters( 'rocket_maybe_disable_rucss', $config['rucss_status'] ) );
	}
}
