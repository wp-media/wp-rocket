<?php

declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Admin;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Admin\Subscriber::sanitize_cdn_type_option
 *
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

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldSanitizeAndValidateCdnFields( array $input, array $expected ): void {
		$result = wpm_apply_filters_typed( 'array', $this->hook_name, $input );

		$this->assertSame( $expected['cdn_type'], $result['cdn_type'] );

		if ( \array_key_exists( 'cdn_state', $expected ) ) {
			$this->assertSame( $expected['cdn_state'], $result['cdn_state'] );
		}
	}
}
