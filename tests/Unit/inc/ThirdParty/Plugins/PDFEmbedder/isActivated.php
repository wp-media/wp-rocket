<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PDFEmbedder;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\PDFEmbedder;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\PDFEmbedder::is_activated
 *
 * @group PDFEmbedder
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests PDFEmbedder::is_activated() against the presence/absence of the core_pdf_embedder class.
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
		if ( $config['define_core_pdf_embedder'] ) {
			eval( 'class core_pdf_embedder {}' );
		}

		$this->assertSame( $expected, PDFEmbedder::is_activated() );
	}
}
