<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Beacon\Beacon;

use WP_Rocket\Engine\Admin\ActionSchedulerSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\ActionSchedulerSubscriber::hide_pastdue_status_filter
 * @group  Beacon
 */
class Test_HidePastdueStatusFilter extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldHidePastDue( $input, $expected ) {
		$subscriber = new ActionSchedulerSubscriber();
		$this->assertSame( $expected, $subscriber->hide_pastdue_status_filter( $input ) );
	}

}
