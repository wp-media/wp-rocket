<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Siteground;

use Mockery;
use SG_CachePress_Supercacher;
use WP_Rocket\ThirdParty\Hostings\Siteground;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Siteground::is_supercacher_active
 *
 */
class Test_isSupercacherActive extends TestCase {

    /**
    * @var Siteground
    */
    protected $siteground;

    public function set_up() {
        parent::set_up();

        $this->siteground = new Siteground();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {
		Functions\expect('rocket_get_sg_optimizer_version')->andReturn($config['version']);
		if(! $config['is_version_superior']) {
			$super_cache = Mockery::mock('overload:' . SG_CachePress_Supercacher::class);
			$super_cache->expects()->purge_cache();
		}
		Functions\expect('get_option')->with($expected['option_name'], $expected['option_value']);
		$this->siteground->is_supercacher_active();
    }
}
