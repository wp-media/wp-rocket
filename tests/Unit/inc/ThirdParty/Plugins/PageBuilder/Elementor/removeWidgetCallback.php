<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use ThirdParty\Plugins\PageBuilder\Elementor\ElementorTestTrait;
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

	use ElementorTestTrait;

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config )
    {
		Filters\expectRemoved('widget_update_callback')->with('rocket_widget_update_callback');
        $this->elementor->remove_widget_callback();
    }
}
