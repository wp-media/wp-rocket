<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use ThirdParty\Plugins\PageBuilder\Elementor\ElementorTestTrait;
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

   use ElementorTestTrait;

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
