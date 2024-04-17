<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PDFEmbedder;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\PDFEmbedder::exclude_pdfembedder_scripts
 * @group  ThirdParty
 * @group  PDFEmbedderPremium
 */
class Test_ExcludePDFEmbedderScriptsPremium extends TestCase {
	// Saves and restores original settings.
	protected static $use_settings_trait = false;

	public function testShouldExcludePDFEmbedderScripts() {
		$excluded_js = apply_filters( 'rocket_exclude_js', [] );
		$expected = [
			'/wp-content/plugins/PDFEmbedder-premium/js/pdfjs/(.*).js',
			'/wp-content/plugins/PDFEmbedder-premium/js/(.*).js',
		];

		foreach ( $expected as $item ) {
			$this->assertContains( $item, $excluded_js );
		}
	}
}
