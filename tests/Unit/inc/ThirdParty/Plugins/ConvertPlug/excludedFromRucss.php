<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\ConvertPlug;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\ConvertPlug;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\ConvertPlug::excluded_from_rucss
 * @group ConvertPlug
 * @group ThirdParty
 */
class Test_ExcludedFromRucss extends TestCase {
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->subscriber = new ConvertPlug();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {

		$this->assertSame($expected, $this->subscriber->excluded_from_rucss($config['excluded']));
	}
}
