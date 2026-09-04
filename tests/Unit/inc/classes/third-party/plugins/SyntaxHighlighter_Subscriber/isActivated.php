<?php
namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\SyntaxHighlighter_Subscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\Third_Party\Plugins\SyntaxHighlighter_Subscriber;

/**
 * Test class covering \WP_Rocket\Subscriber\Third_Party\Plugins\SyntaxHighlighter_Subscriber::is_activated
 *
 * @group SyntaxHighlighter
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests SyntaxHighlighter_Subscriber::is_activated() against the presence/absence
	 * of the SyntaxHighlighter class.
	 *
	 * A namespaced class_exists() override was evaluated and rejected here, same as
	 * tests/Unit/inc/ThirdParty/Plugins/PDFEmbedder/isActivated.php: this legacy
	 * WP_Rocket\Subscriber\Third_Party\Plugins namespace already contains a production
	 * class_exists( 'Jetpack' ) guard (inc/classes/subscriber/third-party/plugins/class-mobile-subscriber.php),
	 * and NGG_Subscriber::is_activated() (this same slice) adds another. Declaring a
	 * namespaced class_exists() override anywhere in the analyzed fileset would make
	 * PHPStan resolve every unqualified class_exists() call in the namespace to the
	 * override instead of the builtin, breaking its class-exists type-narrowing
	 * extension for those unrelated guards. eval() + @runInSeparateProcess avoids this
	 * entirely by keeping the defined class (and this test) isolated to its own process.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( $config['define_syntaxhighlighter'] ) {
			eval( 'class SyntaxHighlighter {}' );
		}

		$this->assertSame( $expected, SyntaxHighlighter_Subscriber::is_activated() );
	}
}
