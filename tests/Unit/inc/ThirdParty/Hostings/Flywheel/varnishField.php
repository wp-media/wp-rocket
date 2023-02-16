<?php

namespace WP_Rocket\Tests\Unit\ThirdParty\Hostings\Flywheel;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Flywheel;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Flywheel::varnish_field
 *
 */
class Test_varnishField extends TestCase {

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
		$this->stubTranslationFunctions();
		$this->assertSame($expected, $this->subscriber->varnish_field($config));
    }
}
