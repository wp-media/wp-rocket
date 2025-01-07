<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::exclude_inline_js
 *
 * @group  Subscribers
 * @group  CriticalPath
 */
class Test_ExcludeInlineJs extends TestCase {
	use SubscriberTrait;

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldInsertCriticalCSS( $excluded_inline, $expected_inline ) {
		$this->setUpTests();

		// Run it.
		$this->assertSame( $expected_inline, $this->subscriber->exclude_inline_js( $excluded_inline ) );
	}
}
