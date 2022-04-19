<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\MinimalistBlogger;

use WP_Rocket\ThirdParty\Themes\MinimalistBlogger;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\MinimalistBlogger::exclude_jquery_from_delay_js
 *
 * @group  ThirdParty
 */
class Test_ExcludeJqueryFromDelayJs extends \WP_Rocket\Tests\Unit\TestCase {
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->subscriber = new MinimalistBlogger();
	}

	public function testShouldReturnAsExpected($config, $expected) {
		$this->assertEquals($expected, $this->subscriber->exclude_jquery_from_delay_js($config['excluded']));
	}
}
