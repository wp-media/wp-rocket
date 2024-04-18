<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings::add_localize_script_data
 *
 * @group  RUCSS
 */
class Test_AddLocalizeScriptData extends TestCase {
	private $options;
	private $settings;
	private $used_css;

	public function setUp(): void {
		parent::setUp();

		$this->options  = Mockery::mock( Options_Data::class );
		$this->used_css = $this->createMock(UsedCSS::class);
		$this->settings = new Settings( $this->options, Mockery::mock( Beacon::class ), $this->used_css );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->options->shouldReceive( 'get' )
			->once()
			->with( 'remove_unused_css', 0 )
			->andReturn( $config['remove_unused_css'] );

		Functions\when( 'get_transient' )->justReturn( $config['transient'] );

		$this->assertSame(
			$expected,
			$this->settings->add_localize_script_data( $config['data'] )
		);
	}
}
