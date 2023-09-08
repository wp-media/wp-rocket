<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use Brain\Monkey\Functions;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::setup_transient
 */
class Test_setupTransient extends TestCase {

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
		Functions\when('get_current_user_id')->justReturn($config['user_id']);
		Functions\when('get_user_meta')->justReturn($config['boxes']);
		$this->options->allows()->get('remove_unused_css', false)->andReturn($config['remove_unused_css']);

		if($config['change']) {
			Functions\expect('update_user_meta')->with($expected['user_id'], 'rocket_boxes', $expected['boxes']);
			Functions\expect('set_transient')->with('wpr_elementor_need_purge', true);
		} else {
			Functions\expect('update_user_meta')->never();
			Functions\expect('set_transient')->never();
		}

		$this->elementor->setup_transient($config['check'], $config['object_id'], $config['meta_key'], $config['meta_value'], $config['prev_value']);

	}
}
