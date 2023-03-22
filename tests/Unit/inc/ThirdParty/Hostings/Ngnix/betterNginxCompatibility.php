<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Ngnix;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Ngnix;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Ngnix::better_nginx_compatibility
 *
 */
class Test_betterNginxCompatibility extends TestCase {

	/**
	 * @var Ngnix
	 */
	protected $subscriber;

	protected function set_up()
	{
		parent::set_up();
		$this->subscriber = new Ngnix();
		$GLOBALS['is_nginx'] = true;
	}

	protected function tear_down()
	{
		unset($GLOBALS['is_nginx']);
		parent::tear_down();
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {
		$this->assertSame($expected, $this->subscriber->better_nginx_compatibility($config));
    }
}
