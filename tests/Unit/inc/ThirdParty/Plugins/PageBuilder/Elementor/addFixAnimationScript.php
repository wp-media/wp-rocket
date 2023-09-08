<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use WP_Filesystem_Direct;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::add_fix_animation_script
 */
class Test_addFixAnimationScript extends TestCase {

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
        $this->filesystem = Mockery::mock(WP_Filesystem_Direct::class);
        $this->delayjs_html = Mockery::mock(HTML::class);
        $this->used_css = Mockery::mock(UsedCSS::class);

        $this->elementor = new Elementor($this->options, $this->filesystem, $this->delayjs_html, $this->used_css);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		Functions\when('rocket_get_constant')->returnArg();
		$this->filesystem->allows()->get_contents('WP_ROCKET_PATHassets/js/elementor-animation.js')->andReturn($config['script']);
        $this->delayjs_html->allows()->is_allowed()->andReturn($config['is_allowed']);
		$this->assertSame($expected, $this->elementor->add_fix_animation_script($config['html']));
    }
}
