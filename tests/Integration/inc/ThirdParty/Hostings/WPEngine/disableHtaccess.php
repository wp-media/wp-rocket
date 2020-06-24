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
		$this->assertTrue( apply_filters( 'rocket_disable_htaccess', true ) );
	}

	public function testShouldNotDisableHtaccess() {
		$this->assertTrue( apply_filters( 'rocket_disable_htaccess', false ) );
	}
}
