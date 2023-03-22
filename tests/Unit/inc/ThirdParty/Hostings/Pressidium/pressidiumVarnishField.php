<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Pressidium;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Pressidium;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Pressidium::pressidium_varnish_field
 *
 */
class Test_pressidiumVarnishField extends TestCase {

	/**
	 * @var Pressidium
	 */
	protected $subscriber;

	public function set_up()
	{
		parent::set_up();
		$this->subscriber = new Pressidium();
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {
		$this->stubTranslationFunctions();
		$this->assertSame($expected, $this->subscriber->pressidium_varnish_field($config));
    }
}
