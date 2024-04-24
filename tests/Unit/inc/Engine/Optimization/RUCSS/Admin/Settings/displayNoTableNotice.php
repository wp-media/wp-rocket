<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Settings;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings::display_no_table_notice
 *
 * @group  RUCSS
 */
class Test_DisplayNoTableNotice extends FilesystemTestCase
{
	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Admin/Settings/displayNoTableNotice.php';

	protected $settings;
	protected $options;
	protected $beacon;
	protected $table;

	public function setUp(): void
	{
		parent::setUp();
		$this->options  = Mockery::mock( Options_Data::class );
		$this->beacon  = Mockery::mock( Beacon::class );
		$this->table  = $this->createMock(UsedCSS::class);
		$this->settings = new Settings( $this->options, $this->beacon,  $this->table);

		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Functions\when( 'get_current_screen' )->justReturn( $config['current_screen'] );
		Functions\when( 'current_user_can' )->justReturn( $config['has_rights'] );

		$this->options->shouldReceive( 'get' )
			->with( 'remove_unused_css', 0 )
			->andReturn( $config['is_enabled'] );

		$this->table->expects(self::atLeast(0))->method('exists')->willReturn($config['table_exists']);

		if ( $expected ) {
			Functions\expect( 'rocket_notice_html' )
				->once();
		} else {
			Functions\expect( 'rocket_notice_html' )->never();
		}

		$this->settings->display_no_table_notice();
	}
}
