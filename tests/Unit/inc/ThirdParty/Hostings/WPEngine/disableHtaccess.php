<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\WPEngine;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\WPEngine::disable_htaccess
 *
 * @group  WPEngine
 * @group  ThirdParty
 */
class Test_DisableHtaccess extends WPEngineTestCase {

	public function testShouldDisableHtaccess() {
		if ( version_compare( PHP_VERSION, '7.4' ) >= 0 ) {
			$this->assertTrue( $this->wpengine->disable_htaccess( false ) );
		} else {
			$this->assertFalse( $this->wpengine->disable_htaccess( false ) );
		}
	}
}
