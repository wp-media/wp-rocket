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
		$this->assertTrue( $this->wpengine->disable_htaccess() );
		$this->assertTrue( $this->wpengine->disable_htaccess( true ) );
	}

	public function testShouldNotDisableHtaccess() {
		$this->assertFalse( $this->wpengine->disable_htaccess( false ) );
	}
}
