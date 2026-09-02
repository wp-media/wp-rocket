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
		remove_all_filters( 'pre_get_rocket_option_cdn' );
		remove_all_filters( 'pre_get_rocket_option_cdn_type' );
		$this->restoreWpHook( $this->hook_name );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldPreserveCdnFieldsFromDb( array $config, array $expected ): void {
		$db = $config['options'];

		add_filter( 'pre_get_rocket_option_cdn', fn() => $db['cdn'] );
		add_filter( 'pre_get_rocket_option_cdn_type', fn() => $db['cdn_type'] );

		$result = wpm_apply_filters_typed( 'array', $this->hook_name, $config['input'] );

		$this->assertSame( $expected['cdn_type'], $result['cdn_type'] );
		$this->assertSame( $expected['cdn_state'], $result['cdn_state'] );
	}
}
