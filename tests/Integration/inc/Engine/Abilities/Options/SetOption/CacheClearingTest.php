<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Abilities\Options\SetOption;

use WP_Rocket\Engine\Abilities\Options\SetOption;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Integration tests for cache clearing after a wp-rocket/set-option ability execution.
 *
 * @group Abilities
 */
class CacheClearingTest extends TestCase {
	/**
	 * Test cache-clearing behavior outside wp-admin.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected result.
	 *
	 * @return void
	 */
	public function testShouldReturnExpected( array $config, array $expected ): void {
		$before = did_action( 'rocket_options_changed' );
		$result = null;

		switch ( $config['action'] ) {
			case 'update_rocket_option':
				update_rocket_option( $config['option_name'], $config['option_value'] );
				break;

			case 'set_option_execute':
				$result = ( new SetOption() )->execute(
					[
						'option_name'  => $config['option_name'],
						'option_value' => $config['option_value'],
					]
				);
				break;
		}

		if ( array_key_exists( 'is_admin', $expected ) ) {
			$this->assertSame( $expected['is_admin'], is_admin() );
		}

		if ( array_key_exists( 'hooked', $expected ) ) {
			$this->assertSame( $expected['hooked'], (bool) has_action( 'update_option_wp_rocket_settings', 'rocket_after_save_options' ) );
		}

		if ( array_key_exists( 'success', $expected ) ) {
			$this->assertSame( $expected['success'], $result['success'] );
		}

		if ( array_key_exists( 'options_changed_fired', $expected ) ) {
			$this->assertSame( $expected['options_changed_fired'], did_action( 'rocket_options_changed' ) > $before );
		}
	}
}
