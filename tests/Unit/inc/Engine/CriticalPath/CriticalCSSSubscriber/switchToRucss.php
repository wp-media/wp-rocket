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
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::switch_to_rucss
 */
class Test_switchToRucss extends TestCase {

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

	protected function tear_down()
	{
		unset($_GET);
		parent::tear_down();
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		Functions\when('rocket_get_constant')->justReturn(true);
		Functions\expect('check_admin_referer')->with($expected['action']);
		Functions\expect('current_user_can')->with('rocket_manage_options')->andReturn($config['user_can']);
		Functions\expect('wp_get_referer')->andReturn($config['referer']);
		Functions\expect('wp_safe_redirect')->with($expected['referer']);
		Functions\expect('wp_die');
		$this->configure_switch_rucss($config, $expected);
		$this->configure_dismiss($config, $expected);
        $this->criticalcsssubscriber->switch_to_rucss();

    }

	protected function configure_switch_rucss( $config, $expected ) {
		if(! $config['user_can']) {
			return;
		}
		$this->options->expects()->set('critical_css', false);
		$this->options->expects()->set('remove_unused_css', true);
		$this->options->expects()->get_options()->andReturn($config['options']);
		$this->options_api->expects()->set('settings', $expected['options']);

	}

	protected function configure_dismiss( $config, $expected ) {
		if (! $config['user_can']) {
			return;
		}
		Functions\expect('rocket_dismiss_box')->with('switch_to_rucss_notice');
	}
}
