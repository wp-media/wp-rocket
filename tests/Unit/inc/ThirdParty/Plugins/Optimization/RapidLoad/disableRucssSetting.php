<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Optimization\RapidLoad;

/**
 * Test class covering WP_Rocket\ThirdParty\Plugins\Optimization\RapidLoad::disable_rucss_setting
 */
class Test_DisableRucssOptionRapidLoad extends TestCase {
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->subscriber = new RapidLoad();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
        $this->stubTranslationFunctions();

        Functions\expect( 'get_option' )
			->once()
			->with( 'autoptimize_uucss_settings' )
			->andReturn( $config['autoptimize_uucss_settings'] );

		$this->assertSame( $expected, $this->subscriber->disable_rucss_setting( $config['rucss_status'] ) );
	}
}
