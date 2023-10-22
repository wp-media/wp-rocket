<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Engine\Common\Ajax\AjaxHandler;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::add_clear_action
 */
class Test_addClearAction extends TestCase {

    /**
     * @var Options_Data
     */
    protected $options;

    protected $filesystem;

    /**
     * @var HTML
     */
    protected $delayjs_html;

    /**
     * @var UsedCSS
     */
    protected $used_css;

    /**
     * @var AjaxHandler
     */
    protected $ajax_handler;

    /**
     * @var Elementor
     */
    protected $elementor;

    public function set_up() {
        parent::set_up();
        $this->options = Mockery::mock(Options_Data::class);
        $this->filesystem = null;
        $this->delayjs_html = Mockery::mock(HTML::class);
        $this->used_css = Mockery::mock(UsedCSS::class);
        $this->ajax_handler = Mockery::mock(AjaxHandler::class);

        $this->elementor = new Elementor($this->options, $this->filesystem, $this->delayjs_html, $this->used_css, $this->ajax_handler);
    }

	protected function tear_down()
	{
		unset( $_SERVER['REQUEST_URI'] );
		parent::tear_down();
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		$this->stubTranslationFunctions();

		$_SERVER['REQUEST_URI'] = $config['request_uri'];

		$this->options->allows()->get('remove_unused_css', false)->andReturn($config['rucss']);

		Functions\when('admin_url')->justReturn($config['admin_url']);
		Functions\when('add_query_arg')->returnArg(2);
		Functions\when('wp_nonce_url')->returnArg();

        $this->assertSame($expected, $this->elementor->add_clear_action($config['args']));
    }
}
