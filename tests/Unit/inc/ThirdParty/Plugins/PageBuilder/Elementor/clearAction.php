<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use ThirdParty\Plugins\PageBuilder\Elementor\ElementorTestTrait;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Engine\Common\Ajax\AjaxHandler;
use Brain\Monkey\Functions;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::clear_action
 */
class Test_clearAction extends TestCase {
	use ElementorTestTrait;

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		$this->ajax_handler->allows()->validate_referer('rocket_elementor_clear_usedcss', 'rocket_remove_unused_css')->andReturn($config['is_valid']);
		$this->options->allows()->get('remove_unused_css', false)->andReturn($config['rucss']);

		Functions\when('wp_get_referer')->justReturn($config['referer']);
		Functions\when('get_current_user_id')->justReturn($config['user_id']);
		Functions\when('get_user_meta')->justReturn($config['boxes']);

		if($expected['cleared']) {
			$this->configureClean($config, $expected);
			Functions\expect('update_user_meta')->with($expected['user_id'], 'rocket_boxes', $expected['boxes']);
			$this->ajax_handler->expects()->redirect();
		} else {
			Functions\expect('wp_nonce_ays');
		}

        $this->elementor->clear_action();
    }


	protected function configureClean($config, $expected) {
		Functions\expect('rocket_clean_domain');
		if(! $config['rucss']) {
			return;
		}
		$this->used_css->expects()->delete_used_css_rows();
	}
}
