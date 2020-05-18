<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::exclude_inline_js
 * @group  Subscribers
 * @group  CriticalCss
 */
class Test_ExcludeInlineJs extends TestCase {
	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldExcludeInlineJs( $excluded_inline, $expected_inline ) {
		$this->assertSame( $expected_inline, apply_filters( 'rocket_excluded_inline_js_content', $excluded_inline ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'excludeInlineJs' );
	}
}
