<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Settings;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Brain\Monkey\Functions;
use Mockery;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings::display_no_table_notice
 *
 * @group  RUCSS
 */
class Test_DisplayNoTableNotice extends FilesystemTestCase
{
	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Admin/Settings/displayNoTableNotice.php';

	private $options;
	private $settings;
	private $used_css;

	public function setUp(): void {
		parent::setUp();

		$this->options  = Mockery::mock( Options_Data::class );
		$this->used_css = Mockery::mock(UsedCSS::class);
		$this->settings = new Settings( $this->options, Mockery::mock( Beacon::class ), $this->used_css );

		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {

		Functions\when( 'get_current_screen' )->justReturn( $config['screen'] );
		Functions\when( 'current_user_can' )->justReturn( $config['has_right'] );

		$this->options->shouldReceive( 'get' )
			->with( 'remove_unused_css', 0 )
			->andReturn( $config['enabled'] );

		$this->used_css->shouldReceive('exists')->with()->andReturn($config['exists'])->zeroOrMoreTimes();

		if ( $expected ) {
			Functions\expect( 'rocket_notice_html' )
				->once();
		} else {
			Functions\expect( 'rocket_notice_html' )->never();
		}

		$this->assertTrue( $this->filesystem->is_writable( rocket_get_constant( 'WP_ROCKET_USED_CSS_PATH' ) ) );

		$this->settings->display_no_table_notice();
	}
}
