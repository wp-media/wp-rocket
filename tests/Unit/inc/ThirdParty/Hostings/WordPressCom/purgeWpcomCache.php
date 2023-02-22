<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\WordPressCom;

use WP_Rocket\ThirdParty\Hostings\WordPressCom;
use Brain\Monkey\Functions;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\WordPressCom::purge_wpcom_cache
 *
 */
class Test_purgeWpcomCache extends TestCase {

    /**
    * @var WordPressCom
    */
    protected $wordpresscom;

    public function set_up() {
        parent::set_up();

        $this->wordpresscom = new WordPressCom();
    }

    public function testShouldReturnExpected()
    {
		Functions\expect('wp_cache_flush');
		$this->wordpresscom->purge_wpcom_cache();
    }
}
