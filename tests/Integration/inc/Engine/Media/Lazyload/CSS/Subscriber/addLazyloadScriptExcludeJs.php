<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Lazyload\CSS\Subscriber;

use WP_Rocket\Tests\Integration\FilterTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::add_lazyload_script_exclude_js
 */
class Test_addLazyloadScriptExcludeJs extends TestCase {

	use FilterTrait;

	protected $config;

	public function set_up()
	{
		parent::set_up();
		$this->unregisterAllCallbacksExcept('rocket_exclude_js', 'add_lazyload_script_exclude_js');
		add_filter('pre_get_rocket_option_lazyload_css_bg_img', [$this, 'lazyload_css_bg_img']);
	}

	public function tear_down()
	{
		remove_filter('pre_get_rocket_option_lazyload_css_bg_img', [$this, 'lazyload_css_bg_img']);
		$this->restoreWpFilter('');
		parent::tear_down();
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		$this->config = $config;
        $this->assertSame($expected, apply_filters('rocket_exclude_js', $config['js_files']));
    }

	public function lazyload_css_bg_img() {
		return $this->config['enabled'];
	}
}
