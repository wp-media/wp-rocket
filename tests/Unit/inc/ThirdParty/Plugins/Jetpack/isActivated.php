<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Jetpack;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Jetpack;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Jetpack::is_activated
 *
 * A namespaced class_exists() override for WP_Rocket\ThirdParty\Plugins was
 * evaluated and rejected here, same as PDFEmbedder/isActivated.php and
 * I18n/Weglot/isActivated.php: Jetpack.php itself lives in that namespace and
 * calls class_exists( 'Jetpack' ) unqualified inside its own, untouched
 * get_subscribed_events(), so declaring an override there breaks PHPStan's
 * built-in class-exists type-narrowing extension for that call. eval() +
 * @runInSeparateProcess sidesteps it by genuinely defining the Jetpack class
 * in an isolated process.
 *
 * @group Jetpack
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldReturnFalseWhenJetpackClassMissing() {
		$this->assertFalse( Jetpack::is_activated() );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldReturnTrueWhenJetpackClassPresent() {
		eval( 'class Jetpack {}' );

		$this->assertTrue( Jetpack::is_activated() );
	}
}
