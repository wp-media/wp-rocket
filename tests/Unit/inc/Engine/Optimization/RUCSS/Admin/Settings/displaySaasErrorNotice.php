<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Settings;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

class Test_DisplaySaasErrorNotice extends FilesystemTestCase
{
	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Admin/Settings/displaySaasErrorNotice.php';

	protected $settings;
	protected $options;
	protected $beacon;
	protected $used_css;

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = Mockery::mock(Options_Data::class);
		$this->beacon = Mockery::mock(Beacon::class);
		$this->used_css = $this->createMock(UsedCSS::class);
		$this->settings = new Settings($this->options, $this->beacon, $this->used_css);
		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Functions\when( 'get_current_screen' )->justReturn( $config['current_screen'] );
		Functions\when( 'current_user_can' )->justReturn( $config['has_rights'] );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( $config['boxes'] );
		Functions\expect('get_transient')->with('wp_rocket_rucss_errors_count' )->andReturn($config['saas_transient']);
		$this->options->shouldReceive('get')->with('remove_unused_css', 0)->andReturn($config['has_rights'])
			->zeroOrMoreTimes();
		Functions\when('rocket_notice_html')->justEcho();
		ob_start();
		$this->settings->display_saas_error_notice();
		$result = ob_get_clean();
		$this->assertSame($expected, $result);
	}
}
