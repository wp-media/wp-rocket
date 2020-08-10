<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DelayJS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber::display_restore_defaults_button
 *
 * @group  DelayJS
 */
class Test_DisplayRestoreDefaultsButton extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	public function testShouldDoExpected() {
		$expected = '<button id="wpr-action-rocket_delay_js_restore_defaults"  class="wpr-button wpr-button--icon wpr-button--purple wpr-icon-refresh">Restore Defaults</button>';

		$this->assertSame(
			$expected,
			$this->getActualHTML()
		);

	}

	private function getActualHTML() {
		ob_start();
		do_action( 'rocket_after_textarea_field_delay_js_scripts' );
		return ob_get_clean();
	}

}
