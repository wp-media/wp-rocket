<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Mockery;
use WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\License\API\User;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::switch_to_rucss_notice
 */
class Test_switchToRucssNotice extends TestCase {

    /**
     * @var CriticalCSS
     */
    protected $critical_css;

    /**
     * @var ProcessorService
     */
    protected $cpcss_service;

    /**
     * @var Options_Data
     */
    protected $options;

    /**
     * @var Options
     */
    protected $options_api;

    /**
     * @var User
     */
    protected $user;

    protected $filesystem;

    /**
     * @var CriticalCSSSubscriber
     */
    protected $criticalcsssubscriber;

    public function set_up() {
        parent::set_up();
        $this->critical_css = Mockery::mock(CriticalCSS::class);
        $this->cpcss_service = Mockery::mock(ProcessorService::class);
        $this->options = Mockery::mock(Options_Data::class);
        $this->options_api = Mockery::mock(Options::class);
        $this->user = Mockery::mock(User::class);
        $this->filesystem = null;

        $this->criticalcsssubscriber = new CriticalCSSSubscriber($this->critical_css, $this->cpcss_service, $this->options, $this->options_api, $this->user, $this->filesystem);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		$this->stubTranslationFunctions();
		Functions\expect('get_current_user_id')->andReturn($config['user_id']);
		Functions\expect('get_user_meta')->with($expected['user_id'], 'rocket_boxes', true)->andReturn($config['boxes']);
        $this->configure_async_css_activated($config, $expected);
		$this->configure_licence($config, $expected);
		$this->configure_current_screen($config, $expected);
		$this->configure_display_notice($config, $expected);
		$this->criticalcsssubscriber->switch_to_rucss_notice();
    }

	protected function configure_async_css_activated($config, $expected) {
		if( $config['in_boxes'] ) {
			return;
		}
		$this->options->expects()->get('async_css', 0)->andReturn($config['async_css']);
	}

	protected function configure_licence( $config, $expected ) {
		if( $config['in_boxes'] || ! $config['async_css'] ) {
			return;
		}
		$this->user->expects()->is_license_expired()->andReturn($config['expired_license']);
	}

	protected function configure_current_screen( $config, $expected ) {
		if( $config['in_boxes'] || ! $config['async_css'] || $config['expired_license'] ) {
			return;
		}
		Functions\expect('get_current_screen')->andReturn($config['screen']);
	}

	protected function configure_display_notice( $config ,$expected ) {
		if( $config['in_boxes'] || ! $config['async_css'] || $config['expired_license'] || ! $config['is_right_screen'] ) {
			Functions\expect('rocket_notice_html')->never();
			return;
		}
		Functions\expect('rocket_notice_html')->with($expected['notice']);
	}
}
