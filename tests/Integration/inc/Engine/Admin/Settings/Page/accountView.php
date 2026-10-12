<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Settings\Page;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering views/settings/page-sections/account.php
 *
 * @uses \WP_Rocket\Abstract_Render::generate
 * @uses ::rocket_get_constant
 * @uses ::rocket_do_action_and_deprecated
 *
 * @group AdminOnly
 * @group SettingsPage
 */
class Test_AccountView extends TestCase {
	public function tear_down() {
		remove_all_actions( 'rocket_account_license_info' );
		remove_all_actions( 'rocket_account_after_license_info' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRenderExpectedStates( $config, $expected ) {
		Functions\when( 'rocket_get_constant' )->alias(
			function ( $constant_name, $default = null ) use ( $config ) {
				if ( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' === $constant_name ) {
					return $config['white_label'];
				}

				return defined( $constant_name ) ? constant( $constant_name ) : $default;
			}
		);

		add_action(
			'rocket_account_license_info',
			function () {
				echo '<i id="license-hook"></i>';
			}
		);
		add_action(
			'rocket_account_after_license_info',
			function () {
				echo '<i id="after-hook"></i>';
			}
		);

		$container = apply_filters( 'rocket_container', null );
		$render    = $container->get( 'settings_render' );

		$html = $render->generate(
			'page-sections/account',
			[
				'id'            => 'account',
				'title'         => 'Account',
				'customer_data' => $config['customer_data'],
			]
		);

		foreach ( $expected['contains'] as $needle ) {
			$this->assertStringContainsString( $needle, $html );
		}

		foreach ( $expected['not_contains'] as $needle ) {
			$this->assertStringNotContainsString( $needle, $html );
		}
	}
}
