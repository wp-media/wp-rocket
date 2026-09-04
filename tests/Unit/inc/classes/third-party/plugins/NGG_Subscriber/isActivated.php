<?php
namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\NGG_Subscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\Third_Party\Plugins\NGG_Subscriber;

/**
 * Test class covering \WP_Rocket\Subscriber\Third_Party\Plugins\NGG_Subscriber::is_activated
 *
 * @group NGG
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests NGG_Subscriber::is_activated() against the presence/absence of the
	 * C_NextGEN_Bootstrap class.
	 *
	 * A namespaced class_exists() override was evaluated and rejected here, same as
	 * tests/Unit/inc/ThirdParty/Plugins/PDFEmbedder/isActivated.php: this legacy
	 * WP_Rocket\Subscriber\Third_Party\Plugins namespace already contains a production
	 * class_exists( 'Jetpack' ) guard (inc/classes/subscriber/third-party/plugins/class-mobile-subscriber.php),
	 * and SyntaxHighlighter_Subscriber::is_activated() (this same slice) adds another.
	 * Declaring a namespaced class_exists() override anywhere in the analyzed fileset
	 * would make PHPStan resolve every unqualified class_exists() call in the
	 * namespace to the override instead of the builtin, breaking its class-exists
	 * type-narrowing extension for those unrelated guards. eval() + @runInSeparateProcess
	 * avoids this entirely by keeping the defined class (and this test) isolated to
	 * its own process.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( $config['define_ngg'] ) {
			eval( 'class C_NextGEN_Bootstrap {}' );
		}

		$this->assertSame( $expected, NGG_Subscriber::is_activated() );
	}
}
