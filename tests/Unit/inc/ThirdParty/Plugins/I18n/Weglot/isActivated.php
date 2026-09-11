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
	 * Uses eval() + @runInSeparateProcess, rather than a namespaced class_exists()
	 * override, to define the class in isolation: a namespaced override would make
	 * PHPStan resolve every unqualified class_exists() call in this shared
	 * namespace to the override, breaking its class-exists type-narrowing for
	 * unrelated production files.
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
