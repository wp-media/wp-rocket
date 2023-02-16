<?php

namespace WP_Rocket\Tests\Unit\ThirdParty\Hostings\Flywheel;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Flywheel;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Flywheel::ip_on_flywheel
 *
 */
class Test_ipOnFlywheel extends TestCase {

	/**
	 * @var Flywheel
	 */
	protected $subscriber;

	protected function set_up()
	{
		parent::set_up();
		$this->subscriber = new Flywheel();
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {
		$this->assertSame($expected, $this->subscriber->ip_on_flywheel($config));
    }
}
