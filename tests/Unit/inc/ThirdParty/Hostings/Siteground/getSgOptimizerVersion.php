<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Siteground;

use WP_Rocket\ThirdParty\Hostings\Siteground;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Siteground::get_sg_optimizer_version
 *
 */
class Test_getSgOptimizerVersion extends TestCase {

    /**
    * @var Siteground
    */
    protected $siteground;

    public function set_up() {
        parent::set_up();

        $this->siteground = new Siteground();

		if( ! defined('WP_PLUGIN_DIR') ) {
			define('WP_PLUGIN_DIR', 'WP_PLUGIN_DIR');
		}

    }

	/**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {
		Functions\expect('get_file_data')->with($expected['cache_file'], $expected['params'])->andReturn($config['optimizer']);
		$this->assertSame($expected['version'], $this->siteground->get_sg_optimizer_version());
    }
}
