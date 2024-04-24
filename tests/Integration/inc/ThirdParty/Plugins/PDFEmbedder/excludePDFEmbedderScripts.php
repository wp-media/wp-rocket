<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PDFEmbedder;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\PDFEmbedder::exclude_pdfembedder_scripts
 * @group  ThirdParty
 * @group  PDFEmbedder
 */
class Test_ExcludePDFEmbedderScripts extends TestCase {
	// Saves and restores original settings.
	protected static $use_settings_trait = false;

	public function testShouldExcludePDFEmbedderScripts() {
		$excluded_js = apply_filters( 'rocket_exclude_js', [] );

		$this->assertContains( '/wp-content/plugins/pdf-embedder/js/(.*).js', $excluded_js );
	}
}
