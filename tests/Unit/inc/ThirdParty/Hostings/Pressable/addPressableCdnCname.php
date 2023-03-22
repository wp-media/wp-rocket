<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Pressable;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Pressable;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Pressable::add_pressable_cdn_cname
 *
 */
class Test_addPressableCdnCname extends TestCase {

	/**
	 * @var Pressable
	 */
	protected $subscriber;

	protected function set_up()
	{
		parent::set_up();
		$this->subscriber = new Pressable();
		if(! defined('WP_STACK_CDN_DOMAIN')) {
			define('WP_STACK_CDN_DOMAIN', 'WP_STACK_CDN_DOMAIN');
		}
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {
		$this->assertSame($expected, $this->subscriber->add_pressable_cdn_cname($config));
    }
}
