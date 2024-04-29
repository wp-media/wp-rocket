<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\MinimalistBlogger;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\MinimalistBlogger;

/**
 * Test class covering \WP_Rocket\ThirdParty\Themes\MinimalistBlogger::exclude_jquery_from_delay_js
 *
 * @group  ThirdParty
 */
class Test_ExcludeJqueryFromDelayJs extends TestCase {
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->subscriber = new MinimalistBlogger();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->assertEquals($expected, $this->subscriber->exclude_jquery_from_delay_js($config['excluded']));
	}
}
