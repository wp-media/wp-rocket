<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;
/**
 * @covers \WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::remove_widget_callback
 */
class Test_removeWidgetCallback extends TestCase {

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
     * @var Elementor
     */
    protected $elementor;

    public function set_up() {
        parent::set_up();
        $this->options = Mockery::mock(Options_Data::class);
        $this->filesystem = null;
        $this->delayjs_html = Mockery::mock(HTML::class);
        $this->used_css = Mockery::mock(UsedCSS::class);

        $this->elementor = new Elementor($this->options, $this->filesystem, $this->delayjs_html, $this->used_css);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config )
    {
		Filters\expectRemoved('widget_update_callback')->with('rocket_widget_update_callback');
        $this->elementor->remove_widget_callback();
    }
}
