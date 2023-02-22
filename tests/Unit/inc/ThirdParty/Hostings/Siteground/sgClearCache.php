<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Siteground;

use WP_Rocket\ThirdParty\Hostings\Siteground;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Siteground::sg_clear_cache
 *
 */
class Test_sgClearCache extends TestCase {

    /**
    * @var Siteground
    */
    protected $siteground;

    public function set_up() {
        parent::set_up();
		$_GET['_wpnonce'] = true;
        $this->siteground = new Siteground();
    }

	protected function tear_down()
	{
		unset( $_GET['_wpnonce'] );
		parent::tear_down();
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {
		Functions\expect('sanitize_key')->with($expected['sanitize_key'])->andReturn($config['sanitize_key']);
		Functions\expect('wp_verify_nonce')->with($expected['sanitize_key'],  'sg-cachepress-purge')->andReturn($config['nonce']);
		$this->configureVerifyUserRight($config);
		$this->configureClean($config);
		$this->siteground->sg_clear_cache();
	}

	protected function configureVerifyUserRight($config) {
		if(! $config['nonce']) {
			return;
		}

		Functions\expect('current_user_can')->with('rocket_purge_cache')->andReturn($config['has_right']);
	}

	protected function configureClean($config) {
		if(! $config['nonce'] || ! $config['has_right']) {
			Functions\expect('rocket_clean_domain')->never();
			return;
		}

		Functions\expect('rocket_clean_domain');
	}
}
