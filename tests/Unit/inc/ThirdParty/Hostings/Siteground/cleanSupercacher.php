<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Siteground;

use Mockery;
use WP_Rocket\ThirdParty\Hostings\Siteground;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Supercacher;
use SG_CachePress_Supercacher;
/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Siteground::clean_supercacher
 *
 */
class Test_cleanSupercacher extends TestCase {

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
	public function testShouldReturnExpected( $config )
	{
		Functions\expect('rocket_is_supercacher_active')->andReturn($config['is_active']);
		$this->configureClearCache($config);
		$this->siteground->clean_supercacher();
	}

	protected function configureClearCache($config) {
		global $sg_cachepress_supercacher;
		Functions\expect('rocket_get_sg_optimizer_version')->andReturn($config['version']);


		if($config['is_version_superior']) {
			$super_cache = Mockery::mock('overload:' . Supercacher::class);
			$super_cache->expects()->purge_cache();
			return;
		}

		$super_cache = Mockery::mock('overload:' . SG_CachePress_Supercacher::class);
		$super_cache->expects()->purge_cache();
		$sg_cachepress_supercacher = $super_cache;
	}
}
