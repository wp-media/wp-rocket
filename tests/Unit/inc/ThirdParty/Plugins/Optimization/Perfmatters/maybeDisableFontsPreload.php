<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Optimization\Perfmatters;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\Optimization\Perfmatters::maybe_disable_fonts_preload
 */
class Test_MaybeDisableFontsPreload extends TestCase {
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
        Functions\expect( 'get_option' )
			->once()
			->with( 'perfmatters_options' )
			->andReturn( $config['perfmatters_options'] );

		$this->assertSame( $expected, $this->subscriber->maybe_disable_fonts_preload() );
	}
}
