<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Optimization\Perfmatters;

/**
 * Test class covering WP_Rocket\ThirdParty\Plugins\Optimization\Perfmatters::disable_rucss_setting
 */
class Test_DisableRucssOptionPerfmatters extends TestCase {
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->subscriber = new Perfmatters();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
        $this->stubTranslationFunctions();

        Functions\expect( 'get_option' )
			->once()
			->with( 'perfmatters_options' )
			->andReturn( $config['perfmatters_options'] );

		$this->assertSame( $expected, $this->subscriber->disable_rucss_setting( $config['rucss_status'] ) );
	}
}
