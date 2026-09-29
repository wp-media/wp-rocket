<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\I18n\Weglot;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\I18n\Weglot;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\I18n\Weglot::is_activated
 *
 * @group Weglot
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests Weglot::is_activated() against the presence/absence of the Context_Weglot class.
	 *
	 * A namespaced class_exists() override (the pattern used in
	 * tests/Unit/inc/ThirdParty/Plugins/PWA/excludeServiceWorker.php for function_exists())
	 * was evaluated here and rejected: WP_Rocket\ThirdParty\Plugins is shared by several
	 * slice-1/slice-2 classes, and once such an override is declared anywhere in the analyzed
	 * fileset, PHPStan resolves EVERY unqualified class_exists() call in that namespace to
	 * the override instead of the builtin — including in unrelated production files (e.g.
	 * inc/ThirdParty/Plugins/Jetpack.php's `class_exists( 'Jetpack' )` guard). That breaks
	 * PHPStan's built-in class-exists type-narrowing extension, which only recognizes the
	 * real \class_exists(), producing a false "unknown class Jetpack" error on the next
	 * line's static call. Confirmed locally: `composer run-stan` is clean on this branch
	 * without the override and fails with that exact error once the override is added.
	 * eval() + @runInSeparateProcess avoids this entirely by keeping the defined class (and
	 * this test) isolated to its own process, with no shared-namespace function collision.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( $config['define_context_weglot'] ) {
			eval( 'class Context_Weglot {}' );
		}

		$this->assertSame( $expected, Weglot::is_activated() );
	}
}
