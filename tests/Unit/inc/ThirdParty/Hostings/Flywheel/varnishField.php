<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Flywheel;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Flywheel;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Flywheel::varnish_field
 *
 * @group Flywheel
 * @group ThirdParty
 * @group Hostings
 */
class Test_VarnishField extends TestCase {
	public function testShouldSetFlywheelTitle() {
		Functions\when( '__' )->returnArg( 1 );

		$subscriber = new Flywheel();

		$settings = $subscriber->varnish_field( [ 'varnish_auto_purge' => [ 'title' => '' ] ] );

		$this->assertSame(
			sprintf( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'Flywheel' ),
			$settings['varnish_auto_purge']['title']
		);
	}
}
