<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PDFEmbedder;

use WP_Rocket\Tests\Integration\TestCase;

class Test_ExcludePDFEmbedderScripts extends TestCase
{
    // Saves and restores original settings.
    protected static $use_settings_trait = false;
    public function testShouldExcludePDFEmbedderScripts()
    {
        $excluded_js = apply_filters('rocket_exclude_js', []);
        $this->assertContains('/wp-content/plugins/pdf-embedder/js/(.*).js', $excluded_js);
    }
}
