<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Admin\Settings;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Preload\Admin\Settings::maybe_display_preload_notice
 * @group  Preload
 */
class Test_MaybeDisplayPreloadNotice extends TestCase {
	protected $settings;
	protected $options;

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = Mockery::mock(Options_Data::class);
		$this->settings = new Settings($this->options);
		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Functions\expect('get_current_screen')->with()->andReturn($config['screen']);
		Functions\expect('current_user_can')->with('rocket_manage_options')->andReturn($config['has_right']);
		Functions\expect( 'rocket_dismiss_box' )->with( 'preload_notice' )->andReturnNull();
		$this->configureRocketBoxes( $config );
		$this->configureEnabled($config);
		$this->configureTransient($config);
		$this->configureNotice($config, $expected);
		$this->settings->maybe_display_preload_notice();
	}

	protected function configureEnabled($config) {
		if(! key_exists('enabled', $config)) {
			return;
		}
		$this->options->expects()->get('manual_preload', 0)->andReturn($config['enabled']);
	}

	protected function configureTransient($config) {
		if(! key_exists('load_transient', $config)) {
			return;
		}

		Functions\expect('get_transient')->with('rocket_preload_processing')->andReturn($config['transient']);
	}

	protected function configureRocketBoxes( $config ) {
		if(! key_exists('rocket_boxes', $config)) {
			return;
		}

		Functions\expect('get_current_user_id')->andReturn( 1 );
		Functions\when('get_user_meta')->justReturn( $config['rocket_boxes'] );
	}

	protected function configureNotice($config, $expected) {
		if(! key_exists('show_display_notice', $config)) {
			return;
		}

		Functions\expect('rocket_notice_html')->with($expected['notice']);
	}
}
