<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Wpengine;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Wpengine::disable_htaccess
 *
 * @group  Wpengine
 * @group  ThirdParty
 */
class Test_DisableHtaccess extends WpengineTestCase {

	public function testShouldDisableHtaccess() {
		if ( version_compare( PHP_VERSION, '7.4' ) >= 0 ) {
			$this->assertTrue( $this->wpengine->disable_htaccess( false ) );
		} else {
			$this->assertFalse( $this->wpengine->disable_htaccess( false ) );
		}
	}
}
