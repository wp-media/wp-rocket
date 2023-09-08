<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::maybe_clear_cache_change_notice
 */
class Test_maybeClearCacheChangeNotice extends TestCase {

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
    public function testShouldDoAsExpected( $config, $expected )
    {
		$this->stubTranslationFunctions();
		Functions\when('get_current_user_id')->justReturn($config['user_id']);
		Functions\when('get_user_meta')->justReturn($config['boxes']);
		Functions\when('current_user_can')->justReturn($config['can']);
		Functions\when('get_transient')->justReturn($config['transient']);

		if($config['notice']) {
			Functions\expect('rocket_notice_html')->with($expected['notice']);
		} else {
			Functions\expect('rocket_notice_html')->never();
		}

		$this->elementor->maybe_clear_cache_change_notice();

    }
}
