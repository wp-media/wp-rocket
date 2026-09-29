<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\I18n\TranslatePress;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress::is_activated
 *
 * @group TranslatePress
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests TranslatePress::is_activated() when the TRP_Translate_Press class is present.
	 *
	 * Unlike PDFEmbedder/Weglot's marker classes, TRP_Translate_Press is
	 * unconditionally preloaded by tests/Unit/bootstrap.php's
	 * load_original_files_before_mocking() (needed by the pre-existing
	 * TranslatePress callback tests, e.g. detectHomepage.php, which call
	 * TRP_Translate_Press::get_trp_instance() directly). That preload runs
	 * again in an @runInSeparateProcess fork, so the class is always defined
	 * for every test in this suite — the false/absent branch is therefore not
	 * testable in-process without touching that shared bootstrap fixture list,
	 * which is out of scope for this class. Only the true branch is covered.
	 */
	public function testShouldReturnTrueWhenTranslatePressPresent() {
		$this->assertTrue( TranslatePress::is_activated() );
	}
}
