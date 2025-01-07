<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::exclude_inline_js
 *
 * @group  Subscribers
 * @group  CriticalCss
 */
class Test_ExcludeInlineJs extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldExcludeInlineJs( $excluded_inline, $expected_inline ) {
			delete_transient( 'wpr_dynamic_lists');
			$this->assertSame( $expected_inline, apply_filters( 'rocket_excluded_inline_js_content', $excluded_inline ) );
	}
}
