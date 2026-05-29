<?php

declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Admin;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @group CDN
 * @group AdminOnly
 */
class Test_SanitizeCdnTypeOption extends TestCase {
	private $hook_name = 'rocket_input_sanitize';

	public function set_up() {
		parent::set_up();

		 $this->unregisterAllCallbacksExcept( $this->hook_name, 'sanitize_cdn_type_option', 10 );
	}

	public function tear_down() {
		$this->restoreWpHook( $this->hook_name );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		$sanitized_fields = wpm_apply_filters_typed( 'array', $this->hook_name, $config['input'] );
		
		$this->assertSame($expected['input'], $sanitized_fields);
	}
}
