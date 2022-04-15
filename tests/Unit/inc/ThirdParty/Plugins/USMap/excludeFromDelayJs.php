<?php

namespace WP_Rocket\Tests\Unit\Inc\ThirdParty\Plugins\USMap;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\USMap;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\USMap::exclude_from_delay_js
 *
 * @group USMap
 * @group ThirdParty
 */
class Test_ExcludeFromDelayJs extends TestCase {

	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->subscriber = new USMap();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->assertEquals($expected, $this->subscriber->exclude_from_delay_js($config));
	}
}
