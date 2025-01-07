<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\ExtractCSS\Subscriber;

use WP_Rocket\Tests\Integration\IsolateHookTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\ExtractCSS\Subscriber::extract_inline_css_from_html
 */
class Test_extractInlineCssFromHtml extends TestCase {

	use IsolateHookTrait;

	public function set_up()
	{
		parent::set_up();
		$this->unregisterAllCallbacksExcept('rocket_generate_lazyloaded_css', 'extract_inline_css_from_html', 14);
	}

	public function tear_down()
	{
		$this->restoreWpHook('rocket_generate_lazyloaded_css');
		parent::tear_down();
	}

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
        $this->assertSame($expected, apply_filters('rocket_generate_lazyloaded_css', $config['data']));
    }
}
