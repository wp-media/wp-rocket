<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Admin\Settings::maybe_display_preload_notice
 * @group  Preload
 */
class Test_MaybeDisplayPreloadNotice extends TestCase {
	protected $settings;
	protected $options;

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = Mockery::mock(Options_Data::class);
		$this->settings = new Settings($this->options, Mockery::mock(PreloadUrl::class));
		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		Functions\when('get_current_screen')->justReturn( $config['screen'] );
		Functions\when('current_user_can')->justReturn( $config['has_right'] );
		Functions\when('get_current_user_id')->justReturn( 1 );
		Functions\when('get_user_meta')->justReturn([]);

		Functions\expect( 'rocket_dismiss_box' )
			->with( 'preload_notice' )
			->atMost()
			->once();

		$this->options->allows()->get('manual_preload', 0)->andReturns($config['enabled']);
		Functions\when('get_transient')->justReturn($config['transient']);

		if ( $expected ) {
			Functions\expect('rocket_notice_html')->once()->with($expected['notice']);
		} else {
			Functions\expect('rocket_notice_html')->never();
		}

		$this->settings->maybe_display_preload_notice();
	}
}
