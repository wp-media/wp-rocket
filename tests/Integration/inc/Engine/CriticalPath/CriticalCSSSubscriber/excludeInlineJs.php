<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use WP_Rocket\Tests\Integration\FilterTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::exclude_inline_js
 *
 * @group  Subscribers
 * @group  CriticalCss
 */
class Test_ExcludeInlineJs extends TestCase {

	use FilterTrait;

	public function set_up()
	{
		parent::set_up();
		$this->unregisterAllCallbacksExcept('rocket_excluded_inline_js_content', 'exclude_inline_js');
	}

	public function tear_down()
	{
		$this->restoreWpFilter('rocket_excluded_inline_js_content');
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldExcludeInlineJs( $excluded_inline, $expected_inline ) {
		$this->assertSame( $expected_inline, apply_filters( 'rocket_excluded_inline_js_content', $excluded_inline ) );
	}
}
