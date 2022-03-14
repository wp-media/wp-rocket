<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings::add_localize_script_data
 *
 * @group  RUCSS
 */
class Test_AddLocalizeScriptData extends TestCase {
	private $options;
	private $settings;

	public function set_up() {
		parent::set_up();

		$this->options  = Mockery::mock( Options_Data::class );
		$this->settings = new Settings( $this->options, Mockery::mock( Beacon::class ) );
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
