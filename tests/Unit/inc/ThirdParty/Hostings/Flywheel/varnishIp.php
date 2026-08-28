<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Flywheel;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Flywheel;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Flywheel::varnish_ip
 *
 * @group Flywheel
 * @group ThirdParty
 * @group Hostings
 */
class Test_VarnishIp extends TestCase {
	public function testShouldAddFlywheelVarnishIp() {
		$subscriber = new Flywheel();

		$this->assertSame(
			[ '10.0.0.1', '127.0.0.1' ],
			$subscriber->varnish_ip( [ '10.0.0.1' ] )
		);
	}
}
