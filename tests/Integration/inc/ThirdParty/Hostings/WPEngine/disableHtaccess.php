<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\WPEngine;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\WPEngine::disable_htaccess
 *
 * @group  WPEngine
 * @group  ThirdParty
 */
class Test_DisableHtaccess extends TestCase {

	public function testShouldDisableHtaccess() {
		if ( version_compare( PHP_VERSION, '7.4' ) >= 0 ) {
			$this->assertTrue( apply_filters( 'rocket_disable_htaccess', false ) );
		} else {
			$this->assertFalse( apply_filters( 'rocket_disable_htaccess', false ) );
		}
	}
}
