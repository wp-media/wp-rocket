<?php

namespace WP_Rocket\Tests\Unit\ThirdParty\inc\classes\third_party\plugins\Images\Webp\Webp_Common;

use stdClass;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Webp_Common;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers Webp_Common::register
 * @group ThirdParty
 * @group Webp
 */
class Test_Register extends TestCase {

	public function testShouldAddSelfReference() {
		$mock = $this->getMockForTrait( Webp_Common::class );

		$registered = $mock->register( [] );

		$this->assertContains( $mock, $registered );

		$registered = $mock->register(
			[
				new stdClass(),
			]
		);

		$this->assertContains( $mock, $registered );
	}
}
