<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Xstore;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\Xstore;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Xstore::exclude_inline_content
 * @group ThirdParty
 */
class Test_ExcludeInlineContent extends TestCase {
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->subscriber = new Xstore();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {

		$this->assertSame($expected, $this->subscriber->exclude_inline_content($config['excluded']));
	}
}
